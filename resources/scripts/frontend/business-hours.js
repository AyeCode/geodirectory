/**
 * Business Hours Frontend Display
 * Calculates and displays real-time open/closed status
 * Bootstrap handles all dropdown toggle functionality
 */

class BusinessHoursDisplay {
	constructor() {
		this.refreshAll();
		this.refreshInterval = setInterval(() => this.refreshAll(), 60000);
		document.addEventListener('geodir_map_infowindow_open', () => setTimeout(() => this.refreshAll(), 50));
		document.addEventListener('geodir_content_updated', () => setTimeout(() => this.refreshAll(), 50));
	}

	refreshAll() {
		document.querySelectorAll('.gd-bh-show-field').forEach(field => this.refreshField(field));
	}

	refreshField(field) {
		const expandRange = field.querySelector('.gd-bh-expand-range');
		if (!expandRange) return;

		// Calculate current time with timezone offset
		const offsetSec = parseInt(expandRange.dataset.offsetsec || '0');
		const d = new Date();
		const localDate = new Date(d.getTime() + (d.getTimezoneOffset() * 60000) + (offsetSec * 1000));

		const time = this.pad(localDate.getHours(), 2) + this.pad(localDate.getMinutes(), 2);
		let day = localDate.getDay() || 7; // Convert Sunday (0) to 7

		field.dataset.t = time;
		expandRange.dataset.date = localDate.toISOString().slice(0, 19);

		const todayEl = field.querySelector(`[data-day="${day}"]`);
		const prevDay = day > 1 ? day - 1 : 7;
		const prevEl = field.querySelector(`[data-day="${prevDay}"]`);

		let hasPrevOpen = false;
		let times = [];
		let opens = [];

		// Reset all classes
		field.classList.remove('gd-bh-open', 'gd-bh-close');
		field.querySelectorAll('div').forEach(div => {
			div.classList.remove('gd-bh-open', 'gd-bh-close', 'gd-bh-days-open', 'gd-bh-days-close',
				'gd-bh-slot-open', 'gd-bh-slot-close', 'gd-bh-days-today', 'gd-bh-days-prevday', 'active');
		});

		// Check previous day for next-day close scenarios
		if (prevEl?.querySelector('.gd-bh-next-day')) {
			prevEl.classList.add('gd-bh-days-prevday');
			const prevDayName = prevEl.querySelector('.gd-bh-days-d')?.textContent || '';

			prevEl.querySelectorAll('.gd-bh-slot').forEach(slot => {
				const isOpen = slot.dataset.open && slot.dataset.close && parseInt(time) <= parseInt(slot.dataset.close);
				const slotRange = slot.querySelector('.gd-bh-slot-r')?.innerHTML || '';

				slot.classList.add(isOpen ? 'gd-bh-slot-open' : 'gd-bh-slot-close');
				if (isOpen) {
					hasPrevOpen = true;
					opens.push(prevDayName + ' ' + slotRange);
				}
				times.push(prevDayName + ' ' + slotRange);
			});

			prevEl.classList.add(hasPrevOpen ? 'gd-bh-days-open' : 'gd-bh-days-close');
			if (hasPrevOpen) times = opens;

			const todayRange = field.querySelector('.gd-bh-today-range');
			if (todayRange) todayRange.innerHTML = times.join(', ');
		}

		// Process today's hours
		let hasOpen = false;
		let hasClosed = false;

		if (todayEl) {
			todayEl.classList.add('gd-bh-days-today');
			const dayName = todayEl.querySelector('.gd-bh-days-d')?.textContent || '';
			const dayPrefix = hasPrevOpen ? (dayName ? dayName + ' ' : '') : '';

			if (!hasPrevOpen) {
				times = [];
				opens = [];
			}

			if (todayEl.dataset.closed !== '1') {
				todayEl.querySelectorAll('.gd-bh-slot').forEach(slot => {
					const open = parseInt(slot.dataset.open);
					const close = parseInt(slot.dataset.close);
					const isNextDay = slot.classList.contains('gd-bh-next-day');
					const slotRange = slot.querySelector('.gd-bh-slot-r')?.innerHTML || '';
					const currentTime = parseInt(time);
					const isOpen = open && close && open <= currentTime && (currentTime <= close || isNextDay);

					slot.classList.add(isOpen ? 'gd-bh-slot-open' : 'gd-bh-slot-close');
					if (isOpen) {
						hasOpen = true;
						opens.push(dayPrefix + slotRange);
					}
					if ((hasPrevOpen && hasOpen) || !hasPrevOpen) {
						times.push(dayPrefix + slotRange);
					}
				});
			} else {
				hasClosed = true;
			}

			todayEl.classList.add(hasOpen ? 'gd-bh-days-open' : 'gd-bh-days-close');
			if (hasOpen) times = opens;

			const todayRange = field.querySelector('.gd-bh-today-range');
			if (todayRange) todayRange.innerHTML = [...new Set(times)].join(', ');
		}

		// Update status label and colors
		const params = window.geodir_params || {};
		const isOpen = hasOpen || hasPrevOpen;
		const label = isOpen ? (params.txt_open_now || 'Open now')
			: (hasClosed ? (params.txt_closed_today || 'Closed today') : (params.txt_closed_now || 'Closed now'));

		field.classList.add(isOpen ? 'gd-bh-open' : 'gd-bh-close');

		const iconEl = field.querySelector('.geodir-i-biz-hours');
		const labelEl = field.querySelector('.geodir-i-biz-hours font');
		const colorClass = isOpen ? 'text-success' : 'text-danger';
		const removeClass = isOpen ? 'text-danger' : 'text-success';

		if (iconEl) {
			iconEl.classList.add(colorClass);
			iconEl.classList.remove(removeClass);
		}
		if (labelEl) labelEl.textContent = label;

		// Color slot times in dropdown
		field.querySelectorAll('.gd-bh-slot-open .gd-bh-slot-r').forEach(el => {
			el.classList.add('text-success');
			el.classList.remove('text-danger');
		});
		field.querySelectorAll('.gd-bh-slot-close .gd-bh-slot-r').forEach(el => {
			el.classList.add('text-danger');
			el.classList.remove('text-success');
		});

		// Add background color to today's row based on open/closed status
		if (todayEl?.classList.contains('gd-bh-days-list')) {
			const bgClass = isOpen ? 'bg-success-subtle' : 'bg-danger-subtle';
			const removeBgClass = isOpen ? 'bg-danger-subtle' : 'bg-success-subtle';
			todayEl.classList.add(bgClass);
			todayEl.classList.remove(removeBgClass);
		}

		// Add background to previous day if showing next-day close
		if (hasPrevOpen && prevEl?.classList.contains('gd-bh-days-list')) {
			prevEl.classList.add('bg-success-subtle');
			prevEl.classList.remove('bg-danger-subtle');
		}
	}

	pad(num, size) {
		return String(num).padStart(size, '0');
	}

	destroy() {
		clearInterval(this.refreshInterval);
	}
}

export function initBusinessHoursDisplay() {
	return document.querySelector('.gd-bh-show-field') ? new BusinessHoursDisplay() : null;
}
