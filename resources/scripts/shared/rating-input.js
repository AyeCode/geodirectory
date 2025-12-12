/**
 * Rating Input Handler
 *
 * Handles interactive star rating inputs for GeoDirectory reviews.
 * Used on both frontend comment forms and admin edit comment screens.
 *
 * @package GeoDirectory
 * @since 3.0.0
 */

/**
 * Initialize rating input functionality.
 *
 * This function finds all `.gd-rating-input` elements and adds interactive
 * hover and click behaviors for star rating selection.
 *
 * @returns {void}
 */
export function initRatingInput() {
	const ratingInputs = document.querySelectorAll('.gd-rating-input');

	if (!ratingInputs.length) {
		return;
	}

	ratingInputs.forEach((ratingInput) => {
		const ratingWrap = ratingInput.querySelector('.gd-rating-wrap');
		const foreground = ratingInput.querySelector('.gd-rating-foreground');
		const ratingText = ratingInput.querySelector('.gd-rating-text');
		const hiddenInput = ratingInput.querySelector('input[type="hidden"]');
		// Get stars from foreground for count and titles
		const stars = ratingInput.querySelectorAll('.gd-rating-foreground > i, .gd-rating-foreground > svg, .gd-rating-foreground > img');

		if (!ratingWrap || !foreground || !ratingText || !hiddenInput || !stars.length) {
			return;
		}

		const totalStars = stars.length;
		const currentValue = parseFloat(hiddenInput.value) || 0;

		// Make foreground not block mouse events
		foreground.style.pointerEvents = 'none';

		// Set initial state if value exists
		if (currentValue > 0) {
			const percent = (currentValue / totalStars) * 100;
			foreground.style.width = `${percent}%`;

			const starIndex = Math.floor(currentValue) - 1;
			if (stars[starIndex]) {
				ratingText.textContent = stars[starIndex].getAttribute('title') || '';
			}
		}

		// Track if rating has been set by click
		let ratingSet = false;

		// Mouse move on the rating wrap - calculate which star is hovered
		ratingWrap.addEventListener('mousemove', (e) => {
			const rect = ratingWrap.getBoundingClientRect();
			const x = e.clientX - rect.left;
			const starWidth = rect.width / totalStars;
			const hoveredIndex = Math.floor(x / starWidth);
			const rating = Math.min(Math.max(hoveredIndex + 1, 1), totalStars);
			const percent = (rating / totalStars) * 100;

			// Show preview
			foreground.style.width = `${percent}%`;

			if (stars[rating - 1]) {
				ratingText.textContent = stars[rating - 1].getAttribute('title') || '';
			}

			ratingSet = false;
		});

		// Mouse leave - restore to saved value
		ratingWrap.addEventListener('mouseleave', () => {
			if (!ratingSet) {
				const currentRating = parseFloat(hiddenInput.value) || 0;
				const originalPercent = (currentRating / totalStars) * 100;
				foreground.style.width = `${originalPercent}%`;

				if (currentRating > 0) {
					const savedStarIndex = Math.floor(currentRating) - 1;
					if (stars[savedStarIndex]) {
						ratingText.textContent = stars[savedStarIndex].getAttribute('title') || '';
					}
				} else {
					ratingText.textContent = ratingText.getAttribute('data-title') || '';
				}
			}

			ratingSet = false;
		});

		// Click - set rating permanently
		ratingWrap.addEventListener('click', (e) => {
			const rect = ratingWrap.getBoundingClientRect();
			const x = e.clientX - rect.left;
			const starWidth = rect.width / totalStars;
			const clickedIndex = Math.floor(x / starWidth);
			const rating = Math.min(Math.max(clickedIndex + 1, 1), totalStars);
			const percent = (rating / totalStars) * 100;

			hiddenInput.value = rating;
			foreground.style.width = `${percent}%`;

			if (stars[rating - 1]) {
				ratingText.textContent = stars[rating - 1].getAttribute('title') || '';
			}

			ratingSet = true;
		});
	});
}
