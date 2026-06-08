import './bootstrap';
import 'flowbite';
import 'simple-datatables/dist/style.css';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';
import { DataTable } from 'simple-datatables';
import { selectSearchable } from './select-searchable';

// Make TomSelect available globally for Alpine components
window.TomSelect = TomSelect;

// Register Alpine plugins
Alpine.plugin(collapse);

Alpine.store('swal', {
	fire: (options) => Swal.fire(options),
	stopTimer: () => Swal.stopTimer(),
	resumeTimer: () => Swal.resumeTimer(),
});

Alpine.data('swalConfirm', (options) => ({
	swalStore: null,
	options,
	init() {
		this.swalStore = this.$store.swal;
	},
	submit(event) {
		event.preventDefault();
		if (!this.swalStore) return;

		this.swalStore
			.fire({
				title: this.options.title,
				text: this.options.text,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: this.options.confirmText,
				cancelButtonText: this.options.cancelText,
			})
			.then((result) => {
				if (result.isConfirmed) {
					this.$el.submit();
				}
			});
	},
}));

Alpine.store('datatables', {
	_registry: new Map(),
	register(id, instance) {
		this._registry.set(id, instance);
	},
	get(id) {
		return this._registry.get(id) || null;
	},
	onReady(id, callback) {
		const existing = this.get(id);
		if (existing) {
			callback(existing);
			return;
		}

		const handler = (event) => {
			if (event.detail?.id === id) {
				callback(event.detail.instance);
				window.removeEventListener('datatable:initialized', handler);
			}
		};

		window.addEventListener('datatable:initialized', handler);
	},
});

const dataTableRegistry = new Map();

const initDataTables = () => {
	document.querySelectorAll('[data-datatable]').forEach((table) => {
		if (table.dataset.datatableInitialized === 'true') {
			return;
		}

		let options = {};
		const rawOptions = table.dataset.datatableOptions;
		if (rawOptions) {
			try {
				options = JSON.parse(rawOptions);
			} catch (error) {
				options = {};
			}
		}

		const dt = new DataTable(table, options);
		table.dataset.datatableInitialized = 'true';
		table.dataset.datatableReady = 'true';
		table.__dataTableInstance = dt;

		// Store instance by table id
		if (table.id) {
			dataTableRegistry.set(table.id, dt);
			Alpine.store('datatables').register(table.id, dt);

			// Move the built-in search UI into a custom container when provided
			const searchTarget = document.querySelector(
				`[data-datatable-search-for="${table.id}"]`
			);
			if (searchTarget) {
				const searchEl = dt.wrapperDOM?.querySelector('.dataTable-search');
				if (searchEl) {
					searchTarget.appendChild(searchEl);
				}
			}

			window.dispatchEvent(
				new CustomEvent('datatable:initialized', {
					detail: { id: table.id, instance: dt },
				})
			);
		}
	});
};

const initSwalAlerts = () => {
	const swalStore = Alpine.store('swal');
	if (!swalStore) return;

	const baseStyles = {
		success: {
			bg: ['#ecfdf5', '#1e293b'],
			color: ['#065f46', '#ffffff'],
		},
		error: {
			bg: ['#fef2f2', '#1e293b'],
			color: ['#991b1b', '#ffffff'],
		},
		warning: {
			bg: ['#fffbeb', '#1e293b'],
			color: ['#92400e', '#ffffff'],
		},
		info: {
			bg: ['#eff6ff', '#1e293b'],
			color: ['#1e40af', '#ffffff'],
		},
	};

	document.querySelectorAll('[data-swal-alert]').forEach((el) => {
		let alerts = [];
		let errors = [];

		try {
			alerts = JSON.parse(el.dataset.swalAlerts || '[]');
		} catch (error) {
			alerts = [];
		}

		try {
			errors = JSON.parse(el.dataset.swalErrors || '[]');
		} catch (error) {
			errors = [];
		}

		alerts.forEach((alert) => {
			const style = baseStyles[alert.type] || baseStyles.info;
			swalStore.fire({
				icon: alert.type,
				title: alert.title,
				text: alert.text,
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: alert.timer || 4000,
				timerProgressBar: true,
				background: style.bg[0],
				color: style.color[0],
				customClass: {
					popup: 'colored-toast',
				},
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', swalStore.stopTimer);
					toast.addEventListener('mouseleave', swalStore.resumeTimer);
				},
			});
		});

		if (errors.length > 0) {
			const errorList = `<ul class="text-left text-sm list-disc pl-4">${errors
				.map((item) => `<li>${item}</li>`)
				.join('')}</ul>`;

			swalStore.fire({
				icon: 'error',
				title: 'Validasi Gagal!',
				html: errorList,
				confirmButtonText: 'Mengerti',
				confirmButtonColor: '#059669',
				background: '#ffffff',
				color: '#0f172a',
			});
		}
	});
};

// Custom filter function for DataTable
const filterDataTable = (tableId, columnIndex, value) => {
	const dt = dataTableRegistry.get(tableId);
	if (!dt) return;

	dt.search(value, [columnIndex]);
};

// Multi-column filter function
const multiFilterDataTable = (tableId, filters) => {
	const dt = dataTableRegistry.get(tableId);
	if (!dt) return;

	// Reset and apply all filters
	dt.search('');
	
	// Build combined search terms
	const searchTerms = Object.values(filters).filter(v => v !== '').join(' ');
	if (searchTerms) {
		dt.search(searchTerms);
	}
};

const initDatePickers = () => {
	document.querySelectorAll('input[type="date"], [data-datepicker]').forEach((input) => {
		if (input.dataset.datepickerInitialized === 'true') {
			return;
		}

		const baseClasses = input.dataset.altInputClass || input.className || 'form-input-custom';
		const enableTime = input.dataset.dateEnableTime === 'true';

		const options = {
			altInput: input.dataset.dateAltInput !== 'false',
			altFormat: input.dataset.dateAltFormat || (enableTime ? 'd F Y H:i' : 'd F Y'),
			altInputClass: `${baseClasses} flatpickr-alt-input`,
			allowInput: true,
			dateFormat: input.dataset.dateFormat || (enableTime ? 'Y-m-d H:i' : 'Y-m-d'),
			disableMobile: true,
			defaultDate: input.value || undefined,
			enableTime,
			time_24hr: true,
		};

		const minDate = input.dataset.dateMin || input.getAttribute('min');
		const maxDate = input.dataset.dateMax || input.getAttribute('max');
		if (minDate) options.minDate = minDate;
		if (maxDate) options.maxDate = maxDate;
		if (input.dataset.dateMode) options.mode = input.dataset.dateMode;

		const instance = flatpickr(input, options);
		input.dataset.datepickerInitialized = 'true';
		input._flatpickrInstance = instance;

		if (input.disabled && instance.altInput) {
			instance.altInput.disabled = true;
		}

		const observer = new MutationObserver((mutations) => {
			mutations.forEach((mutation) => {
				if (mutation.attributeName === 'disabled' && instance.altInput) {
					instance.altInput.disabled = input.disabled;
				}
			});
		});

		observer.observe(input, { attributes: true, attributeFilter: ['disabled'] });
		input._flatpickrObserver = observer;
	});
};

// Backward-compatible window bindings
window.dataTables = new Proxy(
	{},
	{
		get: (_target, prop) => {
			if (typeof prop !== 'string') return undefined;
			return dataTableRegistry.get(prop);
		},
		set: (_target, prop, value) => {
			if (typeof prop === 'string' && value) {
				dataTableRegistry.set(prop, value);
			}
			return true;
		},
	}
);
window.initDataTables = initDataTables;
window.filterDataTable = filterDataTable;
window.multiFilterDataTable = multiFilterDataTable;
window.initDatePickers = initDatePickers;

// Register selectSearchable with Alpine before start
try {
	Alpine.data && Alpine.data('selectSearchable', selectSearchable);
} catch (e) {
	// ignore if registration fails
}

// Start Alpine
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	initDataTables();
	initSwalAlerts();
	initDatePickers();
});

