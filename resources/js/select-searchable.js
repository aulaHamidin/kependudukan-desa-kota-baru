import TomSelect from 'tom-select';

export const selectSearchable = (config) => ({
    tomSelect: null,
    config,
    abortController: null,
    initialValueAbortController: null,
    debounceTimer: null,

    init(selectEl) {
        let el = selectEl || (this.$refs && this.$refs.select) || null;
        if (!el) {
            console.error('[TomSelect] Select element not found');
            return;
        }

        // Cegah double init
        if (el.tomselect) {
            console.warn('[TomSelect] Already initialized');
            return;
        }

        const isRemote = !!this.config.remoteUrl;
        // Use Vite's environment variable - automatically false in production build
        const DEBUG = import.meta.env.DEV;

        if (DEBUG) {
            console.log('[TomSelect] Initializing:', {
                name: this.config.name,
                isRemote,
                remoteUrl: this.config.remoteUrl,
                initialValue: this.config.initialValue
            });
        }

        const baseOptions = {
            valueField: this.config.valueField || 'id',
            labelField: this.config.labelField || 'label',
            searchField: [this.config.labelField || 'label'],
            maxOptions: this.config.maxResults || 20,
            create: this.config.allowCreate || false,
            preload: this.config.preload || false,
            placeholder: this.config.placeholder || '',
            plugins: this.config.clearable ? ['clear_button'] : [],
            render: {
                option: (data, escape) => {
                    if (DEBUG) console.log('[TomSelect] Rendering option:', data);
                    // Guard against undefined label
                    const label = data.text || data[this.config.labelField || 'label'] || '(no label)';
                    if (data.subtitle) {
                        return `<div class="ts-option-custom">
                            <span class="ts-option-label">${escape(label)}</span>
                            <span class="ts-option-subtitle">${escape(data.subtitle)}</span>
                        </div>`;
                    }
                    return `<div>${escape(label)}</div>`;
                },
                item: (data, escape) => {
                    if (DEBUG) console.log('[TomSelect] Rendering item:', data);
                    // Guard against undefined label
                    const label = data.text || data[this.config.labelField || 'label'] || '(no label)';
                    return `<div>${escape(label)}</div>`;
                },
                no_results: () => `<div class="no-results">Tidak ada hasil ditemukan</div>`,
                loading: () => `<div class="ts-loading">Mencari...</div>`,
            },
        };

        if (isRemote) {
            // Bypass client-side filtering — let server handle search/scoring
            // score() must return a function(item) => number; return 1 so every
            // server-returned item is always shown (sifter won't hide them).
            baseOptions.score = function (/* search */) {
                return function (/* item */) { return 1; };
            };

            // Control when TomSelect should trigger load
            baseOptions.shouldLoad = (query) => {
                return query.length >= (this.config.minChars || 2);
            };

            baseOptions.load = (query, callback) => {
                const minChars = this.config.minChars || 2;
                if (query.length < minChars) {
                    if (DEBUG) console.log('[TomSelect] Query too short:', query.length, '<', minChars);
                    return callback();
                }

                // Debounce: tunda fetch hingga user berhenti mengetik
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {

                // Abort previous request
                if (this.abortController) {
                    this.abortController.abort();
                }
                this.abortController = new AbortController();

                const url = new URL(this.config.remoteUrl, window.location.origin);
                // Use configured search field name (default 'q') when building query
                const searchParam = this.config.searchField || 'q';
                url.searchParams.set(searchParam, query);
                url.searchParams.set('limit', String(this.config.maxResults || 20));

                if (DEBUG) console.log('[TomSelect] Fetching:', url.toString());

                fetch(url.toString(), {
                    signal: this.abortController.signal,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(json => {
                        if (DEBUG) console.log('[TomSelect] RAW JSON:', json);

                        // Guard: Ensure items is always an array
                        const items = Array.isArray(json.data) ? json.data : Array.isArray(json) ? json : [];

                        if (DEBUG) console.log('[TomSelect] ITEMS:', items);
                        if (DEBUG) console.log('[TomSelect] Calling callback with', items.length, 'items');

                        // Save current selection before clearing to avoid losing initial value
                        const currentValue = this.tomSelect.getValue();
                        const currentItem = currentValue ? this.tomSelect.options[currentValue] : null;

                        // Clear existing options to avoid cache conflicts
                        this.tomSelect.clearOptions();

                        // Re-add selected item if exists (preserve initial value)
                        if (currentItem) {
                            this.tomSelect.addOption(currentItem);
                        }

                        callback(items);

                        if (DEBUG) console.log('[TomSelect] Callback called successfully');
                    })
                    .catch((error) => {
                        if (error.name === 'AbortError') {
                            if (DEBUG) console.log('[TomSelect] Request aborted');
                            // Don't call callback on abort - let next request handle it
                            return;
                        }
                        console.error('[TomSelect] Error loading remote data:', error);
                        callback(); // Only call on real errors
                    });

                }, this.config.debounceMs ?? 300);
            };
        }

        // Init TomSelect first
        this.tomSelect = new TomSelect(el, baseOptions);
        if (DEBUG) console.log('[TomSelect] Initialized successfully');

        // Auto-cleanup on Alpine component destroy (Alpine v3+)
        if (this.$cleanup) {
            this.$cleanup(() => this.destroy());
        }

        // Load initial selected value AFTER initialization
        // Fix: Check for null/undefined explicitly, allow 0 and empty string
        if (isRemote && this.config.initialValue != null && this.config.initialValue !== '') {
            const initialValue = this.config.initialValue;
            const remoteUrl = this.config.remoteUrl;
            const valueField = baseOptions.valueField;
            const tomSelectInstance = this.tomSelect;

            // Disable during loading to prevent showing raw value
            tomSelectInstance.disable();
            tomSelectInstance.load(''); // Trigger loading state

            // Use separate AbortController for initial value
            this.initialValueAbortController = new AbortController();

            const url = new URL(remoteUrl, window.location.origin);
            // Use valueField as parameter name for initial-value fetch (default 'id')
            const idParam = baseOptions.valueField || this.config.valueField || 'id';
            url.searchParams.set(idParam, String(initialValue));

            if (DEBUG) console.log('[TomSelect] Loading initial value:', initialValue);

            fetch(url.toString(), {
                signal: this.initialValueAbortController.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(json => {
                    // Guard: Ensure items is always an array
                    const items = Array.isArray(json.data) ? json.data : Array.isArray(json) ? json : [];
                    if (DEBUG) console.log('[TomSelect] Initial value loaded:', items);
                    if (items.length > 0) {
                        tomSelectInstance.addOption(items[0]);
                        tomSelectInstance.setValue(items[0][valueField], true);
                        if (DEBUG) console.log('[TomSelect] Initial value set successfully');
                    }
                })
                .catch((error) => {
                    if (error.name === 'AbortError') {
                        if (DEBUG) console.log('[TomSelect] Initial value request aborted');
                    } else {
                        console.error('[TomSelect] Error loading initial value:', error);
                    }
                })
                .finally(() => {
                    // Re-enable after loading completes (success or error)
                    tomSelectInstance.enable();
                });
        }
    },

    destroy() {
        // Cancel any pending debounce
        clearTimeout(this.debounceTimer);

        // Abort any pending requests
        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
        }
        if (this.initialValueAbortController) {
            this.initialValueAbortController.abort();
            this.initialValueAbortController = null;
        }

        // Destroy TomSelect instance
        if (this.tomSelect) {
            this.tomSelect.destroy();
            this.tomSelect = null;
        }
    },

    clear() { this.tomSelect?.clear(); },
    setValue(value) { this.tomSelect?.setValue(value); },
    getValue() { return this.tomSelect?.getValue(); },
    disable() { this.tomSelect?.disable(); },
    enable() { this.tomSelect?.enable(); },
});
