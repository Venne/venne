/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

jQuery.extend({
	venne:{
		getBasePath:function () {
			if ($("body").attr("data-venne-basepath") !== undefined) {
				return $("body").attr("data-venne-basepath");
			} else {
				return "";
			}
		}
	}
});

$(function () {

	$("#snippet-panel-tabs a").live("click", function (event) {
		event.preventDefault();
		history.pushState({
			module:"leave"
		}, "page 2", $(this).attr("href"));
		$("#snippet-panel-tabs li").removeClass("active");
		$(this).parent().addClass("active");

		$.get(this.href);
	});

	$('#create-new').live("click", function () {
		$(this).next().click();
	});


	// Ajax
	$.nette.init();


	$('a[data-confirm], button[data-confirm], input[data-confirm]').live('click', function (e) {
		var el = $(this);
		if (el.triggerAndReturn('confirm')) {
			if (!confirm(el.attr('data-confirm'))) {
				e.preventDefault();
				e.stopImmediatePropagation();
				return false;
			}
		}
	});

});


