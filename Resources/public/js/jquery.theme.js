// Animates the dimensional changes resulting from altering element contents
// Usage examples: 
//    $("#myElement").showHtml("new HTML contents");
//    $("div").showHtml("new HTML contents", 400);
//    $(".className").showHtml("new HTML contents", 400, 
//                    function() {/* on completion */});
(function ($) {
    $.fn.showHtml = function (html, speed, callback) {
        return this.each(function () {
            var el = $(this);

            var finish = {width:el.width() + 'px', height:200 + 'px'};
            var h = el.html();
            el.html(html);
            var next = {width:el.width() + 'px', height:el.height() + 'px'};
            el.html(h);

            el.animate(finish, speed, function ()  // animate to final dimensions
            {
                el.html(html);
                el.css(finish);
                $(this).animate(next, speed, function ()  // animate to final dimensions
                {
                    el.css(next);
                    if ($.isFunction(callback)) callback();
                });
            });
        });
    };


})(jQuery);


jQuery.extend({
    theme:{
        updateSnippet:function (id, html) {
            if (id == "snippet--content") {
                //$("#snippet--content").showHtml(html, 800);
                $('#' + id).fadeOut(90, function () {
                    $("#" + id).html(html);
                    $('#' + id).fadeIn(90);
                });
            } else {
                jQuery.nette.updateSnippet(id, html);
            }
        },

        success:function (payload) {
            // redirect
            if (payload.redirect) {
                window.location.href = payload.redirect;
                return;
            }

            // snippets
            if (payload.snippets) {
                for (var i in payload.snippets) {
                    jQuery.theme.updateSnippet(i, payload.snippets[i]);
                }
            }

            if (typeof($.dependentselectbox) != "undefined") {
                $.dependentselectbox.hideSubmits();
            }
        }
    }
});

jQuery.ajaxSetup({
    success:jQuery.theme.success,
    dataType:"json"
});