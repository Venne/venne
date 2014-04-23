(function ($) {
	$.fn.textWithSelect = function () {

		$(this).find('.dropdown-menu li a')
			.off('click.textWithSelect')
			.on('click.textWithSelect', function(e){
				e.preventDefault();
				$(this).parent().parent().parent().find("span.textWithSelect-text").text($(this).text());
				$(this).parent().parent().parent().parent().find("input").val($(this).text());
			});

		$(this).parent().find("input")
			.off('change.textWithSelect')
			.on('change.textWithSelect', function () {
				var text = $(this).val();

				var $span = $(this).parent().parent().parent().find("span.textWithSelect-text");
				$span.text('');

				$(this).parent().find('.dropdown-menu li a').each(function(){
					if ($(this).text() == text) {
						$span.text(text);
					}
				});
			});

	};
})(jQuery);
