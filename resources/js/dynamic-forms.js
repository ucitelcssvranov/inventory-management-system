/**
 * Dynamic Form Fields Component
 * Spravuje dynamické načítavanie polí vo formulároch na základe výberu používateľa
 */
class DynamicFormFields {
    constructor() {
        this.ajaxLoader = window.ajaxLoader;
        this.dependentFields = new Map();
        this.initialize();
    }

    /**
     * Inicializuje komponent
     */
    initialize() {
        this.setupCategoryLocationDependency();
        this.setupLocationAssetDependency();
        this.setupCommissionUserDependency();
    }

    /**
     * Nastaví závislosť medzi kategóriou a lokáciami
     */
    setupCategoryLocationDependency() {
        const categorySelects = document.querySelectorAll('[data-loads-locations]');
        
        categorySelects.forEach(categorySelect => {
            const locationSelectId = categorySelect.getAttribute('data-loads-locations');
            const locationSelect = document.querySelector(locationSelectId);
            
            if (!locationSelect) {
                console.warn(`Location select ${locationSelectId} not found`);
                return;
            }

            // Uložiť závislosť
            this.dependentFields.set(categorySelect, {
                dependent: locationSelect,
                type: 'locations',
                placeholder: 'Najprv vyberte kategóriu'
            });

            // Disable dependent field initially
            locationSelect.disabled = true;
            this.setPlaceholder(locationSelect, 'Najprv vyberte kategóriu');

            // Event listener pre category change
            categorySelect.addEventListener('change', (e) => {
                this.handleCategoryChange(e.target, locationSelect);
            });
        });
    }

    /**
     * Nastaví závislosť medzi lokáciou a assets
     */
    setupLocationAssetDependency() {
        const locationSelects = document.querySelectorAll('[data-loads-assets]');
        
        locationSelects.forEach(locationSelect => {
            const assetSelectId = locationSelect.getAttribute('data-loads-assets');
            const assetSelect = document.querySelector(assetSelectId);
            
            if (!assetSelect) {
                console.warn(`Asset select ${assetSelectId} not found`);
                return;
            }

            this.dependentFields.set(locationSelect, {
                dependent: assetSelect,
                type: 'assets',
                placeholder: 'Najprv vyberte lokáciu'
            });

            assetSelect.disabled = true;
            this.setPlaceholder(assetSelect, 'Najprv vyberte lokáciu');

            locationSelect.addEventListener('change', (e) => {
                this.handleLocationChange(e.target, assetSelect);
            });
        });
    }

    /**
     * Nastaví závislosť medzi komisiou a používateľmi
     */
    setupCommissionUserDependency() {
        const commissionSelects = document.querySelectorAll('[data-loads-users]');
        
        commissionSelects.forEach(commissionSelect => {
            const userSelectId = commissionSelect.getAttribute('data-loads-users');
            const userSelect = document.querySelector(userSelectId);
            
            if (!userSelect) {
                console.warn(`User select ${userSelectId} not found`);
                return;
            }

            this.dependentFields.set(commissionSelect, {
                dependent: userSelect,
                type: 'users',
                placeholder: 'Najprv vyberte komisiu'
            });

            userSelect.disabled = true;
            this.setPlaceholder(userSelect, 'Najprv vyberte komisiu');

            commissionSelect.addEventListener('change', (e) => {
                this.handleCommissionChange(e.target, userSelect);
            });
        });
    }

    /**
     * Spracuje zmenu kategórie
     */
    async handleCategoryChange(categorySelect, locationSelect) {
        const categoryId = categorySelect.value;
        
        // Reset location select
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
            
            // Znovu inicializuj autocomplete ak existuje
            if (typeof initializeAutocomplete === 'function' && locations.length > 10) {
                initializeAutocomplete(`#${locationSelect.id}`, {
                    placeholder: 'Začnite písať názov lokácie...',
                    noResultsText: 'Žiadne lokácie nenájdené'
                });
            }

            this.ajaxLoader.showSuccess(`Načítaných ${locations.length} lokácií`);

        } catch (error) {
            console.error('Error loading locations:', error);
            this.resetSelect(locationSelect, 'Chyba pri načítavaní');
            locationSelect.disabled = true;
        }
    }

    /**
     * Spracuje zmenu lokácie
     */
    async handleLocationChange(locationSelect, assetSelect) {
        const locationId = locationSelect.value;
        
        this.resetSelect(assetSelect, 'Načítavam majetok...');
        
        if (!locationId) {
            this.resetSelect(assetSelect, 'Najprv vyberte lokáciu');
            assetSelect.disabled = true;
            return;
        }

        try {
            assetSelect.disabled = true;
            const assets = await this.ajaxLoader.loadAssetsForLocation(locationId);
            
            if (assets.length === 0) {
                this.resetSelect(assetSelect, 'Žiadny majetok nenájdený');
                assetSelect.disabled = true;
                return;
            }

            // Pre assets používame aj inventory_number v texte
            const formattedAssets = assets.map(asset => ({
                id: asset.id,
                name: `${asset.name} (${asset.inventory_number || 'bez čísla'})`
            }));

            this.ajaxLoader.updateSelectOptions(
                assetSelect, 
                formattedAssets, 
                'id', 
                'name', 
                'Vyberte majetok'
            );
            
            assetSelect.disabled = false;
            
            if (typeof initializeAutocomplete === 'function' && assets.length > 10) {
                initializeAutocomplete(`#${assetSelect.id}`, {
                    placeholder: 'Začnite písať názov majetku...',
                    noResultsText: 'Žiadny majetok nenájdený'
                });
            }

            this.ajaxLoader.showSuccess(`Načítaných ${assets.length} položiek majetku`);

        } catch (error) {
            console.error('Error loading assets:', error);
            this.resetSelect(assetSelect, 'Chyba pri načítavaní');
            assetSelect.disabled = true;
        }
    }

    /**
     * Spracuje zmenu komisie
     */
    async handleCommissionChange(commissionSelect, userSelect) {
        const commissionId = commissionSelect.value;
        
        this.resetSelect(userSelect, 'Načítavam používateľov...');
        
        if (!commissionId) {
            this.resetSelect(userSelect, 'Najprv vyberte komisiu');
            userSelect.disabled = true;
            return;
        }

        try {
            userSelect.disabled = true;
            const users = await this.ajaxLoader.loadUsersForCommission(commissionId);
            
            if (users.length === 0) {
                this.resetSelect(userSelect, 'Žiadni používatelia nenájdení');
                userSelect.disabled = true;
                return;
            }

            this.ajaxLoader.updateSelectOptions(
                userSelect, 
                users, 
                'id', 
                'name', 
                'Vyberte používateľa'
            );
            
            userSelect.disabled = false;
            
            if (typeof initializeAutocomplete === 'function' && users.length > 10) {
                initializeAutocomplete(`#${userSelect.id}`, {
                    placeholder: 'Začnite písať meno používateľa...',
                    noResultsText: 'Žiadni používatelia nenájdení'
                });
            }

            this.ajaxLoader.showSuccess(`Načítaných ${users.length} používateľov`);

        } catch (error) {
            console.error('Error loading users:', error);
            this.resetSelect(userSelect, 'Chyba pri načítavaní');
            userSelect.disabled = true;
        }
    }

    /**
     * Resetuje select element
     */
    resetSelect(selectElement, placeholder) {
        selectElement.innerHTML = `<option value="">${placeholder}</option>`;
    }

    /**
     * Nastaví placeholder pre select
     */
    setPlaceholder(selectElement, text) {
        this.resetSelect(selectElement, text);
    }

    /**
     * Pridá dependency medzi dvoma select elementmi
     */
    addDependency(parentSelector, childSelector, type, options = {}) {
        const parentSelect = document.querySelector(parentSelector);
        const childSelect = document.querySelector(childSelector);
        
        if (!parentSelect || !childSelect) {
            console.error('Parent or child select not found');
            return;
        }

        this.dependentFields.set(parentSelect, {
            dependent: childSelect,
            type: type,
            placeholder: options.placeholder || 'Najprv vyberte nadradenú hodnotu',
            ...options
        });

        childSelect.disabled = true;
        this.setPlaceholder(childSelect, options.placeholder || 'Najprv vyberte nadradenú hodnotu');

        parentSelect.addEventListener('change', (e) => {
            this.handleGenericChange(e.target, childSelect, type, options);
        });
    }

    /**
     * Všeobecný handler pre dependency changes
     */
    async handleGenericChange(parentSelect, childSelect, type, options = {}) {
        const parentValue = parentSelect.value;
        
        this.resetSelect(childSelect, 'Načítavam...');
        
        if (!parentValue) {
            this.resetSelect(childSelect, options.placeholder || 'Najprv vyberte nadradenú hodnotu');
            childSelect.disabled = true;
            return;
        }

        try {
            childSelect.disabled = true;
            
            let data = [];
            switch (type) {
                case 'locations':
                    data = await this.ajaxLoader.loadLocationsForCategory(parentValue);
                    break;
                case 'assets':
                    data = await this.ajaxLoader.loadAssetsForLocation(parentValue);
                    break;
                case 'users':
                    data = await this.ajaxLoader.loadUsersForCommission(parentValue);
                    break;
                default:
                    console.error('Unknown dependency type:', type);
                    return;
            }
            
            if (data.length === 0) {
                this.resetSelect(childSelect, options.emptyText || 'Žiadne dáta nenájdené');
                childSelect.disabled = true;
                return;
            }

            this.ajaxLoader.updateSelectOptions(
                childSelect, 
                data, 
                options.valueKey || 'id', 
                options.textKey || 'name', 
                options.selectPlaceholder || 'Vyberte hodnotu'
            );
            
            childSelect.disabled = false;
            
            if (typeof initializeAutocomplete === 'function' && data.length > 10) {
                initializeAutocomplete(`#${childSelect.id}`, {
                    placeholder: options.searchPlaceholder || 'Začnite písať...',
                    noResultsText: 'Žiadne výsledky nenájdené'
                });
            }

            if (options.showSuccessMessage !== false) {
                this.ajaxLoader.showSuccess(`Načítaných ${data.length} položiek`);
            }

        } catch (error) {
            console.error(`Error loading ${type}:`, error);
            this.resetSelect(childSelect, 'Chyba pri načítavaní');
            childSelect.disabled = true;
        }
    }
}

// Inicializuj po načítaní DOM
document.addEventListener('DOMContentLoaded', function() {
    window.dynamicFormFields = new DynamicFormFields();
});