function createCookie(name, value, days) {
    alert(name);
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else
        var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

jQuery(document).ready(function ($) {
    
    $("a.wph_add_title").click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        $.post(ajaxurl, {'action': 'wph_metabox_html'}, function (data) {
            data = $(data);
            $("#wph_inner_container").append(data);
            data.find('input').focus();
        });
//        var wph_metabox_content = $("#wph_metabox_content").html();
//        $("#wph_inner_container").append(wph_metabox_content);
    });

    $(document).on('click', "a.remove_title", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var _this = $(this);
        if (confirm("Are you sure?")) {
            if ($(this).data('version') == "old") {
                $.post(ajaxurl, {'action': 'wph_title_remove', 'key': $(this).data('key'), 'post_id': $(this).data('post')}, function (data) {
                });
            }
            _this.parent().parent().remove();
        }
    });

    $(document).bind('keydown', function (e) {
        if (e.shiftKey && e.which === 78) {
            $("a.wph_add_title").trigger('click');
            return false;
        }
    });
});