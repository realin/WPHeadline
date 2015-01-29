<?php

/*
  Plugin Name: WP Headlines
  Plugin URI:
  Description: Track Headlines Control
  Version: 0.1
  Author: Sachin Khosla
  Author Email: sachin@digimantra.com
  License:

  Copyright 2011 Sachin Khosla (sachin@digimantra.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( !function_exists( 'add_action' ) ) {
    echo 'Nothing much here :)';
    exit;
}


//define('WPHEADLINES_PLUGIN_URL', plugin_dir_url(__FILE__));
//define('WPHEADLINES_PLUGIN_DIR', plugin_dir_path(__FILE__));

class WPHeadlines
{
    /* --------------------------------------------*
     * Constants
     * -------------------------------------------- */

    const name = 'WP Headlines';
    const slug = 'wp_headlines';

    /**
     * Constructor
     */
    function __construct()
    {
        if (!session_id())
        {
            session_start();
        }

        //register an activation hook for the plugin
        register_activation_hook(__FILE__, array(&$this, 'install_wp_headlines'));

        //Hook up to the init action
        add_action('init', array(&$this, 'init_wp_headlines'));
        add_action('init', array(&$this, 'save_session_to_cookies'));
    }

    /**
     * Runs when the plugin is initialized
     */
    function init_wp_headlines()
    {

        // Load JavaScript and stylesheets
        $this->register_scripts_and_styles();

        // Register the shortcode [headlines_shortcode]
        add_shortcode('headlines_shortcode', array(&$this, 'render_shortcode'));

      

        /*
         * TODO: Define custom functionality for your plugin here
         *
         * For more information: 
         * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
         */
        add_action('edit_form_after_title', array(&$this, 'cus_title_metabox_display'));
        add_action('save_post', array(&$this, 'cus_title_metabox_save'));
//        add_action('admin_footer', array(&$this, 'wph_admin_footer'));
        add_action('wp_ajax_wph_metabox_html', array(&$this, 'wph_metabox_html'));
        add_action('wp_ajax_wph_title_remove', array(&$this, 'wph_title_remove'));
        add_filter('the_title', array(&$this, 'wp_headline_title'), 10, 2);
    }

    function cus_title_metabox_display($post)
    {
//check if there's already a title to display
        $title = get_post_meta($post->ID, '_cus_title', true);
        echo '<div id="wph_container">
				<div id="wph_inner_container">
				<label for="title" id="wph_title_label" class="wph_title_label">Title</label>';
        if ($title)
        {
            $data = unserialize($title);
            $i = 0;
            foreach ($data as $key => $value)
            {
                include 'template/titles_content.php';
                $i++;
            }
        }
        echo '</div>
		<a href="#" class="button wph_add_title">Add Title</a> (Shift + N)
			</div><div class="clearfix"></div>';
    }

    function cus_title_metabox_save($post_ID)
    {
//check if there is a value to save
        if (count($_POST["cus_title"]) >= 1)
        {
            $titles = array_filter($_POST["cus_title"]);
            update_post_meta($post_ID, '_cus_title', serialize($titles));
        } else
        {
            update_post_meta($post_ID, '_cus_title', false);
        }
    }

    function wph_metabox_html()
    {
        include 'template/metaboxhtml.php';
        die;
    }
    
    function wph_title_remove()
    {
        $key  = "title_pos_".$_POST['key'];
        $post_ID = $_POST['post_id'];
        if($title_count = get_post_meta($post_ID, $key,true)){
            delete_post_meta($post_ID,$key);
            update_post_meta($post_ID, 'total_title_count', (int) get_post_meta($post_ID, 'total_title_count', true) - (int)$title_count);
        }
        die;
    }

    function wp_headline_title($title, $post_ID)
    {

        //so that titles do not messup in the wordpress dashboard

        if (!is_admin())
        {
            // if it's a returning user, we fetch data from cookie

            if (!isset($_SESSION['data']))
            {
                $this->get_session_from_cookies();
            }

            // if we have randomized titles in the session, let's show 'em
            if (isset($_SESSION['data'][$post_ID]) && !empty($_SESSION['data'][$post_ID]))
            {
                if ($post_titles = get_post_meta($post_ID, '_cus_title', true))
                {
                    $selector = $_SESSION["data"][$post_ID];
                    $selected_title = $this->get_wph_title($post_ID, $selector);
                    $this->set_counter_for_single($post_ID, $selector);
                } else
                {
                    return $title;
                }
            }
            else
            {
                //user has arrived for the first and we will generate 
                // a new title for him. After which we set the title to session
                if ($post_titles = get_post_meta($post_ID, '_cus_title', true))
                {
                    if (!isset($_SESSION['data']))
                    {
                        $_SESSION['data'] = array();
                    }
                    
                    $wph_title = unserialize($post_titles);

                    $total_counter = count($wph_title) - 1;
                    $selector = rand(0, $total_counter);
                    $wph_title_key = array_keys($wph_title);
                    $wph_title_key = $wph_title_key[$selector];
                    $selected_title = $wph_title[$wph_title_key];
                    $_SESSION['data'][$post_ID] = $wph_title_key;
                    $this->set_counter_for_single($post_ID, $wph_title_key);
                } 
                // if there's no title, return the "default" one
                else
                {
                    return $title;
                }
            }
            return $selected_title;
        } else
        {
            return $title;
        }
    }

    function get_wph_title($post_ID, $selector)
    {
        $post_titles = get_post_meta($post_ID, '_cus_title', true);
        $wph_title = unserialize($post_titles);
        return $wph_title[$selector];
    }

    function set_counter_for_single($post_IDs, $pos)
    {
        if (is_single())
        {
            global $post;
            if (!isset($_SESSION['cdata']))
            {
                $_SESSION['cdata'] = array();
            }
            $post_ID = $post->ID;
            if (!(isset($_SESSION['cdata'][$post_ID]) && !empty($_SESSION['cdata'][$post_ID])) && $post_IDs == $post_ID)
            {
                update_post_meta($post_ID, 'title_pos_' . $pos, (int) get_post_meta($post_ID, 'title_pos_' . $pos, true) + 1);
                update_post_meta($post_ID, 'total_title_count', (int) get_post_meta($post_ID, 'total_title_count', true) + 1);
                $_SESSION['cdata'][$post_ID] = true;
            }
        }
    }

    function save_session_to_cookies()
    {
        $session1 = $_SESSION['data'];
        $session1 = json_encode($session1, true);
        $session2 = $_SESSION['cdata'];
        $session2 = json_encode($session2, true);
        setcookie('site_random_counter', $session2, time() + (86400 * 30), "/"); // 86400 = 1 day
        setcookie('site_random_title', $session1, time() + (86400 * 30), "/"); // 86400 = 1 day
    }

    function get_session_from_cookies()
    {
        if (isset($_COOKIE['site_random_title']))
        {
            $session = ($_COOKIE['site_random_title']);
            $session = stripslashes($session);
            $session = (array) (json_decode($session, true));
            $_SESSION['data'] = $session;
        }

        if (isset($_COOKIE['site_random_counter']))
        {
            $session = ($_COOKIE['site_random_counter']);
            $session = stripslashes($session);
            $session = (array) (json_decode($session, true));
            $_SESSION['cdata'] = $session;
        }
    }

    function get_counter_for_single($post_ID, $pos)
    {

        if ($value = get_post_meta($post_ID, 'title_pos_' . $pos, true))
        {
            $total_count = (int) get_post_meta($post_ID, 'total_title_count', true);
            if ($total_count)
                $percentage = (int) $value / (int) get_post_meta($post_ID, 'total_title_count', true) * 100;
            else
                $percentage = 0;
            return round($percentage, 2);
        } else
            return 0;
    }

    function render_shortcode($atts)
    {
// Extract the attributes
        extract(shortcode_atts(array(
            'attr1' => 'foo', //foo is a default value
            'attr2' => 'bar'
                        ), $atts));
// you can now access the attribute values using $attr1 and $attr2
    }

    /**
     * Registers and enqueues stylesheets for the administration panel and the
     * public facing site.
     */
    private function register_scripts_and_styles()
    {
        if (is_admin())
        {
            $this->load_file(self::slug . 'admin_bootstrap_script', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js', true, true);
            $this->load_file(self::slug . 'admin_bootstrap_style', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css', false, true);
            $this->load_file(self::slug . '-admin-script', '/js/wph_script.js', true);
            $this->load_file(self::slug . '-admin-style', '/css/wph_style.css');
        } else
        {
//            $this->load_file(self::slug . '-script', '/js/widget.js', true);
//            $this->load_file(self::slug . '-style', '/css/widget.css');
        } // end if/else
    }

// end register_scripts_and_styles

    /**
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name	The 	ID to register with WordPress
     * @file_path		The path to the actual file
     * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
     */
    private function load_file($name, $file_path, $is_script = false, $is_url = false)
    {
        if ($is_url)
        {
            if ($is_script)
            {
                wp_register_script($name, $file_path, array('jquery')); //depends on jquery
                wp_enqueue_script($name);
            } else
            {
                wp_register_style($name, $file_path);
                wp_enqueue_style($name);
            } // 
        } else
        {
            $url = plugins_url($file_path, __FILE__);
            $file = plugin_dir_path(__FILE__) . $file_path;

            if (file_exists($file))
            {
                if ($is_script)
                {
                    wp_register_script($name, $url, array('jquery')); //depends on jquery
                    wp_enqueue_script($name);
                } else
                {
                    wp_register_style($name, $url);
                    wp_enqueue_style($name);
                } // end if
            } // end if
        }
    }

// end load_file
}

// end class
new WPHeadlines();