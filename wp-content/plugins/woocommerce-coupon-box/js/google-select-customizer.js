jQuery( document ).ready(function($) {
	"use strict";
	/**
	 * Googe Font Select Custom Control
	 *
	 * @author Anthony Hortin <http://maddisondesigns.com>
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @link https://github.com/maddisondesigns
	 */

	// $('.wcb-google-fonts-list').each(function (i, obj) {
	// 	if (!$(obj).hasClass('select2-hidden-accessible')) {
	// 		$(obj).select2({placeholder: "Select font",allowClear: true});
	// 	}
	// });

	$('.wcb-google-fonts-list').on('change', function() {
		var selectedFont = $(this).val();
		var customizerControlName = $(this).attr('control-name');

		// Get the Google Fonts control object
		var bodyfontcontrol = _wpCustomizeSettings.controls[customizerControlName];

		// Find the index of the selected font
		var indexes = $.map(bodyfontcontrol.skyrocketfontslist, function(obj, index) {
			if(obj.family === selectedFont) {
				return index;
			}
		});
		var index = indexes[0];
		// Update the font category based on the selected font

		skyrocketGetAllSelects($(this).parent().parent());
	});

	$('.google_fonts_select_control select').on('change', function() {
		skyrocketGetAllSelects($(this).parent().parent());
	});
	$('.wcb-remove-font').on('click',function () {
        $(this).find("#dynamicAttributes").val(null).trigger("change");
    })
	function skyrocketGetAllSelects($element) {
		var selectedFont = {
			font: $element.find('.wcb-google-fonts-list').val(),
		};

		// Important! Make sure to trigger change event so Customizer knows it has to save the field
		$element.find('.customize-control-google-font-selection').val(JSON.stringify(selectedFont)).trigger('change');
	}
});
