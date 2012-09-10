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

	$('#create-new').live("click", function () {
		$(this).next().click();
	});

	var dateInputOptions = {
		datetime:{
			dateFormat:'d.m.yy',
			timeFormat:'h:mm'
		},
		'datetime-local':{
			dateFormat:'d.m.yy',
			timeFormat:'h:mm'
		},
		date:{
			dateFormat:'d.m.yy'
		},
		month:{
			dateFormat:'MM yy'
		},
		week:{
			dateFormat:"w. 'týden' yy"
		},
		time:{
			timeFormat:'h:mm'
		}
	};

	// Jquery plugins
	$('select[multiple]').multiSelect();
	$('input[data-dateinput-type]').dateinput(dateInputOptions);
	$('select[data-venne-form-textwithselect]').textWithSelect();


	// Ajax
	$.nette.ext('formsValidationBind', {
		success:function (payload) {
			if (!payload.snippets) {
				return;
			}

			for (var i in payload.snippets) {
				$('#' + i + ' form').each(function () {
					Nette.initForm(this);
				});
			}
		}
	});
	$.nette.ext('formsMultiSelectBind', {
		success:function (payload) {
			if (!payload.snippets) {
				return;
			}

			for (var i in payload.snippets) {
				$('#' + i + ' select[multiple]').each(function () {
					$(this).multiSelect();
				});
			}
		}
	});
	$.nette.ext('formsDateInputBind', {
		success:function (payload) {
			if (!payload.snippets) {
				return;
			}

			for (var i in payload.snippets) {
				$('#' + i + ' input[data-dateinput-type]').each(function () {
					$(this).dateinput(dateInputOptions);
				});
			}
		}
	});
	$.nette.ext('formsTextWithSelectInputBind', {
		success:function (payload) {
			if (!payload.snippets) {
				return;
			}

			for (var i in payload.snippets) {
				$('#' + i + ' select[data-venne-form-textwithselect]').each(function () {
					$(this).textWithSelect();
				});
			}
		}
	});
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


