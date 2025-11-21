/**
 * Category management for add-listing form
 * Handles default category selection for checkboxes, radios, and selects
 */

/**
 * Initialize category management
 * @param {HTMLFormElement} form
 */
export function initCategoryManager(form) {
	// Handle checkbox categories with default category
	initCheckboxCategories(form);

	// Handle radio categories with default category
	initRadioCategories(form);

	// Listen for category changes
	setupCategoryChangeListeners(form);

	// Populate default category on load
	populateDefaultCategoryInput(form);
}

/**
 * Initialize checkbox category handling
 * @param {HTMLFormElement} form
 */
function initCheckboxCategories(form) {
	const checkboxes = form.querySelectorAll('.geodir_form_row input[data-ccheckbox]');

	checkboxes.forEach(checkbox => {
		checkbox.addEventListener('change', function(e) {
			handleCheckboxChange(this, form);
		});
	});

	// Handle "make default" buttons
	const makeDefaultButtons = form.querySelectorAll('.gd-make-default-term');
	makeDefaultButtons.forEach(button => {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			handleMakeDefaultClick(this, form);
		});
	});

	// Trigger initial change
	const firstCheckbox = form.querySelector('.geodir_form_row input[data-ccheckbox]:first-of-type');
	if (firstCheckbox) {
		firstCheckbox.dispatchEvent(new Event('change'));
	}
}

/**
 * Handle checkbox change event
 * @param {HTMLInputElement} checkbox
 * @param {HTMLFormElement} form
 */
function handleCheckboxChange(checkbox, form) {
	const parent = checkbox.closest('.geodir_form_row');
	const fieldName = checkbox.dataset.ccheckbox;
	const defaultCategoryField = form.querySelector(`input[name="${fieldName}"]`);

	if (!parent || !defaultCategoryField) return;

	// Reset visual states
	parent.classList.remove('gd-term-handle');
	parent.querySelectorAll('.gd-term-checked').forEach(el => el.classList.remove('gd-term-checked'));
	parent.querySelectorAll('.gd-default-term').forEach(el => el.classList.remove('gd-default-term'));

	let checkedCount = 0;
	let firstCheckedInput = null;
	const currentValue = defaultCategoryField.value || '';

	// Find all checked checkboxes
	const name = checkbox.getAttribute('name');
	const checkboxes = parent.querySelectorAll(`[name="${name}"]`);

	checkboxes.forEach(cb => {
		if (cb.checked) {
			checkedCount++;
			cb.parentElement.classList.add('gd-term-checked');

			if (checkedCount === 1) {
				firstCheckedInput = cb;
			}
		}
	});

	// If more than one checked, show "make default" options
	if (checkedCount > 1) {
		parent.classList.add('gd-term-handle');
	}

	// If current default value is still checked, keep it
	const currentDefaultCheckbox = parent.querySelector(`#gd-cat-${currentValue}`);
	if (currentDefaultCheckbox && currentDefaultCheckbox.checked) {
		firstCheckedInput = currentDefaultCheckbox;
	}

	// Set the default category
	if (firstCheckedInput) {
		const makeDefaultButton = firstCheckedInput.parentElement.querySelector('.gd-make-default-term');
		if (makeDefaultButton) {
			makeDefaultButton.click();
		}
	} else {
		defaultCategoryField.value = '';
		defaultCategoryField.dispatchEvent(new Event('change'));
	}
}

/**
 * Handle "make default" button click
 * @param {HTMLElement} button
 * @param {HTMLFormElement} form
 */
function handleMakeDefaultClick(button, form) {
	const row = button.closest('.geodir_form_row');
	const parent = button.parentElement;
	const checkbox = parent.querySelector('[type="checkbox"]');

	if (!checkbox) return;

	const fieldName = checkbox.dataset.ccheckbox;
	const defaultCategoryField = form.querySelector(`input[name="${fieldName}"]`);

	if (!defaultCategoryField) return;

	// Remove previous default
	row.querySelectorAll('.gd-default-term').forEach(el => el.classList.remove('gd-default-term'));

	// Set new default
	parent.classList.add('gd-default-term');

	// Update hidden field
	const value = checkbox.value;
	defaultCategoryField.value = value;
	defaultCategoryField.dispatchEvent(new Event('change'));
}

/**
 * Initialize radio category handling
 * @param {HTMLFormElement} form
 */
function initRadioCategories(form) {
	const radios = form.querySelectorAll('.geodir_form_row input[data-cradio]');

	radios.forEach(radio => {
		radio.addEventListener('change', function() {
			handleRadioChange(this, form);
		});
	});

	// Trigger initial change
	const firstRadio = form.querySelector('.geodir_form_row input[data-cradio]:first-of-type');
	if (firstRadio) {
		firstRadio.dispatchEvent(new Event('change'));
	}
}

/**
 * Handle radio change event
 * @param {HTMLInputElement} radio
 * @param {HTMLFormElement} form
 */
function handleRadioChange(radio, form) {
	const fieldName = radio.dataset.cradio;
	const defaultCategoryField = form.querySelector(`input[name="${fieldName}"]`);

	if (!defaultCategoryField) return;

	let value = '';
	const name = radio.getAttribute('name');
	const checkedRadio = form.querySelector(`[name="${name}"]:checked`);

	if (checkedRadio) {
		value = checkedRadio.value;
	}

	defaultCategoryField.value = value;
}

/**
 * Set up category change listeners
 * @param {HTMLFormElement} form
 */
function setupCategoryChangeListeners(form) {
	// Listen for changes on category selects, checkboxes, and radios
	const categoryFields = form.querySelectorAll(
		'.geodir_taxonomy_field .geodir-category-select, ' +
		'.geodir_taxonomy_field [data-ccheckbox="default_category"], ' +
		'.geodir_taxonomy_field input[data-cradio]'
	);

	categoryFields.forEach(field => {
		field.addEventListener('change', () => {
			populateDefaultCategoryInput(form);
			const defaultCategoryField = form.querySelector('[name="default_category"]');
			if (defaultCategoryField) {
				defaultCategoryField.dispatchEvent(new Event('change'));
			}
		});
	});
}

/**
 * Populate the default_category hidden input based on selected categories
 * @param {HTMLFormElement} form
 */
export function populateDefaultCategoryInput(form) {
	const defaultCategoryField = form.querySelector('#default_category');
	if (!defaultCategoryField) return;

	const currentDefaultValue = defaultCategoryField.value;

	// Handle multi-select dropdown
	const categorySelect = form.querySelector('.geodir_taxonomy_field .geodir-category-select');
	if (categorySelect) {
		// Clear options
		defaultCategoryField.innerHTML = '';

		const selectedValues = Array.from(categorySelect.selectedOptions).map(opt => opt.value);

		if (selectedValues.length > 0) {
			categorySelect.querySelectorAll('option').forEach(option => {
				if (selectedValues.includes(option.value)) {
					const newOption = document.createElement('option');
					newOption.value = option.value;
					newOption.textContent = option.textContent;
					newOption.selected = currentDefaultValue === option.value ||
					                      (!currentDefaultValue && selectedValues[0] === option.value);
					defaultCategoryField.appendChild(newOption);
				}
			});
		} else {
			defaultCategoryField.value = '';
		}
		return;
	}

	// Handle checkboxes
	const checkboxes = form.querySelectorAll('.geodir_taxonomy_field [data-ccheckbox="default_category"]');
	if (checkboxes.length > 0) {
		defaultCategoryField.innerHTML = '';

		const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked);

		if (selectedCheckboxes.length > 0) {
			selectedCheckboxes.forEach((checkbox, index) => {
				const newOption = document.createElement('option');
				newOption.value = checkbox.value;
				newOption.textContent = checkbox.title || checkbox.value;
				newOption.selected = currentDefaultValue === checkbox.value ||
				                      (!currentDefaultValue && index === 0);
				defaultCategoryField.appendChild(newOption);
			});
		} else {
			defaultCategoryField.value = '';
		}
		return;
	}

	// Handle radio buttons
	const radios = form.querySelectorAll('.geodir_taxonomy_field [data-cradio="default_category"]');
	if (radios.length > 0) {
		const checkedRadio = form.querySelector('.geodir_taxonomy_field [data-cradio="default_category"]:checked');
		if (checkedRadio) {
			defaultCategoryField.value = checkedRadio.value;
		} else {
			defaultCategoryField.value = '';
		}
	}
}
