/**
 * Business Hours Widget for add-listing form
 * Manages opening hours with time slots, timezones, and 24-hour mode
 */

/**
 * Business Hours Manager Class
 */
export class BusinessHoursManager {
	constructor(params) {
		this.params = params;
		this.field = params.field;
		this.fieldElement = document.querySelector(`[name="${this.field}"]`);

		if (!this.fieldElement) return;

		this.wrap = this.fieldElement.closest('.gd-bh-row');
		if (!this.wrap) return;

		const blankSlot = this.wrap.querySelector('.gd-bh-items .gd-bh-blank');
		this.slotTemplate = blankSlot ? blankSlot.innerHTML : '';

		this.defaultTimezoneString = window.geodir_params?.timezone_string || '';
		this.defaultOffset = window.geodir_params?.gmt_offset || '0';
		this.gmtOffset = params.offset || this.defaultOffset;

		this.init();
	}

	init() {
		this.setupActiveToggle();
		this.setupTimezoneChange();
		this.setupAddSlot();
		this.setup24HoursToggle();

		// Initialize existing slots after a short delay
		setTimeout(() => {
			this.initializeExistingSlots();
			this.attachRemoveHandlers();
			this.attachChangeHandlers();
		}, 100);
	}

	setupActiveToggle() {
		const activeToggle = this.wrap.querySelector('[data-field="active"]');
		if (!activeToggle) return;

		activeToggle.addEventListener('change', (e) => {
			const itemsContainer = this.wrap.querySelector('.gd-bh-items');

			if (activeToggle.value === '1') {
				if (itemsContainer) {
					itemsContainer.style.display = 'block';
				}

				// Reinitialize select2 if needed
				const timezoneSelect = this.wrap.querySelector('[data-field="timezone_string"]');
				if (timezoneSelect && timezoneSelect.classList.contains('select2-hidden-accessible')) {
					// Destroy and reinitialize select2 (if using select2)
					// For Choices.js, similar logic would apply
				}
			} else {
				if (itemsContainer) {
					itemsContainer.style.display = 'none';
				}
			}

			this.setValue();
			e.preventDefault();
		});
	}

	setupTimezoneChange() {
		const timezoneSelect = this.wrap.querySelector('[data-field="timezone_string"]');
		if (!timezoneSelect) return;

		timezoneSelect.addEventListener('change', (e) => {
			this.setValue();
			e.preventDefault();
		});

		// Auto-detect timezone when lat/lng changes
		const form = this.wrap.closest('form');
		if (form) {
			const latField = form.querySelector('[name="latitude"]');
			const lngField = form.querySelector('[name="longitude"]');

			if (latField && lngField) {
				[latField, lngField].forEach(field => {
					field.addEventListener('change', () => {
						if (!window.gdTzApi) {
							window.gdTzApi = true;
							setTimeout(() => {
								this.getTimezone(timezoneSelect);
							}, 1000);
						}
					});
				});
			}
		}
	}

	setupAddSlot() {
		const addButtons = this.wrap.querySelectorAll('.gd-bh-add');

		addButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				this.addSlot(button);
			});
		});
	}

	setup24HoursToggle() {
		const checkboxes = this.wrap.querySelectorAll('.gd-bh-24hours [type="checkbox"]');

		checkboxes.forEach(checkbox => {
			checkbox.addEventListener('click', () => {
				this.handle24HoursChange(checkbox);
			});
		});
	}

	initializeExistingSlots() {
		// Handle existing 24-hour slots
		const items24 = this.wrap.querySelectorAll('.gd-bh-has24');
		items24.forEach(item => {
			this.handle24Hours(item.closest('.gd-bh-item'));
		});

		// Initialize change handlers for existing slots
		const existingSlots = this.wrap.querySelectorAll('.gd-bh-hours');
		if (existingSlots.length > 0) {
			this.attachChangeHandlers();
		}
	}

	addSlot(button) {
		const item = button.closest('.gd-bh-item');
		if (!item) return;

		const uniqueId = Math.floor(Math.random() * 100000000000).toString();

		// Remove "closed" message
		item.querySelectorAll('.gd-bh-closed').forEach(el => el.remove());
		item.classList.remove('gd-bh-item-closed');

		// Show 24-hour checkbox
		const checkbox24 = item.querySelector('.gd-bh-24hours [type="checkbox"]');
		if (checkbox24) {
			checkbox24.style.display = '';
		}

		// Prepare slot HTML from template
		let slotHTML = this.slotTemplate.replace(/GD_UNIQUE_ID/g, uniqueId);

		const timeContainer = item.querySelector('.gd-bh-time');
		if (!timeContainer) return;

		const fieldName = timeContainer.dataset.field;

		slotHTML = slotHTML.replace('data-field-alt="open"',
			`data-field-alt="open" name="${fieldName}[open][]" data-aui-init="flatpickr"`);
		slotHTML = slotHTML.replace('data-field-alt="close"',
			`data-field-alt="close" name="${fieldName}[close][]" data-aui-init="flatpickr"`);

		timeContainer.insertAdjacentHTML('beforeend', slotHTML);

		// Reinitialize time pickers (if using AUI or other library)
		this.initTimePickers();

		// Attach handlers to new slot
		this.attachRemoveHandlers();
		this.attachChangeHandlers();
	}

	removeSlot(button) {
		const hoursSlot = button.closest('.gd-bh-hours');
		if (!hoursSlot) return;

		const timeContainer = hoursSlot.closest('.gd-bh-time');
		const item = timeContainer.closest('.gd-bh-item');

		// Remove the slot
		hoursSlot.remove();

		// Check if this was the last slot
		const remainingSlots = timeContainer.querySelectorAll('.gd-bh-hours');
		if (remainingSlots.length === 0) {
			item.classList.add('gd-bh-item-closed');

			// Hide 24-hour checkbox
			const checkbox24 = item.querySelector('.gd-bh-24hours [type="checkbox"]');
			if (checkbox24) {
				checkbox24.style.display = 'none';
			}

			// Show "closed" message
			const closedText = window.geodir_params?.txt_closed || 'Closed';
			timeContainer.innerHTML = `<div class="gd-bh-closed text-center">${closedText}</div>`;
		}

		this.setValue();
	}

	attachRemoveHandlers() {
		const removeButtons = this.wrap.querySelectorAll('.gd-bh-remove');

		removeButtons.forEach(button => {
			// Remove existing listener (if any) to avoid duplicates
			const newButton = button.cloneNode(true);
			button.parentNode.replaceChild(newButton, button);

			newButton.addEventListener('click', (e) => {
				e.preventDefault();
				this.removeSlot(newButton);
			});
		});
	}

	attachChangeHandlers() {
		const timeInputs = this.wrap.querySelectorAll(`[name^="${this.field}_f[hours]"]`);

		timeInputs.forEach(input => {
			const newInput = input.cloneNode(true);
			input.parentNode.replaceChild(newInput, input);

			newInput.addEventListener('change', (e) => {
				const item = newInput.closest('.gd-bh-item');
				this.handle24Hours(item);
				this.setValue();
				e.preventDefault();
			});
		});
	}

	handle24HoursChange(checkbox) {
		const item = checkbox.closest('.gd-bh-item');
		const hoursSlot = item.querySelector('.gd-bh-hours:first-of-type');

		if (!hoursSlot) return;

		if (checkbox.checked) {
			const time12am = checkbox.closest('.gd-bh-items').dataset['12am']?.trim() || '12:00 AM';

			item.classList.add('gd-bh-item-24hours');

			const inputGroups = item.querySelectorAll('.input-group');
			inputGroups.forEach(group => {
				group.style.opacity = '0.67';
			});

			// Set time to 00:00 (24 hours)
			const openAlt = hoursSlot.querySelector('.gd-alt-open');
			const closeAlt = hoursSlot.querySelector('.gd-alt-close');
			const openField = hoursSlot.querySelector('[data-field-alt="open"]');
			const closeField = hoursSlot.querySelector('[data-field-alt="close"]');

			if (openAlt) openAlt.value = time12am;
			if (closeAlt) closeAlt.value = time12am;
			if (openField) {
				openField.value = '00:00';
				openField.dispatchEvent(new Event('change'));
			}
			if (closeField) closeField.value = '00:00';
		} else {
			const inputGroups = item.querySelectorAll('.input-group');
			inputGroups.forEach(group => {
				group.style.opacity = '1';
			});
		}
	}

	handle24Hours(item) {
		if (!item) return;

		let has24 = false;
		const hoursSlots = item.querySelectorAll('.gd-bh-hours');

		hoursSlots.forEach(slot => {
			const openField = slot.querySelector('[data-field-alt="open"]');
			const closeField = slot.querySelector('[data-field-alt="close"]');

			if (openField && closeField) {
				const openVal = openField.value.trim();
				const closeVal = closeField.value.trim();

				if (openVal === '00:00' && closeVal === '00:00') {
					has24 = true;
				}
			}
		});

		const checkbox24 = item.querySelector('.gd-bh-24hours input[type="checkbox"]');
		if (checkbox24) {
			checkbox24.checked = has24;
		}

		if (has24) {
			item.classList.add('gd-bh-item-24hours');
			const inputGroups = item.querySelectorAll('.input-group');
			inputGroups.forEach(group => {
				group.style.opacity = '0.67';
			});
		} else {
			item.classList.remove('gd-bh-item-24hours');
			const inputGroups = item.querySelectorAll('.input-group');
			inputGroups.forEach(group => {
				group.style.opacity = '1';
			});
		}
	}

	setValue() {
		const activeRadio = this.wrap.querySelector(`[name="${this.field}_f_active"]:checked`);

		let value = '';
		if (activeRadio && activeRadio.value === '1') {
			value = this.toSchema();
		}

		this.fieldElement.value = value;
		this.fieldElement.dispatchEvent(new Event('change'));
	}

	toSchema() {
		const items = this.wrap.querySelectorAll('.gd-bh-item');
		const daysArray = [];

		items.forEach(item => {
			const timeContainer = item.querySelector('.gd-bh-time');
			if (!timeContainer) return;

			const day = timeContainer.dataset.day;
			if (!day) return;

			const hoursArray = [];
			const hoursSlots = item.querySelectorAll('.gd-bh-hours');

			hoursSlots.forEach(slot => {
				const openField = slot.querySelector('[data-field-alt="open"]');
				const closeField = slot.querySelector('[data-field-alt="close"]');

				if (openField) {
					const openVal = openField.value.trim();
					let closeVal = closeField ? closeField.value.trim() : '';

					if (openVal) {
						if (!closeVal) {
							closeVal = '00:00';
						}
						hoursArray.push(`${openVal}-${closeVal}`);
					}
				}
			});

			if (hoursArray.length > 0) {
				daysArray.push(`${day} ${hoursArray.join(',')}`);
			}
		});

		let schema = '';
		if (daysArray.length > 0) {
			schema += JSON.stringify(daysArray);
			schema += ',';
		}

		// Add timezone info
		const timezoneSelect = this.wrap.querySelector('[data-field="timezone_string"]');
		let tzString = this.defaultTimezoneString;
		let tzOffset = this.defaultOffset;

		if (timezoneSelect) {
			tzString = timezoneSelect.value || tzString;

			const selectedOption = timezoneSelect.querySelector('option:checked');
			if (selectedOption) {
				tzOffset = selectedOption.dataset.offset || tzOffset;
			}
		}

		schema += `["UTC":"${tzOffset}","Timezone":"${tzString}"]`;

		return schema;
	}

	initTimePickers() {
		// Trigger AUI initialization if available
		if (typeof window.aui_init === 'function') {
			window.aui_init();
		}
	}

	async getTimezone(selectElement, prefix = '') {
		const form = selectElement.closest('form');
		if (!form) return;

		const latField = form.querySelector(`[name="${prefix}latitude"]`);
		const lngField = form.querySelector(`[name="${prefix}longitude"]`);

		if (!latField || !lngField) return;

		const lat = latField.value.trim();
		const lng = lngField.value.trim();

		if (!lat || !lng) return;

		try {
			const response = await fetch(window.geodir_params.gd_ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'geodir_timezone_data',
					security: window.geodir_params.basic_nonce,
					lat: lat,
					lon: lng,
					ts: Math.round(Date.now() / 1000).toString()
				})
			});

			const data = await response.json();

			if (data.success && data.data.timeZoneId) {
				selectElement.value = data.data.timeZoneId;
				selectElement.dispatchEvent(new Event('change'));
			} else if (data.data && data.data.error) {
				console.log(data.data.error);
			}
		} catch (error) {
			console.error('Timezone fetch error:', error);
		} finally {
			window.gdTzApi = false;
		}
	}
}

/**
 * Initialize business hours widgets on the page
 * @param {HTMLFormElement} form
 */
export function initBusinessHours(form) {
	// This would typically be called with parameters from PHP
	// For now, look for any business hours fields and initialize them
	const bhRows = form.querySelectorAll('.gd-bh-row');

	bhRows.forEach(row => {
		const hiddenField = row.querySelector('[name*="business_hours"]');
		if (hiddenField) {
			const fieldName = hiddenField.getAttribute('name');
			// Initialize with basic params (full params would come from PHP)
			new BusinessHoursManager({
				field: fieldName,
				offset: window.geodir_params?.gmt_offset || '0'
			});
		}
	});
}
