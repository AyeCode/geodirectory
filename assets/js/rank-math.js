/**
 * Rank Math SEO Integration
 */
; (function ($) {

	/**
	 * RankMath integration class
	 */
	var RankMathIntegration = function () {
		this.init()
		this.hooks()
	}

	/**
	 * Init the plugin
	 */
	RankMathIntegration.prototype.init = function () {
		this.pluginName = 'geodirectory'
	}

	/**
	 * Hook into Rank Math App eco-system
	 */
	RankMathIntegration.prototype.hooks = function () {
		var self = this

		RankMathApp.registerPlugin(this.pluginName)
		wp.hooks.addFilter('rank_math_content', this.pluginName, $.proxy(this.filterContent, this))
		window.setInterval(function () {
			RankMathApp.reloadPlugin(self.pluginName)
		}, 2000);
	}

	/**
	 * Gather ge specific field data for analysis
	 *
	 * @return {String}
	 */
	RankMathIntegration.prototype.getContent = function () {
		var content = ''

		//Add images
		$('.plupload-thumbs img').each(function () {
			var img = $(this).clone()
			img.attr('alt', img.data('title'))
			content += '<p>' + img[0].outerHTML + '.</p>'
		})

		//Add textarea fields
		$('.gd-fieldset-details textarea').each(function () {
			var val = $(this).val()
			if (val.length) {
				content += '<p>' + val + '</p>'
			}
		})

		//Finally, input fields
		$('input.geodir_textfield').each(function () {
			var val = $(this).val()
			var label = $(this).closest('.gd-fieldset-details').find('label').text()

			if ('url' == $(this).attr('type') && val.length) {
				val = '<a href="' + val + '">' + label + ' - ' + val + '</a>'
			} else {
				val = label + ' - ' + val
			}

			if (val.length) {
				content += '<p>' + val + '.</p>'
			}
		})

		return content
	}


	/**
	 * Filters rankmat content
	 *
	 * @param {String} content System content.
	 *
	 * @return {String} Our plugin content concatenated
	 */
	RankMathIntegration.prototype.filterContent = function (content) {
		return content + this.getContent()
	}


	/**
	 * Start Analysing our Fields.
	 */
	$(document).on('ready', function () {
		new RankMathIntegration()
	})

})(jQuery)