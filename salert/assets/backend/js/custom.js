/**
 * Salert Admin JS — Modern UI (2026 redesign)
 * Save endpoint & field names unchanged from v1.3.1.
 */
jQuery(document).ready(function ($) {
	'use strict';

	var $form = $('form#salert-settings-form');
	var $saveBtn = $('.salert-savebar .salert-btn');
	var $notice = $('.salert-savebar .save-notice');
	var $template = $('#popup_template');

	/* ===== Sidebar navigation ===== */
	$('.salert-nav-item').on('click', function () {
		var pane = $(this).data('pane');
		$('.salert-nav-item').removeClass('active');
		$(this).addClass('active');
		$('.salert-pane').removeClass('active');
		$('#' + pane).addClass('active');
	});

	/* ===== Dirty-state tracking ===== */
	var initialized = false; // programmatic .trigger('change') during setup must not mark the form dirty
	function markDirty() {
		if (!initialized) { return; }
		$saveBtn.addClass('save-now');
		$notice.addClass('visible');
	}
	$form.on('input change', 'input, textarea, select', markDirty);

	/* ===== Save via AJAX ===== */
	$form.on('submit', function (e) {
		e.preventDefault();

		var $btnLabel = $saveBtn.html();
		$saveBtn.prop('disabled', true);

		$.ajax({
			url: admin_settings.ajax_url,
			type: 'post',
			data: {
				action: 'salert_save_settings_with_ajax',
				security: admin_settings.ajax_nonce,
				fields: $form.serialize()
			},
			success: function () {
				swal({
					type: 'success',
					title: 'Settings Saved!',
					showConfirmButton: false,
					timer: 2000
				});
				$saveBtn.removeClass('save-now').prop('disabled', false);
				$notice.removeClass('visible');
			},
			error: function () {
				$saveBtn.prop('disabled', false);
				swal('Oops...', 'Something went wrong!', 'error');
			}
		});
	});

	/* ===== Color pickers → live preview ===== */
	$('#bg-color').wpColorPicker({
		change: function (e, ui) { $template.css('background-color', ui.color.toString()); },
		clear: function () { $template.css('background-color', '#fff'); }
	});
	$('#text-color').wpColorPicker({
		change: function (e, ui) { $template.css('color', ui.color.toString()); },
		clear: function () { $template.css('color', '#000'); }
	});
	$('#border-color').wpColorPicker({
		change: function (e, ui) { $template.css('border-color', ui.color.toString()); },
		clear: function () { $template.css('border-color', '#e0e0e0'); }
	});

	// Initial color state
	$template.css('background-color', $('#bg-color').val());
	$template.css('color', $('#text-color').val());

	/* ===== Numeric inputs → live preview ===== */
	function applyNum($el, prop, suffix) {
		var v = parseInt($el.val(), 10);
		if (isNaN(v)) { v = 0; }
		$template.css(prop, v + (suffix || ''));
	}
	applyNum($('#font-size'), 'fontSize', 'px');
	applyNum($('#inner-padding'), 'padding');
	applyNum($('#container-width'), 'width', 'px');

	$form.on('input', '#font-size', function () { applyNum($(this), 'fontSize', 'px'); });
	$form.on('input', '#container-width', function () { applyNum($(this), 'width', 'px'); });

	$form.on('input', '#inner-padding', function () {
		$template.find('.popup-item').css('padding', parseInt($(this).val(), 10) + 'px');
	});
	$template.find('.popup-item').css('padding', parseInt($('#inner-padding').val(), 10) + 'px');

	/* ===== Selects → live preview ===== */
	$('#popup-position').on('change', function () {
		$('#salert-preview-position')
			.removeClass('pos-topLeft pos-topRight pos-bottomLeft pos-bottomRight')
			.addClass('pos-' + $(this).val());
	}).trigger('change');

	$('#image-position').on('change', function () {
		var cls = ['imageOnLeft', 'imageOnRight', 'textOnly'];
		cls.forEach(function (c) { $template.removeClass(c); });
		$template.addClass($(this).val());
	});

	$('#image-style').on('change', function () {
		if ($(this).val() === 'circle') {
			$template.find('img.pimg').css('border-radius', '50%');
		} else {
			$template.find('img.pimg').css('border-radius', '0');
		}
	}).trigger('change');

	$('#text-transform').on('change', function () {
		$template.css('text-transform', $(this).val());
	}).trigger('change');

	/* ===== Toggles ===== */
	// Close button
	(function () {
		var $close = $template.find('.close');
		function apply(checked) { checked ? $close.show() : $close.hide(); }
		$form.on('change', 'input[name="close-btn"]', function () { apply($(this).is(':checked')); });
		apply($('input[name="close-btn"]').is(':checked'));
	})();

	// Box shadow
	(function () {
		function apply(checked) { $template.toggleClass('boxs', checked); }
		$form.on('change', 'input[name="box-shadow"]', function () { apply($(this).is(':checked')); });
		apply($('input[name="box-shadow"]').is(':checked'));
	})();

	// Border enable
	(function () {
		function apply(checked) {
			$template.toggleClass('border', checked);
			$('#salert-border-options').toggleClass('visible', checked);
		}
		$form.on('change', 'input[name="border-enable"]', function () { apply($(this).is(':checked')); });
		apply($('input[name="border-enable"]').is(':checked'));
	})();

	// Border radius / width
	function applyBorderRadius() { $template.css('border-radius', (parseInt($('#border-radius').val(), 10) || 0) + 'px'); }
	function applyBorderWidth() { $template.css('border-width', (parseInt($('#border-width').val(), 10) || 0) + 'px'); }
	applyBorderRadius();
	applyBorderWidth();
	$form.on('input', '#border-radius', applyBorderRadius);
	$form.on('input', '#border-width', applyBorderWidth);

	/* ===== Replay animation ===== */
	$('#salert-replay').on('click', function () {
		var anim = $('#popup-animation').val();
		$template.removeClass('animated ' + anim);
		void $template[0].offsetWidth; // force reflow to restart CSS animation
		$template.addClass('animated ' + anim);
	});

	$('#popup-animation').on('change', function () {
		$('#salert-replay').trigger('click');
	});

	/* ===== Message template tag insertion ===== */
	$('.salert-tags code').on('click', function () {
		var $ta = $('#popup-contents');
		$ta.val($ta.val() + $(this).text()).focus();
		markDirty();
	});

	/* ===== Products repeater ===== */
	var tCount = parseInt($('#table_products_count').val(), 10) || 0;

	$(document).on('click', '.docopy-table-product', function () {
		tCount++;
		$('#table_products_count').val(tCount);
		var html =
			'<div class="single-product">' +
				'<div class="salert-product-thumb"></div>' +
				'<div class="salert-product-fields">' +
					'<input type="text" name="popup-products[title][' + tCount + ']" placeholder="Product name" value="" required/>' +
					'<div class="salert-product-row">' +
						'<input type="text" class="salert-image-url" name="popup-products[url][' + tCount + ']" placeholder="Image URL" value="">' +
						'<button type="button" class="button salert-upload-btn"><span class="dashicons dashicons-upload"></span></button>' +
					'</div>' +
					'<input type="text" name="popup-products[link][' + tCount + ']" placeholder="Link (https://)" value="">' +
				'</div>' +
				'<button type="button" class="button-link delete-product" aria-label="Remove product"><span class="dashicons dashicons-trash"></span></button>' +
			'</div>';
		$('#salert-products-list').append(html);
		markDirty();
	});

	$(document).on('click', '.delete-product', function () {
		$(this).closest('.single-product').remove();
		markDirty();
	});

	// Live thumbnail from image URL
	$(document).on('input', '.salert-image-url', function () {
		var url = $(this).val();
		var $thumb = $(this).closest('.single-product').find('.salert-product-thumb');
		if (url) {
			$thumb.html('<img src="' + url + '" alt="">');
		} else {
			$thumb.empty();
		}
	});

	/* ===== Media uploader ===== */
	$(document).on('click', '.salert-upload-btn', function (e) {
		e.preventDefault();
		var $row = $(this).closest('.single-product');
		var image = wp.media({
			title: 'Select Product Image',
			multiple: false
		}).open().on('select', function () {
			var attachment = image.state().get('selection').first().toJSON();
			$row.find('.salert-image-url').val(attachment.url).trigger('input');
		});
	});
	initialized = true;
});