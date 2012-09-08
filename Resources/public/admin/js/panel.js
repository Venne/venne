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
	$('div').live('mouseover', function () {
		if (!edit) {
			$('#venne-panel-container', topDocument).height('100%');
		}
	});
	$('div').bind('mouseout', function () {
		if (!edit) {
			$('#venne-panel-container', topDocument).height('42px');
		}
	});
	$('a').attr("target", "_parent");


	// Edit & Save buttons
	$('#venne-panel-button-save').hide();
	$('#venne-panel-button-edit').live('click', function () {
		$("#venne-panel-container", topDocument).height('100%');
		$('body').css('background-color', 'rgba(255, 255, 255, 0.5)');

		//drawRectangleOnObject('.breadcrumb');
		$('#venne-panel-button-save').show();
		$(this).hide();
		edit = true;
	});
	$('#venne-panel-button-save').live('click', function () {
		$("#venne-panel-container", topDocument).height('42px');
		$('body').css('background-color', 'transparent');

		clearAll();
		$('#venne-panel-button-edit').show();
		$(this).hide();
		edit = false;
	});


	// Close button
	$('#venne-panel-button-close').live('click', function () {
		$('#venne-panel-container', topDocument).remove();
	});


	// Redraw
	$(window.parent).scroll(function () {
		redrawRectangles();
	});
	$(window.parent).resize(function () {
		redrawRectangles();
	});


	function redrawRectangles() {
		var objects = rectangleObjects.slice(0);

		clearAll();

		for (i = 0; i < objects.length; i++) {
			drawRectangleOnObject(objects[i]);
		}
	}

	function drawRectangleOnObject(element) {
		rectangleObjects[rectangleObjects.length] = element;

		var position = $(element, topDocument).offset();
		var height = $(element, topDocument).height();
		var width = $(element, topDocument).width();
		var top = position.top + $('html', topDocument).offset().top;
		var left = position.left + $('html', topDocument).offset().left;

		drawRectangle(width, height, left, top);
	}

	function drawRectangle(width, height, x, y) {

		var element = $('<div>', {
			id:'rectangle_' + rectangles.length,
			class:'venne-panel-block',
			style:'top: ' + y + 'px; left: ' + x + 'px; width: ' + width + 'px; height: ' + height + 'px;',
			text:'edit'
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


