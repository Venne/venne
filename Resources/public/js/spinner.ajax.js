(function($, undefined) {

	$.nette.ext('spinner', {
		init: function () {
			this.spinner = this.createSpinner();
			this.spinner.appendTo('body');
		},
		before: function () {
		},
		start: function () {
			this.spinner.show(this.speed);
			this.spinner.css('opacity', '1');
			this.spinner.css('width', '0%');
			this.spinner.animate({
				width: '90%'
			});
		},
		complete: function () {
			this.spinner.animate({
				width: '100%'
			}, 50).animate({
					opacity: '0'
				});
		}
	}, {
		createSpinner: function () {
			return $('<div>', {
				id: 'ajax-spinner',
				style: "display: none;"
			});
		},
		spinner: null,
		speed: undefined
	});

})(jQuery);