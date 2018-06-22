(function ($) {
	var yafa_selector_type_map = {
		'address-street_address': 'text',
		'address-address_line'  : 'text',
		'address-city'          : 'text',
		'address-state'         : 'text',
		'address-zip'           : 'text',
		'address-country'       : 'select',
		'date'                  : 'date',
		'email'                 : 'text',
		'hidden'                : 'text',
		'checkbox'              : 'checkbox',
		'gdprcheckbox'          : 'checkbox',
		'name'                  : 'text',
		'name-prefix'           : 'text',
		'name-first-name'       : 'text',
		'name-middle-name'      : 'text',
		'name-last-name'        : 'text',
		'number'                : 'text',
		'phone'                 : 'text',
		'postdata-post-title'   : 'text',
		'postdata-post-content' : 'text',
		'postdata-post-excerpt' : 'text',
		'postdata-post-category': 'checkbox',
		'postdata-post-tags'    : 'checkbox',
		'postdata-post-image'   : 'upload',
		'select'                : 'select',
		'text'                  : 'text',
		'time'                  : 'time',
		'upload'                : 'upload',
		'url'                   : 'text',
	};

	$(document).ready(function () {
		console.log('yafa JS attached');
		$.each($('.forminator-custom-form'), function () {
			var $form   = $(this);
			// find config
			var config  = $form.find('.forminator-yafa-inputs');
			var is_edit = $(config).data('is-edit');
			if (config.length > 0) {
				// parsing pre-fill config
				var pre_fill_config = $(config).find('.forminator-yafa-field-pre-fill');
				if (pre_fill_config.length > 0) {
					$.each(pre_fill_config, function () {
						var $pre_fill_field_config = $(this);
						var field_type             = $pre_fill_field_config.data('field-type');
						var field_id               = $pre_fill_field_config.data('field-id');
						var field_value            = $pre_fill_field_config.data('field-value');


						if ('text' === yafa_selector_type_map[field_type]) {
							$form.find('input[name=' + field_id + ']').val(field_value);
							// can be text area
							$form.find('textarea[name=' + field_id + ']').val(field_value);
						} else if ('select' === yafa_selector_type_map[field_type]) {
							// prefill for select
						} else if ('date' === yafa_selector_type_map[field_type]) {
							// prefill for date
						} else if ('checkbox' === yafa_selector_type_map[field_type]) {
							// prefill for checkbox
						} else if ('upload' === yafa_selector_type_map[field_type]) {
							// prefill for upload
						} else if ('time' === yafa_selector_type_map[field_type]) {
							// prefill for time
						}

					})
				}

				// parsing disable config
				var disabled_config = $(config).find('.forminator-yafa-field-disable');
				if (disabled_config.length > 0) {
					$.each(disabled_config, function () {
						var $disable_config = $(this);
						var field_type      = $disable_config.data('field-type');
						var field_id        = $disable_config.data('field-id');

						if ('text' === yafa_selector_type_map[field_type]) {
							$form.find('input[name=' + field_id + ']').attr('readonly', 'readonly')
							// can be text area
							$form.find('textarea[name=' + field_id + ']').attr('disabled', 'disabled')
						} else if ('select' === yafa_selector_type_map[field_type]) {
							// prefill for select
						} else if ('date' === yafa_selector_type_map[field_type]) {
							// prefill for date
						} else if ('checkbox' === yafa_selector_type_map[field_type]) {
							// prefill for checkbox
						} else if ('upload' === yafa_selector_type_map[field_type]) {
							// prefill for upload
						} else if ('time' === yafa_selector_type_map[field_type]) {
							// prefill for time
						}

					})
				}
			}

			// to reload new filled data
			$form.on('submit.frontSubmit', function () {
				location.reload();
			});

		})

	});
})(jQuery);
