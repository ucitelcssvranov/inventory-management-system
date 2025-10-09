// Combined JavaScript file for inventory application
// Includes: autocomplete, AJAX loader, and dynamic forms functionality

/**
 * SelectAutocomplete Component - Autocomplete functionality for select boxes
 */
class SelectAutocomplete {
    constructor(selectElement, options = {}) {
        this.selectElement = typeof selectElement === 'string' 
            ? document.querySelector(selectElement) 
            : selectElement;
        
        if (!this.selectElement) {
            console.error('Select element not found');
            return;
        }

        this.options = {
            placeholder: 'Začnite písať...',
            noResultsText: 'Žiadne výsledky',
            minLength: 1,
            maxResults: 50,
            ...options
        };

        this.isOpen = false;
        this.selectedIndex = -1;
        this.init();
    }

    init() {
        this.createWrapper();
        this.createInput();
        this.createDropdown();
        this.bindEvents();
        this.hideOriginalSelect();
    }

    createWrapper() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'autocomplete-wrapper position-relative';
        this.selectElement.parentNode.insertBefore(this.wrapper, this.selectElement);
        this.wrapper.appendChild(this.selectElement);
    }

    createInput() {
        this.input = document.createElement('input');
        this.input.type = 'text';
        this.input.className = this.selectElement.className + ' autocomplete-input';
        this.input.placeholder = this.options.placeholder;
        this.input.autocomplete = 'off';
        
        this.wrapper.appendChild(this.input);
    }

    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'autocomplete-dropdown position-absolute w-100 bg-white border border-secondary rounded shadow-sm';
        this.dropdown.style.zIndex = '1050';
        this.dropdown.style.display = 'none';
        this.dropdown.style.maxHeight = '200px';
        this.dropdown.style.overflowY = 'auto';
        
        this.wrapper.appendChild(this.dropdown);
    }

    hideOriginalSelect() {
        this.selectElement.style.display = 'none';
    }

    bindEvents() {
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('focus', () => this.handleFocus());
        this.input.addEventListener('blur', (e) => this.handleBlur(e));
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }

    handleInput(e) {
        const value = e.target.value.toLowerCase();
        
        if (value.length < this.options.minLength) {
            this.closeDropdown();
            return;
        }

        this.filterOptions(value);
    }

    handleFocus() {
        if (this.input.value.length >= this.options.minLength) {
            this.filterOptions(this.input.value.toLowerCase());
        }
    }

    handleBlur(e) {
        // Delay to allow click on dropdown items
        setTimeout(() => {
            if (!this.wrapper.contains(document.activeElement)) {
                this.closeDropdown();
            }
        }, 150);
    }

    handleKeydown(e) {
        if (!this.isOpen) return;

        const items = this.dropdown.querySelectorAll('.autocomplete-item');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                this.updateSelection(items);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection(items);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                    this.selectOption(items[this.selectedIndex]);
                }
                break;
                
            case 'Escape':
                this.closeDropdown();
                break;
        }
    }

    filterOptions(searchTerm) {
        const options = Array.from(this.selectElement.options);
        const filtered = options.filter(option => {
            return option.value && option.textContent.toLowerCase().includes(searchTerm);
        }).slice(0, this.options.maxResults);

        this.renderDropdown(filtered);
    }

    renderDropdown(options) {
        this.dropdown.innerHTML = '';
        
        if (options.length === 0) {
            const noResults = document.createElement('div');
            noResults.className = 'autocomplete-item p-2 text-muted';
            noResults.textContent = this.options.noResultsText;
            this.dropdown.appendChild(noResults);
        } else {
            options.forEach((option, index) => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item p-2 cursor-pointer';
                item.textContent = option.textContent;
                item.dataset.value = option.value;
                
                item.addEventListener('mouseenter', () => {
                    this.selectedIndex = index;
                    this.updateSelection();
                });
                
                item.addEventListener('click', () => {
                    this.selectOption(item);
                });
                
                this.dropdown.appendChild(item);
            });
        }

        this.selectedIndex = -1;
        this.openDropdown();
    }

    updateSelection() {
        const items = this.dropdown.querySelectorAll('.autocomplete-item[data-value]');
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('bg-primary', 'text-white');
            } else {
                item.classList.remove('bg-primary', 'text-white');
            }
        });
    }

    selectOption(item) {
        const value = item.dataset.value;
        const text = item.textContent;
        
        this.selectElement.value = value;
        this.input.value = text;
        
        // Trigger change event
        this.selectElement.dispatchEvent(new Event('change', { bubbles: true }));
        
        this.closeDropdown();
    }

    openDropdown() {
        this.dropdown.style.display = 'block';
        this.isOpen = true;
    }

    closeDropdown() {
        this.dropdown.style.display = 'none';
        this.isOpen = false;
        this.selectedIndex = -1;
    }
}

/**
 * AJAX Loader Component
 */
class AjaxLoader {
    constructor() {
        this.defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            timeout: 10000,
            showLoader: true,
            loaderText: 'Načítavam...'
        };
    }

    async request(url, options = {}) {
        const config = { ...this.defaultOptions, ...options };
        
        if (config.showLoader) {
            this.showLoader(config.loaderText);
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), config.timeout);

            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;

        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Požiadavka bola prerušená kvôli timeout.');
            }
            throw error;
        } finally {
            if (config.showLoader) {
                this.hideLoader();
            }
        }
    }

    async get(url, params = {}, options = {}) {
        const urlObj = new URL(url, window.location.origin);
        Object.keys(params).forEach(key => urlObj.searchParams.append(key, params[key]));
        
        return this.request(urlObj.toString(), { ...options, method: 'GET' });
    }

    async loadLocationsForCategory(categoryId) {
        if (!categoryId) return [];

        try {
            const data = await this.get(`/ajax/locations/${categoryId}`);
            return data.locations || [];
        } catch (error) {
            console.error('Chyba pri načítavaní locations:', error);
            this.showError('Chyba pri načítavaní lokácií');
            return [];
        }
    }

    showLoader(text = 'Načítavam...') {
        let loader = document.getElementById('ajax-loader');
        
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'ajax-loader';
            loader.className = 'ajax-loader';
            loader.innerHTML = `
                <div class="ajax-loader-backdrop">
                    <div class="ajax-loader-content">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="ajax-loader-text mt-2">${text}</div>
                    </div>
                </div>
            `;
            document.body.appendChild(loader);
        }

        loader.querySelector('.ajax-loader-text').textContent = text;
        loader.style.display = 'block';
    }

    hideLoader() {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    showError(message) {
        alert(message); // Simple fallback
    }

    updateSelectOptions(selectElement, options, valueKey = 'id', textKey = 'name', placeholder = null) {
        if (typeof selectElement === 'string') {
            selectElement = document.querySelector(selectElement);
        }

        if (!selectElement) {
            console.error('Select element not found');
            return;
        }

        selectElement.innerHTML = '';

        if (placeholder) {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            placeholderOption.selected = true;
            selectElement.appendChild(placeholderOption);
        }

        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option[valueKey];
            optionElement.textContent = option[textKey];
            selectElement.appendChild(optionElement);
        });

        selectElement.dispatchEvent(new Event('change'));
    }
}

/**
 * Dynamic Form Fields Component
 */
class DynamicFormFields {
    constructor() {
        this.ajaxLoader = window.ajaxLoader;
        this.initialize();
    }

    initialize() {
        this.setupCategoryLocationDependency();
    }

    setupCategoryLocationDependency() {
        const categorySelects = document.querySelectorAll('[data-loads-locations]');
        
        categorySelects.forEach(categorySelect => {
            const locationSelectId = categorySelect.getAttribute('data-loads-locations');
            const locationSelect = document.querySelector(locationSelectId);
            
            if (!locationSelect) return;

            locationSelect.disabled = true;
            this.setPlaceholder(locationSelect, 'Najprv vyberte kategóriu');

            categorySelect.addEventListener('change', (e) => {
                this.handleCategoryChange(e.target, locationSelect);
            });
        });
    }

    async handleCategoryChange(categorySelect, locationSelect) {
        const categoryId = categorySelect.value;
        
        this.resetSelect(locationSelect, 'Načítavam lokácie...');
        
        if (!categoryId) {
            this.resetSelect(locationSelect, 'Najprv vyberte kategóriu');
            locationSelect.disabled = true;
            return;
        }

        try {
            locationSelect.disabled = true;
            const locations = await this.ajaxLoader.loadLocationsForCategory(categoryId);
            
            if (locations.length === 0) {
                this.resetSelect(locationSelect, 'Žiadne lokácie nenájdené');
                locationSelect.disabled = true;
                return;
            }

            this.ajaxLoader.updateSelectOptions(
                locationSelect, 
                locations, 
                'id', 
                'name', 
                'Vyberte lokáciu'
            );
            
            locationSelect.disabled = false;

        } catch (error) {
            console.error('Error loading locations:', error);
            this.resetSelect(locationSelect, 'Chyba pri načítavaní');
            locationSelect.disabled = true;
        }
    }

    resetSelect(selectElement, placeholder) {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
    }

    setPlaceholder(selectElement, text) {
        this.resetSelect(selectElement, text);
    }
}

// CSS štýly
const styles = `
    .ajax-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
    }

    .ajax-loader-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .ajax-loader-content {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        text-align: center;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .autocomplete-item:hover {
        background-color: #f8f9fa !important;
    }

    .autocomplete-input {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        padding-right: 2.5rem;
    }
`;

// Pridaj CSS štýly
if (!document.getElementById('inventory-app-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'inventory-app-styles';
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
}

// Globálne funkcie
function initializeAutocomplete(selector, options = {}) {
    const element = document.querySelector(selector);
    if (element && element.options && element.options.length > 10) {
        return new SelectAutocomplete(element, options);
    }
    return null;
}

// Inicializácia po načítaní DOM
document.addEventListener('DOMContentLoaded', function() {
    window.ajaxLoader = new AjaxLoader();
    window.dynamicFormFields = new DynamicFormFields();
});