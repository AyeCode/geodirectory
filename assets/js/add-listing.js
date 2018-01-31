/**
 * Functions for saving and updating listings.
 */


/**
 * Document load functions
 */
jQuery(function() {
    console.log( "ready!" );
    // Start polling the form for auto saves
    geodir_auto_save_poll(geodir_get_form_data());

    /// check validation on blur
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("[field_type]:visible, .editor textarea").blur(function () {
        geodir_validate_field(this);
    });

    // Check for validation on click for checkbox, radio
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("input[type='checkbox'],input[type='radio']").click(function () {
        geodir_validate_field(this);
    });

});

/**
 * Prevent navigation away if there are unsaved changes.
 */
var geodir_changes_made = false;
window.onbeforeunload = function() {
    return geodir_changes_made ? "You may lose changes if you navigate away now!" : null; // @todo make translatable
};

/**
 * Poll the form looking for changes every 10 seconds, if we detect a change then auto save
 *
 * @param old_form_data
 */
function geodir_auto_save_poll(old_form_data){
    if(jQuery("#geodirectory-add-post").length){
        setTimeout(function(){
            // only save if the forum data has changed
            if(old_form_data != geodir_get_form_data()){
                console.log('form has changed');
                geodir_auto_save_post();
                geodir_changes_made = true; // flag changes have been made
            }
            geodir_auto_save_poll(geodir_get_form_data()); // run the function again.
        }, 10000);
    }
}

/**
 * Saves the post in the background via ajax.
 */
function geodir_auto_save_post(){
    var form_data = geodir_get_form_data();
    form_data += "&action=geodir_auto_save_post";

    jQuery.ajax({
        type: "POST",
        url: geodirectory_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data)
        {
            if(data.success){
                console.log('auto saved');
            }else{
                console.log('auto save failed');
            }
        }
    });
}

/**
 * Get all the form data.
 *
 * @returns {*}
 */
function geodir_get_form_data(){
    return jQuery("#geodirectory-add-post").serialize();
}

/**
 * Save the post and redirect to where needed.
 */
function geodir_save_post(){
    var form_data = geodir_get_form_data();

    console.log(form_data);

    jQuery.ajax({
        type: "POST",
        url: geodirectory_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data)
        {
            if(data.success){
                console.log('saved');
                console.log(data.data);
                geodir_changes_made = false; // set the changes flag to false.
                jQuery('.gd-notification').remove(); // remove current notes
                jQuery('#geodirectory-add-post').replaceWith(data.data); // remove the form and replae with the notification
                jQuery(window).scrollTop(jQuery('.gd-notification').offset().top-100);// scroll to new notification

                return true;
            }else{
                console.log('save failed');
                return false;
            }
        }
    });

}

/**
 * Delete a post revision.
 */
function geodir_delete_revision(){

    var form_data = geodir_get_form_data();
    form_data += "&action=geodir_delete_revision";

    jQuery.ajax({
        type: "POST",
        url: geodirectory_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data)
        {
            if(data.success){
                console.log('deleted');
                location.reload();
                return true;
            }else{
                console.log('delete failed');
                alert(data.data);
                return false;
            }
        }
    });
}

/**
 * Save the post on preview link click.
 */
jQuery( ".geodir_preview_button" ).click(function() {
    geodir_auto_save_post();
    $form = jQuery("#geodirectory-add-post");

    return geodir_validate_submit($form);
});

/**
 * Save the post via ajax.
 */
jQuery("#geodirectory-add-post").submit(function(e) {

    $valid = geodir_validate_submit(this);

    if($valid){
        $result = geodir_save_post();
    }

    e.preventDefault(); // avoid to execute the actual submit of the form.
});



/**
 * Validate all required fields before submit.
 *
 * @returns {boolean}
 */
function geodir_validate_submit(form){
    var is_validate = true;

    jQuery(form).find(".required_field:visible").each(function () {
        jQuery(this).find("[field_type]:visible, .chosen_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload").each(function () {

            // if (jQuery(this).is('.chosen_select, .geodir_location_add_listing_chosen')) {
            //     var chosen_ele = jQuery(this);
            //     jQuery('#' + jQuery(this).attr('id') + '_chzn').mouseleave(function () {
            //         geodir_validate_field(chosen_ele);
            //     });
            // }

            if (!geodir_validate_field(this)){
                is_validate = false;
                //console.log(false);
            }else{
                //console.log(true);
            }

            //console.log(this);


        });


    });


    if (is_validate) {
        return true;
    } else {

        jQuery(window).scrollTop(jQuery(".geodir_message_error:visible:first").closest('.required_field').offset().top);
        return false;
    }
}


/**
 * Validate add listing fields.
 *
 * @param field
 * @returns {boolean}
 */
function geodir_validate_field(field) {

    var is_error = true;
    switch (jQuery(field).attr('field_type')) {
        case 'radio':
        case 'checkbox':

            if (jQuery(field).closest('.required_field').find('#cat_limit').length) {

                var cat_limit = jQuery(field).closest('.required_field').find('#cat_limit').attr('cat_limit');
                var cat_msg = jQuery(field).closest('.required_field').find('#cat_limit').val();

                if (jQuery(field).closest('.required_field').find(":checked").length > cat_limit && cat_limit > 0) {

                    jQuery(field).closest('.required_field').find('.geodir_message_error').show();
                    jQuery(field).closest('.required_field').find('.geodir_message_error').html(cat_msg);
                    return false;

                }

            }

            if (jQuery(field).closest('.required_field').find(":checked").length > 0) {
                is_error = false;
            }
            break;

        case 'select':
            if (jQuery(field).closest('.geodir_form_row').find(".geodir_taxonomy_field").length > 0 && jQuery(field).closest('.geodir_form_row').find("#post_category").length > 0) {
                if (jQuery(field).closest('.geodir_form_row').find("#post_category").val() != '') {
                    is_error = false;
                }
            } else {
                if (jQuery(field).find("option:selected").length > 0 && jQuery(field).find("option:selected").val() != '') {
                    is_error = false;
                }
            }
            break;

        case 'multiselect':
            if (jQuery(field).closest('.required_field').find('#cat_limit').length) {

                var cat_limit = jQuery(field).closest('.required_field').find('#cat_limit').attr('cat_limit');
                var cat_msg = jQuery(field).closest('.required_field').find('#cat_limit').val();

                if (jQuery(field).find("option:selected").length > cat_limit && cat_limit > 0) {
                    jQuery(field).closest('.required_field').find('.geodir_message_error').show();
                    jQuery(field).closest('.required_field').find('.geodir_message_error').html(cat_msg);
                    return false;

                }

            }

            if (jQuery(field).find("option:selected").length > 0) {
                is_error = false;
            }


            break;

        case 'email':
            var filter = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            if (field.value != '' && filter.test(field.value)) {
                is_error = false;
            }
            break;

        case 'url':
            var filter = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
            if (field.value != '' && filter.test(field.value)) {
                is_error = false;
            }
            break;

        case 'editor':
            if (jQuery('#' + jQuery(field).attr('field_id')).val() != '') {
                is_error = false;
            }
            break;

        case 'datepicker':
        case 'time':
        case 'text':
        case 'textarea':
            if (field.value != '') {
                is_error = false;
            }
            break;

        case 'address':

            if (jQuery(field).attr('id') == 'post_latitude' || jQuery(field).attr('id') == 'post_longitude') {

                if (/^[0-90\-.]*$/.test(field.value) == true && field.value != '') {
                    is_error = false;
                } else {

                    var error_msg = geodir_params.latitude_error_msg;
                    if (jQuery(field).attr('id') == 'post_longitude')
                        error_msg = geodir_params.longgitude_error_msg;

                    jQuery(field).closest('.required_field').find('.geodir_message_error').show();
                    jQuery(field).closest('.required_field').find('.geodir_message_error').html(error_msg);

                }

            } else {

                if (field.value != '')
                    is_error = false;
            }

            break;

        default:
            if (field.value != '') {
                is_error = false;
            }
            break;

    }


    if (is_error) {
        if (jQuery(field).closest('.required_field').find('span.geodir_message_error').html() == '') {
            jQuery(field).closest('.required_field').find('span.geodir_message_error').html(geodir_params.field_id_required)
        }

        jQuery(field).closest('.required_field').find('span.geodir_message_error').fadeIn();

        return false;
    } else {

        jQuery(field).closest('.required_field').find('span.geodir_message_error').html('');
        jQuery(field).closest('.required_field').find('span.geodir_message_error').fadeOut();

        return true;
    }
}

var GeoDir_Business_Hours = {
	init: function(params) {
		var $this = this;
		console.log(params);
		this.wrap = jQuery('[name="' + params.field + '"]').closest('.gd-bh-row');
		this.sample = jQuery('.gd-bh-items .gd-bh-blank').html();
		
		// load timepickers
		this.timepickers();
		
		jQuery('[data-field="active"]', this.wrap).on("change", function(e){
			$wrap = this.wrap;
			if (jQuery(this).val() == '1') {
				jQuery('.gd-bh-items', $wrap).slideDown(200);
			} else {
				jQuery('.gd-bh-items', $wrap).slideUp(200);
			}
		});
		
		// add slot
		jQuery(".gd-bh-add", this.wrap).on("click",function(e){
			$this.addSlot(jQuery(this));
			$this.onAddCancelSlot();
			e.preventDefault();
		});
		
		if (jQuery('.gd-bh-hours').length) {
			$this.onAddCancelSlot();
		}
	},
	
	addSlot: function($el) {
		var sample = this.sample;
		var $item = $el.closest('.gd-bh-item');
		jQuery('.gd-bh-closed', $item).remove();
		sample = sample.replace('data-field="open"', 'data-field="open" name="' + jQuery('.gd-bh-time', $item).data('field') + '[open][]"');
		sample = sample.replace('data-field="close"', 'data-field="close" name="' + jQuery('.gd-bh-time', $item).data('field') + '[close][]"');
		jQuery('.gd-bh-time', $item).append(sample);
		this.onAddCancelSlot();
	},
	
	cancelSlot: function($el) {
		var $item = $el.closest('.gd-bh-time');
		$el.closest('.gd-bh-hours').remove();
		if (jQuery('.gd-bh-hours', $item).length < 1) {
			$item.html('<div class="gd-bh-closed">' + geodir_params.txt_closed + '</div>');
		}
	},
	
	onAddCancelSlot: function() {
		jQuery(".gd-bh-remove").on("click", function(e) {
			var $item = jQuery(this).closest('.gd-bh-time');
			jQuery(this).closest('.gd-bh-hours').remove();
			if (jQuery('.gd-bh-hours', $item).length < 1) {
				$item.html('<div class="gd-bh-closed">' + geodir_params.txt_closed + '</div>');
			}
			e.preventDefault();
		});
		this.timepickers();
	},
	
	toSchema: function(data) {
		console.log(data);
	},
	
	toArray: function(data) {
		console.log(data);
	},
	
	timepickers: function() {
		jQuery(this.wrap).find('[data-bh="time"]').each(function() {
			var $el = jQuery(this);
			if (!$el.hasClass('hasDatepicker')) {
				$el.timepicker({
					timeFormat: 'HH:mm',
					showPeriod: true,
					showLeadingZero: true,
					showPeriod: true,
				});
			}
		});
	},
	
	padTimes: function(text) {
    text = text.replace(/([^0-9]|^)([0-9]{3})([^0-9]|$)/g, '$10$2$3');
    text = text.replace(/([^0-9]|^)([0-9]{3})([^0-9]|$)/g, '$10$2$3');
    text = text.replace(/([^0-9]|^)([0-9]{2})([^0-9]|$)/g, '$1$200$3');
    text = text.replace(/([^0-9]|^)([0-9]{2})([^0-9]|$)/g, '$1$200$3');
    text = text.replace(/([^0-9]|^)([0-9])([^0-9]|$)/g, '$10$200$3');
    text = text.replace(/([^0-9]|^)([0-9])([^0-9]|$)/g, '$10$200$3');
    return text;
  },

  toTimeframe: function(days, startMinutes, endMinutes) {
    // If we've day wrapped and end before 4am, push the ending value up 24 hours.
    if (startMinutes >= endMinutes && endMinutes <= 240) {
      endMinutes += 1440;
    }
    var startFormatted = fourSq.util.Hours.formatMinutes(startMinutes);
    var endFormatted = fourSq.util.Hours.formatMinutes(endMinutes);

    return /** @type {fourSq.api.models.hours.MachineTimeframe} */ (({
      days: days,
      open: [(/** @type {fourSq.api.models.hours.MachineSegment} */({
        start: startFormatted,
        end: endFormatted
      }))]
    }));
  },

  formatMinutes: function(minutes) {
    var hh = Math.floor(minutes / 60);
    var mm = minutes % 60;
    var intoNextDay = ((hh % 24) !== hh);
    hh = (hh % 24);
    if (hh % 10 === hh) {
      hh = '0' + hh;
    }
    if (intoNextDay) {
      hh = '+' + hh;
    }
    if (mm % 10 === mm) {
      mm = '0' + mm;
    }
    return hh + '' + mm;
  },

  minutesAfterMidnight: function(hoursText, minutesText, meridiem) {
    var hours = parseInt(hoursText, 10);
    var minutes = (minutesText !== undefined) ? parseInt(minutesText, 10) : 0;
    if (hours === 12 && meridiem) {
      hours -= 12;
    }
    if (meridiem && meridiem[0] === 'p') {
      hours += 12;
    }

    return (hours * 60) + minutes;
  },

  parse: function(text) {
    text = text.toLowerCase();

    // Normalize new lines to ';'
    text = text.replace(/\n/g, ' ; ');

    // Massage times
    // TODO(ss): translate and do weekend/weekday subs
    text = text.replace(/(12|12:00)?midnight/g, '1200a');
    text = text.replace(/(12|12:00)?noon/g, '1200p');
    text = text.replace(/(open)?\s*24\s*hours?/g, '1200a-1200a');

    // Standardize conjunctions to '&'
    text = text.replace(/\s*(and|,|\+|&)\s*/g, '&');

    // Standardize range tokens to '-'
    text = text.replace(/\s*(-|to|thru|through|till?|'till?|until)\s*/g, '-');

    // Standardize am/pm
    text = text.replace(/\s*a\.?m?\.?/g, 'a');
    text = text.replace(/\s*p\.?m?\.?/g, 'p');

    // Not sure this happens, but add trailing zeros to things like 5:3pm
    text = text.replace(/([0-9])(h|:|\.)([0-9])([^0-9]|$)/g, '$1$2$30$4');

    // Remove separators from times (e.g. ':')...
    // if they both have separators
    text = text.replace(/([0-9]+)\s*[^0-9]\s*([0-9]{2})([^0-9]+?)([0-9]+)\s*[^0-9]\s*([0-9]{2})/g, '$1$2$3$4$5');
    // if only the start time has a separator
    text = text.replace(/([0-9]+)\s*(h|:|\.)\s*([0-9]{2})/g, '$1$3');
    // if only the end time has a separator
    //text = text.replace(/([0-9]+)([^0-9ap]+?)([0-9]+)\s*(h|:|\.)\s*([0-9]{2})/g, '$1$2$3$5');

    text = fourSq.util.Hours.padTimes(text);

    var dayCanonicals = _.map(_.range(1, 8), function(dayI) {
      var allNames = fourSq.util.HoursParser.dayAliases(dayI);
      var canonical = _.head(allNames); // Shortest is at the front
      var aliases = _.tail(allNames);
      aliases.reverse();  // Need to have the largest alias first for replacing
      if (canonical && aliases) {
        _.each(aliases, function(alias) {
          text = text.replace(new RegExp(alias, 'g'), canonical);
        });
      }
      return canonical;
    });

    var dayPattern = '(' + dayCanonicals.join('|') + ')';
    var timePattern = '([0-9][0-9])([0-9][0-9])\\s*([ap])?';
    var globTimePattern =  '[0-9]{4}\\s*[ap]?';
    var globTimeRangePattern = '(' + globTimePattern + '[^0-9]+' + globTimePattern + ')';

    // Need to establish whether days come before times (forward) or not (backward)
    var forwardTimeframePattern = dayPattern + '.*?' + globTimeRangePattern;
    var backwardTimeframePattern = globTimeRangePattern + '.*?' + dayPattern;

    var forwardPosition = text.search(new RegExp(forwardTimeframePattern));
    var backwardPosition = text.search(new RegExp(backwardTimeframePattern));

    // If a forward pattern is found first, consider it a forward facing text
    var isForward = (forwardPosition !== -1 && forwardPosition <= backwardPosition) || backwardPosition === -1;
    // TODO(ss): may be better to normalize the string to be forward facing at this point
    //           so the rest of the method would be easier to grok

    // Separate out something like Mon-Thu, Sat, Sun
    if (isForward) {
      var ungroupedPattern = dayPattern + '&' + dayPattern + '[^&]*?' + globTimeRangePattern;
      var ungroupedRegex = new RegExp(ungroupedPattern, 'g');
      for (var i = 0; i < dayCanonicals.length; ++i) {
        text = text.replace(ungroupedRegex, '$1 $3; $2 $3; ');
      }
    } else {
      var ungroupedPattern = globTimeRangePattern + '([^0-9]*?)' + dayPattern + '&' + dayPattern;
      var ungroupedRegex = new RegExp(ungroupedPattern, 'g');
      for (var i = 0; i < dayCanonicals.length; ++i) {
        text = text.replace(ungroupedRegex, '$1 $2 $3; $1 $4; ');
      }
    }

    var dayRangePattern = dayPattern + '[^a-z0-9]*' + dayPattern + '?';
    var timeRangePattern = timePattern + '[^0-9]*' + timePattern;
    var timeframePattern = isForward ? (
      dayRangePattern + '.*?' + timeRangePattern
    ) : (
      timeRangePattern + '.*?' + dayRangePattern
    );
    var dayTimeMatcher = new RegExp(timeframePattern, 'g');

    var matches = [];
    do {
      var dayTimeMatch = dayTimeMatcher.exec(text);
      if (dayTimeMatch) {
        matches.push(dayTimeMatch);
      }
    } while (dayTimeMatch)

    if (matches.length <= 0) {
      // Try to find just a time range, and then we'll assume it's all days later on.
      // First two groups are strings that won't match, to get undefined values
      // in those slots in the regex match array.
      var timeRangeMatcher = new RegExp('(@!ZfW#)?(@!ZfW#)?' + timeRangePattern);
      var timeRangeMatch = timeRangeMatcher.exec(text);
      if (timeRangeMatch) {
        matches.push(timeRangeMatch);
      }
    }

    var timeframes = _.map(matches, function(match) {
      // day slots in the regex match array
      var day1 = isForward ? match[1] : match[7];
      var day2 = isForward ? match[2] : match[8];
      var startDay = (day1 !== undefined) ? dayCanonicals.indexOf(day1) : 0;

      var endDay = null;
      if (day2 !== undefined) {
        if (day1 === undefined) {
          startDay = dayCanonicals.indexOf(day2);
        } else {
          endDay = dayCanonicals.indexOf(day2);
        }
      } else if (day1 === undefined) {
        // If start and end days were undefined, assume 7 days a week
        endDay = 7;
      }
      if (endDay === null) {
        endDay = startDay;
      }

      if (endDay < startDay) {
        // For case where: Sun-Tue (we start on Monday)
        endDay += 7;
      }
      var days = _.map(_.range(startDay, endDay + 1), function(day) {
        // Days start at 1 for Monday
        return (day % 7) + 1;
      });

      // time slots in the regex match array
      var startHour = isForward ? match[3] : match[1];
      var startMinute = isForward ? match[4] : match[2];
      var startMeridiem = isForward ? match[5] : match[3];
      var endHour = isForward ? match[6] : match[4];
      var endMinute = isForward ? match[7] : match[5];
      var endMeridiem = isForward ? match[8] : match[6];
      // TODO(ss): hint the meridiem based on endHour < startHour and > 4
      var startTime = fourSq.util.Hours.minutesAfterMidnight(startHour, startMinute, startMeridiem);
      var endTime = fourSq.util.Hours.minutesAfterMidnight(endHour, endMinute, endMeridiem);
      return fourSq.util.Hours.toTimeframe(days, startTime, endTime);
    });

    if (timeframes.length) {
      return /** @type {fourSq.api.models.hours.MachineHours} */ (({
        timeframes: timeframes
      }));
    } else {
      return null;
    }
  },

  dayAliases: function(day) {
		var text = '';
		switch(day) {
		  case 1: aliases = ['mondays','monday','monda','mond','mon','mo','m']; break;
		  case 2: aliases = ['tuesdays','tuesday','tuesd','tues','tue','tu']; break;
		  case 3: aliases = ['wednesdays','wednesday','wednes','wedne','wedn','wed','we','w']; break;
		  case 4: aliases = ['thursdays','thursday','thurs','thur','thu','th']; break;
		  case 5: aliases = ['fridays','friday','frida','frid','fri','fr','f']; break;
		  case 6: aliases = ['saturdays','saturday','satur','satu','sat','sa']; break;
		  case 7: aliases = ['sundays','sunday','sunda','sund','sun','su']; break;
		  default: return [];
		}
		return _.sortBy(aliases, function(alias) {
		  return alias.length;
		});
	  }
}
