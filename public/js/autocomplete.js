// Autocomplete component pre select elementy
class SelectAutocomplete {
    constructor(selectElement, options = {}) {
        this.select = selectElement;
        this.options = {
            placeholder: options.placeholder || 'Začnite písať...',
            noResultsText: options.noResultsText || 'Žiadne výsledky',
            minLength: options.minLength || 1,
            maxResults: options.maxResults || 10,
            ...options
        };
        
        this.isOpen = false;
        this.selectedIndex = -1;
        this.filteredOptions = [];
        
        this.init();
    }
    
    init() {
        // Skryť pôvodný select
        this.select.style.display = 'none';
        
        // Vytvoriť wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'autocomplete-wrapper';
        this.wrapper.style.position = 'relative';
        
        // Vytvoriť input
        this.input = document.createElement('input');
        this.input.type = 'text';
        this.input.className = this.select.className;
        this.input.placeholder = this.options.placeholder;
        this.input.autocomplete = 'off';
        
        // Vytvoriť dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'autocomplete-dropdown';
        this.dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        `;
        
        // Vložiť do DOM
        this.select.parentNode.insertBefore(this.wrapper, this.select);
        this.wrapper.appendChild(this.input);
        this.wrapper.appendChild(this.dropdown);
        
        // Získať možnosti
        this.allOptions = Array.from(this.select.options).map(option => ({
            value: option.value,
            text: option.textContent.trim(),
            selected: option.selected
        })).filter(option => option.value !== '');
        
        // Nastaviť počiatočnú hodnotu
        const selectedOption = this.allOptions.find(opt => opt.selected);
        if (selectedOption) {
            this.input.value = selectedOption.text;
        }
        
        this.bindEvents();
    }
    
    bindEvents() {
        // Input events
        this.input.addEventListener('input', (e) => this.handleInput(e));
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.input.addEventListener('focus', () => this.handleFocus());
        this.input.addEventListener('blur', (e) => this.handleBlur(e));
        
        // Dropdown events
        this.dropdown.addEventListener('mousedown', (e) => this.handleDropdownClick(e));
    }
    
    handleInput(e) {
        const query = e.target.value.toLowerCase();
        
        if (query.length >= this.options.minLength) {
            this.filteredOptions = this.allOptions
                .filter(option => option.text.toLowerCase().includes(query))
                .slice(0, this.options.maxResults);
            
            this.renderDropdown();
            this.showDropdown();
        } else {
            this.hideDropdown();
        }
        
        this.selectedIndex = -1;
        this.updateSelectValue('');
    }
    
    handleKeydown(e) {
        if (!this.isOpen) return;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.filteredOptions.length - 1);
                this.updateHighlight();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateHighlight();
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0) {
                    this.selectOption(this.filteredOptions[this.selectedIndex]);
                }
                break;
                
            case 'Escape':
                this.hideDropdown();
                break;
        }
    }
    
    handleFocus() {
        if (this.input.value.length >= this.options.minLength) {
            this.showDropdown();
        }
    }
    
    handleBlur(e) {
        // Delay hiding to allow clicking on dropdown
        setTimeout(() => {
            if (!this.dropdown.contains(document.activeElement)) {
                this.hideDropdown();
            }
        }, 150);
    }
    
    handleDropdownClick(e) {
        const item = e.target.closest('.autocomplete-item');
        if (item) {
            const index = parseInt(item.dataset.index);
            this.selectOption(this.filteredOptions[index]);
        }
    }
    
    renderDropdown() {
        if (this.filteredOptions.length === 0) {
            this.dropdown.innerHTML = `
                <div class="autocomplete-item autocomplete-no-results">
                    ${this.options.noResultsText}
                </div>
            `;
        } else {
            this.dropdown.innerHTML = this.filteredOptions
                .map((option, index) => `
                    <div class="autocomplete-item" data-index="${index}">
                        ${this.highlightMatch(option.text)}
                    </div>
                `)
                .join('');
        }
        
        this.addDropdownStyles();
    }
    
    addDropdownStyles() {
        const items = this.dropdown.querySelectorAll('.autocomplete-item');
        items.forEach(item => {
            item.style.cssText = `
                padding: 8px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f8f9fa;
            `;
            
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f8f9fa';
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = 'white';
            });
        });
        
        const noResults = this.dropdown.querySelector('.autocomplete-no-results');
        if (noResults) {
            noResults.style.cursor = 'default';
            noResults.style.color = '#6c757d';
        }
    }
    
    highlightMatch(text) {
        const query = this.input.value.toLowerCase();
        if (!query) return text;
        
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    updateHighlight() {
        const items = this.dropdown.querySelectorAll('.autocomplete-item:not(.autocomplete-no-results)');
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.style.backgroundColor = '#007bff';
                item.style.color = 'white';
            } else {
                item.style.backgroundColor = 'white';
                item.style.color = 'black';
            }
        });
    }
    
    selectOption(option) {
        this.input.value = option.text;
        this.updateSelectValue(option.value);
        this.hideDropdown();
        
        // Trigger change event
        const changeEvent = new Event('change', { bubbles: true });
        this.select.dispatchEvent(changeEvent);
    }
    
    updateSelectValue(value) {
        this.select.value = value;
    }
    
    showDropdown() {
        this.dropdown.style.display = 'block';
        this.isOpen = true;
    }
    
    hideDropdown() {
        this.dropdown.style.display = 'none';
        this.isOpen = false;
        this.selectedIndex = -1;
    }
}

// Helper function pre inicializáciu autocomplete
function initializeAutocomplete(selector, options = {}) {
    const selects = document.querySelectorAll(selector);
    selects.forEach(select => {
        new SelectAutocomplete(select, options);
    });
}

// Export pre použitie
window.SelectAutocomplete = SelectAutocomplete;
window.initializeAutocomplete = initializeAutocomplete;