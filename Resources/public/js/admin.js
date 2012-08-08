$(document).ready(function () {
    $("#submenu ul ul").hide(); // Hide all sub menus
    $("#submenu > ul > li.active").find("ul").slideToggle("slow");

    $("#main-nav li a.no-submenu").click(// When a menu item with no sub menu is clicked...
        function () {
            window.location.href = (this.href); // Just open the link instead of a sub menu
            return false;
        }
    );

    $("#submenu > ul > li > a").hover(
        function () {
            $(this).stop().animate({
                paddingRight:"25px"
            }, 150);
        },
        function () {
            $(this).stop().animate({
                paddingRight:"10px"
            }, 150);
        }
    );

    $("fieldset.collapsed").children().not("legend").children().hide();
    $("legend").live("click", function () {
        $(this).parent().children().not("legend").children().toggle("fast", function () {

        });
        if ($(this).parent().hasClass("collapsed")) $(this).parent().removeClass("collapsed");
        else $(this).parent().addClass("collapsed");
    })

});
