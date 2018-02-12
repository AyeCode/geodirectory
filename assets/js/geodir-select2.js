/* global geodir_select2_params */
jQuery(function($) {
    function geodirSelect2FormatString() {
        return {
            'language': {
                errorLoading: function() {
                    // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
                    return geodir_select2_params.i18n_searching;
                },
                inputTooLong: function(args) {
                    var overChars = args.input.length - args.maximum;
                    if (1 === overChars) {
                        return geodir_select2_params.i18n_input_too_long_1;
                    }
                    return geodir_select2_params.i18n_input_too_long_n.replace('%item%', overChars);
                },
                inputTooShort: function(args) {
                    var remainingChars = args.minimum - args.input.length;
                    if (1 === remainingChars) {
                        return geodir_select2_params.i18n_input_too_short_1;
                    }
                    return geodir_select2_params.i18n_input_too_short_n.replace('%item%', remainingChars);
                },
                loadingMore: function() {
                    return geodir_select2_params.i18n_load_more;
                },
                maximumSelected: function(args) {
                    if (args.maximum === 1) {
                        return geodir_select2_params.i18n_selection_too_long_1;
                    }
                    return geodir_select2_params.i18n_selection_too_long_n.replace('%item%', args.maximum);
                },
                noResults: function() {
                    return geodir_select2_params.i18n_no_matches;
                },
                searching: function() {
                    return geodir_select2_params.i18n_searching;
                }
            }
        };
    }
    try {
        $(document.body).on('geodir-select-init', function() {
            // Regular select boxes
            $(':input.geodir-select').filter(':not(.enhanced)').each(function() {
                var select2_args = $.extend({
                    minimumResultsForSearch: 10,
                    allowClear: $(this).data('allow_clear') ? true : false,
                    containerCssClass: 'gd-select2-selection',
                    dropdownCssClass: 'gd-select2-dropdown',
                    placeholder: $(this).data('placeholder')
                }, geodirSelect2FormatString());
                var $select2 = $(this).select2(select2_args);
                $select2.addClass('enhanced');
                $select2.data('select2').$container.addClass('gd-select2-container');
                $select2.data('select2').$dropdown.addClass('gd-select2-container');
            });
            $(':input.geodir-select-nostd').filter(':not(.enhanced)').each(function() {
                var select2_args = $.extend({
                    minimumResultsForSearch: 10,
                    allowClear: true,
                    containerCssClass: 'gd-select2-selection',
                    dropdownCssClass: 'gd-select2-dropdown',
                    placeholder: $(this).data('placeholder')
                }, geodirSelect2FormatString());
               var $select2 = $(this).select2(select2_args);
               $select2.addClass('enhanced');
               $select2.data('select2').$container.addClass('gd-select2-container');
               $select2.data('select2').$dropdown.addClass('gd-select2-container');
			   if ($(this).data('sortable')) {
					var $select = $(this);
					var $list = $(this).next('.select2-container').find('ul.select2-selection__rendered');
					$list.sortable({
						placeholder: 'ui-state-highlight select2-selection__choice',
						forcePlaceholderSize: true,
						items: 'li:not(.select2-search__field)',
						tolerance: 'pointer',
						stop: function() {
							$($list.find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = $select.find('option[value="' + id + '"]')[0];
								$select.prepend(option);
							});
						}
					});
				}
            });
            $(':input.geodir-select-tags').filter(':not(.enhanced)').each(function() {
                var select2_args = $.extend({
                    tags: true,
                    selectOnClose: true,
                    tokenSeparators: [','],
                    minimumResultsForSearch: 10,
                    allowClear: $(this).data('allow_clear') ? true : false,
                    containerCssClass: 'gd-select2-selection',
                    dropdownCssClass: 'gd-select2-dropdown',
                    placeholder: $(this).data('placeholder')
                }, geodirSelect2FormatString());
                var $select2 = $(this).select2(select2_args);
                $select2.addClass('enhanced');
                $select2.data('select2').$container.addClass('gd-select2-container');
                $select2.data('select2').$dropdown.addClass('gd-select2-container');
				if ($(this).data('sortable')) {
					var $select = $(this);
					var $list = $(this).next('.select2-container').find('ul.select2-selection__rendered');
					$list.sortable({
						placeholder: 'ui-state-highlight select2-selection__choice',
						forcePlaceholderSize: true,
						items: 'li:not(.select2-search__field)',
						tolerance: 'pointer',
						stop: function() {
							$($list.find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = $select.find('option[value="' + id + '"]')[0];
								$select.prepend(option);
							});
						}
					});
				}
            });

        }).trigger('geodir-select-init');
        $('html').on('click', function(event) {
            if (this === event.target) {
                $('.geodir-select').filter('.select2-hidden-accessible').select2('close');
            }
        });
    } catch (err) {
        window.console.log(err);
    }
});