/**
 * AJAX Loader Component
 * Poskytuje funkcionalitu pre dynamické načítavanie dát cez AJAX
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

    /**
     * Vykoná AJAX požiadavku
     * @param {string} url URL endpoint
     * @param {Object} options Možnosti požiadavky
     * @returns {Promise} Promise s odpoveďou
     */
    async request(url, options = {}) {
        const config = { ...this.defaultOptions, ...options };
        
        // Zobraz loader ak je povolený
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

    /**
     * GET požiadavka
     */
    async get(url, params = {}, options = {}) {
        const urlObj = new URL(url, window.location.origin);
        Object.keys(params).forEach(key => urlObj.searchParams.append(key, params[key]));
        
        return this.request(urlObj.toString(), { ...options, method: 'GET' });
    }

    /**
     * POST požiadavka
     */
    async post(url, data = {}, options = {}) {
        return this.request(url, {
            ...options,
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT požiadavka
     */
    async put(url, data = {}, options = {}) {
        return this.request(url, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE požiadavka
     */
    async delete(url, options = {}) {
        return this.request(url, { ...options, method: 'DELETE' });
    }

    /**
     * Zobrazí loader
     */
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

    /**
     * Skryje loader
     */
    hideLoader() {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    /**
     * Načíta locations pre danú kategoriu
     */
    async loadLocationsForCategory(categoryId) {
        if (!categoryId) {
            return [];
        }

        try {
            const data = await this.get(`/ajax/locations/${categoryId}`);
            return data.locations || [];
        } catch (error) {
            console.error('Chyba pri načítavaní locations:', error);
            this.showError('Chyba pri načítavaní lokácií');
            return [];
        }
    }

    /**
     * Načíta assets pre danú lokáciu
     */
    async loadAssetsForLocation(locationId) {
        if (!locationId) {
            return [];
        }

        try {
            const data = await this.get(`/ajax/assets/${locationId}`);
            return data.assets || [];
        } catch (error) {
            console.error('Chyba pri načítavaní assets:', error);
            this.showError('Chyba pri načítavaní majetku');
            return [];
        }
    }

    /**
     * Načíta užívateľov pre danú komisiu
     */
    async loadUsersForCommission(commissionId) {
        if (!commissionId) {
            return [];
        }

        try {
            const data = await this.get(`/ajax/users/${commissionId}`);
            return data.users || [];
        } catch (error) {
            console.error('Chyba pri načítavaní užívateľov:', error);
            this.showError('Chyba pri načítavaní užívateľov');
            return [];
        }
    }

    /**
     * Zobrazí chybovú správu
     */
    showError(message) {
        // Použijeme Toast notifikáciu ak je dostupná, inak alert
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            this.showToast(message, 'error');
        } else {
            alert(message);
        }
    }

    /**
     * Zobrazí úspešnú správu
     */
    showSuccess(message) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            this.showToast(message, 'success');
        }
    }

    /**
     * Zobrazí toast notifikáciu
     */
    showToast(message, type = 'info') {
        const toastId = 'ajax-toast-' + Date.now();
        const bgClass = type === 'error' ? 'bg-danger' : 
                       type === 'success' ? 'bg-success' : 'bg-info';
        
        const toastHtml = `
            <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Systém</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
        }

        container.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Automaticky odstráň toast po zobrazení
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /**
     * Aktualizuje select element s novými options
     */
    updateSelectOptions(selectElement, options, valueKey = 'id', textKey = 'name', placeholder = null) {
        if (typeof selectElement === 'string') {
            selectElement = document.querySelector(selectElement);
        }

        if (!selectElement) {
            console.error('Select element not found');
            return;
        }

        // Vymaž existujúce options
        selectElement.innerHTML = '';

        // Pridaj placeholder ak je zadaný
        if (placeholder) {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = placeholder;
            placeholderOption.selected = true;
            selectElement.appendChild(placeholderOption);
        }

        // Pridaj nové options
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option[valueKey];
            optionElement.textContent = option[textKey];
            selectElement.appendChild(optionElement);
        });

        // Trigger change event pre prípad, že je na to navesený autocomplete
        selectElement.dispatchEvent(new Event('change'));
    }
}

// Globálna inštancia
window.ajaxLoader = new AjaxLoader();

// CSS štýly pre loader
const loaderStyles = `
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

    .ajax-loader-text {
        color: #495057;
        font-size: 0.9rem;
    }
`;

// Pridaj CSS štýly do stránky
if (!document.getElementById('ajax-loader-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'ajax-loader-styles';
    styleSheet.textContent = loaderStyles;
    document.head.appendChild(styleSheet);
}