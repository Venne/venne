/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

jQuery.extend({
	venne: {
		getBasePath: function () {
			if ($("body").attr("data-venne-basepath") !== undefined) {
				return $("body").attr("data-venne-basepath");
			} else {
				return "";
			}
		}
	}
});

$(function () {

	$('#create-new').on("click", function () {
		$(this).next().click();
	});

	// panel max height
	$('#panel .panel-container').css('max-height', ($(window).height() - 155) + 'px');
	$(window).on('resize', function(){
		$('#panel .panel-container').css('max-height', ($(this).height() - 155) + 'px');
	});

	$('#button-fullscreen').on('click', function (event) {
		if ($('#panel').data('state') != 'closed') {
			event.preventDefault();
			$('#panel').animate({
				marginLeft: '-300px'
			}, 300).data('state', 'closed');
			$('#content').animate({
				marginLeft: '10px'
			}, 300);
		} else {
			event.preventDefault();
			$('#panel').animate({
				marginLeft: '0px'
			}, 300).data('state', null);
			$('#content').animate({
				marginLeft: '310px'
			}, 300);
		}
	});

	// Ajax
	$.nette.ext('removeDoubleModalBackground', {
		load: function() {
			var len = $('.modal-backdrop').length;
			$('.modal-backdrop').each(function(index){
				if (index < len - 1) {
					$(this).remove();
				}
			});
		}
	});
	$.nette.ext('data-ajax-confirm', {
		before: function (xhr, settings) {
			if (settings.nette !== undefined && settings.nette.el !== undefined) {
				var question = settings.nette.el.data('confirm');
				if (question) {
					return confirm(question);
				}
			}
		}
	});
	$.nette.ext('formsValidationBind', {
		load: function () {
			this.init($('body'));
		}
	}, {
		init: function (target) {
			target.find(this.selector).each(function () {
				if (!$(this).data('formsValidationBind')) {
					$(this).data('formsValidationBind', true);
					Nette.initForm(this);
				}
			});
		},
		selector: "form"
	});
	$.nette.ext('tooltipBind', {
		load: function () {
			this.init($('body'));
		}
	}, {
		init: function (target) {
			target.find(this.selector).each(function () {
				if (!$(this).data('ajaxTooltipCreated')) {
					$(this).data('ajaxTooltipCreated', true);
					$(this).tooltip({html: true});
				}
			});
		},
		selector: "[data-toggle='tooltip']"
	});
	$.nette.ext('formsMultiSelectBind', {
		load: function () {
			this.init($('body'));
		},
		success: function (payload) {
			if (!payload.snippets) {
				return;
			}

			var _this = this;
			for (var i in payload.snippets) {
				$('#' + i).each(function () {
					_this.init($(this));
				});
			}
		}
	}, {
		init: function (target) {
			target.find(this.selector).each(function () {
				if (!$(this).data('formsMultiSelectBind')) {
					$(this).data('formsMultiSelectBind', true);
					var args = {
						width: 'resolve'
					};

					if ($(this).attr('data-tag-suggest') !== undefined) {
						args.createSearchChoice = function (term, data) {
							if ($(data).filter(function () {
								return this.text.localeCompare(term) === 0;
							}).length === 0) {
								return {
									id: term,
									text: term
								};
							}
						};
						args.initSelection = function (element, callback) {
							var data = [];
							$(element.val().split($(element).attr('data-tag-joiner'))).each(function () {
								data.push({id: this, text: this});
							});
							callback(data);
						};
						args.ajax = {
							url: $(this).attr('data-tag-suggest'),
							dataType: 'json',
							results: function (data) {
								var results = [];
								$.each(data.results, function (index, item) {
									results.push({
										id: index,
										text: item
									});
								});
								return {
									results: results
								};
							},
							data: function (term, page) {
								return {
									q: term
								}
							}
						};
					}

					if ($(this).attr('data-tag-joiner') !== undefined) {
						var tags = jQuery.parseJSON($(this).attr('data-tags'));
						args.tags = true;
						//args.tokenSeparators = [$(this).attr('data-tag-joiner')];
						args.separator = $(this).attr('data-tag-joiner');
					}

					$(this).select2(args);
				}
			});
		},
		selector: 'select[multiple], input[data-tag-joiner]'
	});
	$.nette.ext('formsDateInputBind', {
		load: function () {
			this.init($('body'));
		}
	}, {
		init: function (target) {
			target.find(this.selector).each(function () {
				if (!$(this).data('formsDateInputBind')) {
					$(this).data('formsDateInputBind', true);

					var e = $(this);
					e.wrap('<div class="input-group date" id="datetimepicker-' + $(this).attr('id') + '" />')
						.after('<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>')
						.parent('div');
					$('#datetimepicker-' + $(this).attr('id')).datetimepicker({
						format: 'yyyy-MM-dd hh:mm:ss'
					});
				}
			});
		},
		selector: 'input[type=date], input[type=datetime]'
	});
	$.nette.ext('gridoBind', {
		load: function (rh) {
			$('.grido .actions a').off('click.nette');
			$('.grido').grido();
			$('.grido .actions a').on('click.nette', rh);
		}
	});
	$.nette.ext('formsIframePostBind', {
		load: function () {
			this.init($('body'));
		}
	}, {
		init: function (target) {
			target.find(this.selector).each(function () {
				$(this).parents('form').each(function () {
					if (!$(this).data('formsIframePostBind')) {
						$(this).data('formsIframePostBind', true);

						$(this).removeClass('ajax');
						var _id = $(this).attr('id');

						$(this).iframePostForm({
							iframeID: this.idPrefix + _id,
							complete: function (response) {
								url = $('#' + this.idPrefix + _id).get(0).contentWindow.location;
								$.nette.ajax({url: url});
							}
						});
					}
				});
			});
		},
		selector: 'form.ajax input:file',
		idPrefix: 'iframe-post-form-'
	});
	$.nette.ext('formsFileUpload', {
		load: function () {
			this.init('body');
		}
	}, {
		init: function (target) {
			var changeFunc = function (object) {
				object.change(function () {
					var data = $(this).val();
					if (data) {
						var data = '<i class="glyphicon glyphicon-file"></i> ' + data;
						$('#' + object.attr('id') + '_fake').html(data.replace('C:\\fakepath\\', ''));
						$('#' + object.attr('id') + '_fakeRemove').show();
						$('#' + object.attr('id') + '_fakeButton').text('Change');
					} else {
						$('#' + object.attr('id') + '_fake').html(data);
						$('#' + object.attr('id') + '_fakeRemove').hide();
						$('#' + object.attr('id') + '_fakeButton').text('Select file');
					}
				});
			}
			$(target).find('input[type="file"]').each(function () {
				if (!$(this).data('formsFileUpload')) {
					$(this).data('formsFileUpload', true);

					var fileInput = $(this);
					$(this).after('<div class="input-group">'
						+ '<div class="form-control input-sm" id="' + $(this).attr('id') + '_fake" type="text" disabled></div>'
						+ '<div class="input-group-btn btn-group">'
						+ '<button class="btn btn-default btn-sm hide" id="' + $(this).attr('id') + '_fakeRemove" type="button">Remove</button>'
						+ '<button class="btn btn-default btn-sm" id="' + $(this).attr('id') + '_fakeButton" type="button">Select file</button>'
						+ '</div>'
						+ '</div>');
					$('#' + $(this).attr('id') + '_fakeButton').off('click');
					$('#' + $(this).attr('id') + '_fakeRemove').off('click');
					$('#' + $(this).attr('id') + '_fakeButton').on('click', function () {
						fileInput.click();
					});
					$('#' + $(this).attr('id') + '_fakeRemove').on('click', function () {
						fileInput.replaceWith(fileInput.clone());
						$('#' + fileInput.attr('id') + '_fake').html('');
						$('#' + fileInput.attr('id') + '_fakeRemove').hide();
						$('#' + fileInput.attr('id') + '_fakeButton').text('Select file');
						changeFunc(fileInput);
					});
					changeFunc($(this));
					$(this).hide();
				}
			});
		}
	});
	$.nette.ext('bootstrapModalBind', {
		init: function () {
			this.resize();
			$(window).bind('resize', this.resize);
		},
		success: function (payload) {
			this.resize();
		}
	}, {
		resize: function () {
			$(".modal.modal-full .modal-body").css("max-height", $(window).height() - 120);
		}
	});
	$.nette.ext('textWithSelectBind', {
		load: function () {
			this.init($('body'));
		}
	}, {
		init: function (target) {
			target.find(this.selector).each(function () {
				if (!$(this).data('textWithSelectBind')) {
					$(this).data('textWithSelectBind', true);
					$(this).textWithSelect();
				}
			});
		},
		selector: "form .input-group-btn"
	});
	$.nette.init();

	$('a[data-confirm], button[data-confirm], input[data-confirm]').on('click', function (e) {
		var el = $(this);
		if (el.triggerAndReturn('confirm')) {
			if (!confirm(el.attr('data-confirm'))) {
				e.preventDefault();
				e.stopImmediatePropagation();
				return false;
			}
		}
	});

	$('.table tr').on('click', function (event) {
		if (!$(event.target).closest('input[type=checkbox]').length > 0) {
			var checkbox = $(this).find('input[type=checkbox]').each(function () {
				if (this.checked) {
					this.checked = false;
				} else {
					this.checked = true;
				}
			});
		}
	});

});


