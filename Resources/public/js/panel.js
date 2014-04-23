/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

$(function () {

	var topDocument = top.document;
	var rectangles = new Array();
	var rectangleObjects = new Array();
	var edit = false;


	// Iframe manipulation
	$('div').on('mouseover', function () {
		if (!edit) {
			$('#venne-panel-container', topDocument).height('100%');
		}
	});
	$('div').on('mouseout', function () {
		if (!edit) {
			$('#venne-panel-container', topDocument).height('43px');
		}
	});
	$('a').attr("target", "_parent");
	$('form').attr("target", "_parent");


	// Edit & Save buttons
	$('#venne-panel-button-save').hide();
	$('#venne-panel-button-edit').on('click', function () {
		$("#venne-panel-container", topDocument).height('100%');
		$('body').css('background-color', 'rgba(255, 255, 255, 0.5)');

		drawRectangleOnObject('.venne-element-container');
		$('#venne-panel-button-save').show();
		$(this).hide();
		edit = true;

		window.parent._venne_panel_button = 'edit';
	});
	$('#venne-panel-button-save').on('click', function () {
		$("#venne-panel-container", topDocument).height('42px');
		$('body').css('background-color', 'transparent');

		clearAll();
		$('#venne-panel-button-edit').show();
		$(this).hide();
		edit = false;

		window.parent._venne_panel_button = 'save';
	});
	if (window.parent._venne_panel_button !== undefined) {
		$('#venne-panel-button-' + window.parent._venne_panel_button).click();
	}


	// Close button
	$('#venne-panel-button-close').on('click', function () {
		if (window.parent.location.href.indexOf("?") != -1) {
			window.top.location.href = window.parent.location.href + '&do=closePanel';
		} else {
			window.top.location.href = window.parent.location.href + '?do=closePanel';
		}
	});


	// Redraw
	$(window.parent).scroll(function () {
		redrawRectangles();
	});
	$(window.parent).resize(function () {
		redrawRectangles();
	});


	// Functions
	function redrawRectangles() {
		var objects = rectangleObjects.slice(0);

		clearAll();

		for (i = 0; i < objects.length; i++) {
			drawRectangleOnObject(objects[i]);
		}
	}
	window.redrawRectangles = redrawRectangles;

	function drawRectangleOnObject(element) {
		rectangleObjects[rectangleObjects.length] = element;

		$(element, topDocument).each(function () {

			var obj = $(this);
			var buttons = jQuery.parseJSON($(this).data('venne-element-buttons').replace(/'/g, '"'));

			var position = $(this).offset();
			var height = $(this).height();
			var width = $(this).width();
			var top = position.top - $(window.parent).scrollTop();
			var left = position.left - $(window.parent).scrollLeft();
			var html = '<div class="btn-group"><a class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" href="#">Edit <span class="caret"></span></a><ul class="dropdown-menu">';
			$.each(buttons, function (index, value) {
				html += '<li><a href="?mode=1&do=element&elementName=' + obj.data('venne-element-name') + '&elementView=' + index + '&elementId=' + obj.data('venne-element-id') + '&elementRouteId=' + obj.data('venne-element-route') + '&elementLanguageId=' + obj.data('venne-element-language') + '" target="_self" class="ajax" type="button">' + value + '</a></li>';
			});
			html += '</ul></div>';
			drawRectangle(width, height, left, top, html);
		});

		$.nette.load();
	}

	function drawRectangle(width, height, x, y, html) {

		var element = $('<div>', {
			id:'rectangle_' + rectangles.length,
			class:'venne-panel-block',
			style:'top: ' + y + 'px; left: ' + x + 'px; width: ' + width + 'px; height: ' + height + 'px;',
			html:html
		});

		element.appendTo('body');
		rectangles[rectangles.length] = element;
		return element;
	}

	function clearAll() {
		var element;
		for (i = 0; i < rectangles.length; i++) {
			element = rectangles[i];
			element.remove();
		}
		rectangles.length = 0;
		rectangleObjects.length = 0;
	}

});


