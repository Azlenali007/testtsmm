/**
 * SMM Panel — Core Frontend Application Script (Vanilla JS + Fetch API)
 */

window.ApexApp = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

    /**
     * Standard Fetch API POST request with JSON / Form headers
     */
    async post(url, data = {}) {
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        };

        let body;
        if (data instanceof FormData) {
            data.append('_csrf_token', this.csrfToken);
            body = data;
        } else {
            headers['Content-Type'] = 'application/json';
            data._csrf_token = this.csrfToken;
            body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: headers,
                body: body,
            });

            const result = await response.json();
            return { ok: response.ok, status: response.status, data: result };
        } catch (error) {
            console.error('API Fetch Error:', error);
            return { ok: false, status: 0, data: { success: false, message: 'Network or server communication error.' } };
        }
    },

    /**
     * Copy text to clipboard with visual feedback
     */
    copyToClipboard(text, element) {
        navigator.clipboard.writeText(text).then(() => {
            const original = element.innerHTML;
            element.innerHTML = '<span>Copied!</span>';
            setTimeout(() => {
                element.innerHTML = original;
            }, 2000);
        }).catch(err => {
            console.error('Clipboard copy failed:', err);
        });
    },

    /**
     * Toast alert notifications
     */
    toast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const el = document.createElement('div');
        const bg = type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-rose-600' : 'bg-indigo-600');
        el.className = `${bg} text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium transition-all transform duration-300 opacity-0 translate-y-2 flex items-center space-x-2`;
        el.innerHTML = `<span>${message}</span>`;

        container.appendChild(el);
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', 'translate-y-2');
        });

        setTimeout(() => {
            el.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => el.remove(), 300);
        }, 4000);
    }
};

// Global Order Form Calculator Helper for Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.data('orderForm', (servicesData, initialServiceId = 0) => ({
        services: servicesData || [],
        selectedCategoryId: '',
        selectedServiceId: initialServiceId || '',
        quantity: 100,
        link: '',
        comments: '',
        isDripfeed: false,
        runs: 2,
        interval: 10,
        currentService: null,
        calculatedPrice: '0.0000',

        init() {
            if (this.selectedServiceId) {
                this.onServiceChange();
                if (this.currentService) {
                    this.selectedCategoryId = this.currentService.category_id;
                }
            } else if (this.services.length > 0) {
                this.selectedCategoryId = this.services[0].category_id;
                this.selectedServiceId = this.services[0].id;
                this.onServiceChange();
            }
        },

        get filteredServices() {
            if (!this.selectedCategoryId) return this.services;
            return this.services.filter(s => s.category_id == this.selectedCategoryId);
        },

        onCategoryChange() {
            const available = this.filteredServices;
            if (available.length > 0) {
                this.selectedServiceId = available[0].id;
                this.onServiceChange();
            } else {
                this.selectedServiceId = '';
                this.currentService = null;
                this.calculatedPrice = '0.0000';
            }
        },

        onServiceChange() {
            this.currentService = this.services.find(s => s.id == this.selectedServiceId) || null;
            if (this.currentService) {
                if (this.currentService.min_quantity && this.quantity < this.currentService.min_quantity) {
                    this.quantity = this.currentService.min_quantity;
                }
                this.recalculate();
            }
        },

        onCommentsChange() {
            if (this.currentService && (this.currentService.type === 'custom_comments' || this.currentService.type === 'package_comments')) {
                const lines = this.comments.split('\n').filter(l => l.trim().length > 0);
                this.quantity = lines.length;
            }
            this.recalculate();
        },

        recalculate() {
            if (!this.currentService) {
                this.calculatedPrice = '0.0000';
                return;
            }

            const unitRate = parseFloat(this.currentService.price) || 0;
            const runs = this.isDripfeed ? Math.max(1, parseInt(this.runs) || 1) : 1;
            const totalQty = parseInt(this.quantity) * runs;

            let price;
            if (this.currentService.type === 'package') {
                price = unitRate * runs;
            } else {
                price = (unitRate / 1000) * totalQty;
            }

            this.calculatedPrice = price.toFixed(4);
        }
    }));
});
