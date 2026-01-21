/**
 * Gplanning UX - Fonctionnalités JavaScript
 * Améliore l'expérience utilisateur avec des fonctionnalités interactives
 */

class GplanningUX {
    constructor() {
        this.init();
    }

    init() {
        this.initToastNotifications();
        this.initFormValidation();
        this.initPreviewModal();
        this.initCalendarColoring();
        this.initFiltering();
        this.initTooltips();
        this.initModals();
        this.initAutocomplete();
        this.initDateSync();
        this.initVisualReminders();
        this.initSoftConfirm();
        this.initDraftSave();
        this.initKeyboardNavigation();
        this.initLoadingStates();
    }

    // 1. Avertissements visuels en temps réel
    initToastNotifications() {
        // Créer un conteneur pour les toasts
        if (!document.getElementById('toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = 'position: fixed; top: 100px; right: 20px; z-index: 10000; max-width: 400px;';
            document.body.appendChild(toastContainer);
        }
    }

    showToast(message, type = 'info', duration = 5000) {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            background: ${type === 'warning' ? '#fff3cd' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
            color: ${type === 'warning' ? '#856404' : type === 'error' ? '#721c24' : '#0c5460'};
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            border-left: 4px solid ${type === 'warning' ? '#ffc107' : type === 'error' ? '#dc3545' : '#17a2b8'};
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideInRight 0.3s ease-out;
        `;
        
        const icon = type === 'warning' ? '⚠️' : type === 'error' ? '❌' : 'ℹ️';
        toast.innerHTML = `
            <span style="font-size: 1.5rem;">${icon}</span>
            <span style="flex: 1;">${message}</span>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; opacity: 0.6;">×</button>
        `;
        
        toastContainer.appendChild(toast);
        
        if (duration > 0) {
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'slideOutRight 0.3s ease-out';
                    setTimeout(() => toast.remove(), 300);
                }
            }, duration);
        }
    }

    // 2. Validation dynamique des formulaires
    initFormValidation() {
        document.querySelectorAll('form').forEach(form => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (!submitBtn) return;

            const requiredFields = form.querySelectorAll('[required]');
            const validateForm = () => {
                let isValid = true;
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#dc3545';
                    } else {
                        field.style.borderColor = '#28a745';
                    }
                });
                
                submitBtn.disabled = !isValid;
                submitBtn.style.opacity = isValid ? '1' : '0.6';
                submitBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
            };

            requiredFields.forEach(field => {
                field.addEventListener('input', validateForm);
                field.addEventListener('change', validateForm);
            });
            
            validateForm(); // Initial validation
        });
    }

    // 3. Prévisualisation du planning avant confirmation
    initPreviewModal() {
        // Ajouter le HTML du modal de prévisualisation
        if (!document.getElementById('preview-modal')) {
            const modal = document.createElement('div');
            modal.id = 'preview-modal';
            modal.style.cssText = `
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 10000;
                align-items: center;
                justify-content: center;
            `;
            modal.innerHTML = `
                <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
                    <h3 style="margin-bottom: 1rem; color: #303030;">Prévisualisation</h3>
                    <div id="preview-content"></div>
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <button id="preview-cancel" class="btn btn-secondary">Annuler</button>
                        <button id="preview-confirm" class="btn btn-primary">Confirmer</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            document.getElementById('preview-cancel').addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
    }

    showPreview(data) {
        return new Promise((resolve) => {
            const modal = document.getElementById('preview-modal');
            if (!modal) {
                this.initPreviewModal();
            }
            
            const content = document.getElementById('preview-content');
            
            content.innerHTML = `
                <div style="line-height: 1.8;">
                    <p><strong>Client:</strong> ${data.client || 'N/A'}</p>
                    <p><strong>Date:</strong> ${data.date || 'N/A'}</p>
                    ${data.contentIdea ? `<p><strong>Idée de contenu:</strong> ${data.contentIdea}</p>` : ''}
                    ${data.shooting ? `<p><strong>Tournage lié:</strong> ${data.shooting}</p>` : ''}
                </div>
            `;
            
            modal.style.display = 'flex';
            
            const confirmBtn = document.getElementById('preview-confirm');
            const cancelBtn = document.getElementById('preview-cancel');
            
            const cleanup = () => {
                confirmBtn.onclick = null;
                cancelBtn.onclick = null;
            };
            
            confirmBtn.onclick = () => {
                modal.style.display = 'none';
                cleanup();
                resolve(true);
            };
            
            cancelBtn.onclick = () => {
                modal.style.display = 'none';
                cleanup();
                resolve(false);
            };
        });
    }

    // 4. Coloration intelligente du calendrier
    initCalendarColoring() {
        // Cette fonction sera appelée depuis les vues de calendrier
        window.colorizeCalendarDay = (dayElement, status) => {
            const colors = {
                free: { bg: '#fff', border: '#ddd' },
                publication: { bg: '#d4edda', border: '#28a745' },
                notRecommended: { bg: '#fff3cd', border: '#ffc107' },
                conflict: { bg: '#f8d7da', border: '#dc3545' }
            };
            
            const color = colors[status] || colors.free;
            dayElement.style.backgroundColor = color.bg;
            dayElement.style.borderColor = color.border;
        };
    }

    // 5. Filtrage instantané
    initFiltering() {
        document.querySelectorAll('[data-filter]').forEach(filterInput => {
            filterInput.addEventListener('input', (e) => {
                const filterValue = e.target.value.toLowerCase();
                const filterTarget = filterInput.getAttribute('data-filter');
                const items = document.querySelectorAll(`[data-filter-item="${filterTarget}"]`);
                
                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filterValue) ? '' : 'none';
                });
            });
        });
    }

    // 6. Tooltips explicatifs
    initTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = this.getAttribute('data-tooltip');
                tooltip.style.cssText = `
                    position: absolute;
                    background: #303030;
                    color: white;
                    padding: 0.5rem 0.75rem;
                    border-radius: 4px;
                    font-size: 0.85rem;
                    z-index: 10001;
                    pointer-events: none;
                    white-space: nowrap;
                `;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
                
                this._tooltip = tooltip;
            });
            
            element.addEventListener('mouseleave', function() {
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });
    }

    // 7. Modales simples
    initModals() {
        // Les modales seront créées dynamiquement selon les besoins
        window.showModal = (title, content, onConfirm) => {
            const modal = document.createElement('div');
            modal.className = 'gplanning-modal';
            modal.style.cssText = `
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 10000;
                align-items: center;
                justify-content: center;
            `;
            
            modal.innerHTML = `
                <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
                    <h2 style="margin-bottom: 1.5rem; color: #303030;">${title}</h2>
                    <div>${content}</div>
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <button class="modal-close btn btn-secondary">Fermer</button>
                        ${onConfirm ? `<button class="modal-confirm btn btn-primary">Confirmer</button>` : ''}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.querySelector('.modal-close').addEventListener('click', () => {
                modal.remove();
            });
            
            if (onConfirm) {
                modal.querySelector('.modal-confirm').addEventListener('click', () => {
                    onConfirm();
                    modal.remove();
                });
            }
        };
    }

    // 8. Autocomplétion intelligente
    initAutocomplete() {
        document.querySelectorAll('[data-autocomplete]').forEach(input => {
            const source = input.getAttribute('data-autocomplete');
            const dropdown = document.createElement('div');
            dropdown.className = 'autocomplete-dropdown';
            dropdown.style.cssText = `
                position: absolute;
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                max-height: 200px;
                overflow-y: auto;
                z-index: 1000;
                display: none;
                width: 100%;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            
            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(dropdown);
            
            input.addEventListener('input', async (e) => {
                const value = e.target.value;
                if (value.length < 2) {
                    dropdown.style.display = 'none';
                    return;
                }
                
                // Appel AJAX pour récupérer les suggestions
                try {
                    const response = await fetch(`/api/autocomplete/${source}?q=${encodeURIComponent(value)}`);
                    const data = await response.json();
                    
                    dropdown.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('div');
                            option.style.cssText = 'padding: 0.5rem; cursor: pointer; border-bottom: 1px solid #f0f0f0;';
                            option.textContent = item.name || item.titre || item.nom_entreprise;
                            option.addEventListener('click', () => {
                                input.value = item.name || item.titre || item.nom_entreprise;
                                if (input.dataset.autocompleteValue) {
                                    const hiddenInput = document.querySelector(`[name="${input.dataset.autocompleteValue}"]`);
                                    if (hiddenInput) hiddenInput.value = item.id;
                                }
                                dropdown.style.display = 'none';
                            });
                            option.addEventListener('mouseenter', function() {
                                this.style.backgroundColor = '#f5f5f5';
                            });
                            option.addEventListener('mouseleave', function() {
                                this.style.backgroundColor = 'white';
                            });
                            dropdown.appendChild(option);
                        });
                        dropdown.style.display = 'block';
                    } else {
                        dropdown.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Autocomplete error:', error);
                }
            });
            
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        });
    }

    // 9. Synchronisation date tournage → publication
    initDateSync() {
        const shootingSelect = document.querySelector('[name="shooting_id"]');
        const dateInput = document.querySelector('[name="date"]');
        
        if (shootingSelect && dateInput) {
            shootingSelect.addEventListener('change', async (e) => {
                const shootingId = e.target.value;
                if (!shootingId) return;
                
                try {
                    const response = await fetch(`/api/shootings/${shootingId}`);
                    const shooting = await response.json();
                    if (shooting.date) {
                        // Suggérer des dates après le tournage
                        const shootingDate = new Date(shooting.date);
                        const minDate = new Date(shootingDate);
                        minDate.setDate(minDate.getDate() + 1);
                        dateInput.min = minDate.toISOString().split('T')[0];
                        
                        // Suggérer une date par défaut (7 jours après)
                        const suggestedDate = new Date(shootingDate);
                        suggestedDate.setDate(suggestedDate.getDate() + 7);
                        dateInput.value = suggestedDate.toISOString().split('T')[0];
                        
                        this.showToast('Date suggérée basée sur le tournage sélectionné', 'info');
                    }
                } catch (error) {
                    console.error('Date sync error:', error);
                }
            });
        }
    }

    // 10. Rappels visuels
    initVisualReminders() {
        // Ajouter des badges visuels pour les événements proches
        document.querySelectorAll('[data-upcoming-date]').forEach(element => {
            const date = new Date(element.getAttribute('data-upcoming-date'));
            const daysUntil = Math.ceil((date - new Date()) / (1000 * 60 * 60 * 24));
            
            if (daysUntil >= 0 && daysUntil <= 3) {
                const badge = document.createElement('span');
                badge.className = 'upcoming-badge';
                badge.textContent = daysUntil === 0 ? 'Aujourd\'hui' : `${daysUntil}j`;
                badge.style.cssText = `
                    background: #ffc107;
                    color: #856404;
                    padding: 0.25rem 0.5rem;
                    border-radius: 4px;
                    font-size: 0.75rem;
                    font-weight: 600;
                    margin-left: 0.5rem;
                `;
                element.appendChild(badge);
            }
        });
    }

    // 11. Confirmation douce (soft confirm)
    initSoftConfirm() {
        window.softConfirm = (message, onConfirm) => {
            const confirmDiv = document.createElement('div');
            confirmDiv.className = 'soft-confirm';
            confirmDiv.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                max-width: 400px;
                border-left: 4px solid #ffc107;
            `;
            
            confirmDiv.innerHTML = `
                <p style="margin-bottom: 1rem; color: #303030;">${message}</p>
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button class="soft-confirm-cancel btn btn-secondary" style="padding: 0.5rem 1rem;">Annuler</button>
                    <button class="soft-confirm-ok btn btn-primary" style="padding: 0.5rem 1rem;">Continuer</button>
                </div>
            `;
            
            document.body.appendChild(confirmDiv);
            
            confirmDiv.querySelector('.soft-confirm-cancel').addEventListener('click', () => {
                confirmDiv.remove();
            });
            
            confirmDiv.querySelector('.soft-confirm-ok').addEventListener('click', () => {
                onConfirm();
                confirmDiv.remove();
            });
            
            // Auto-remove after 10 seconds
            setTimeout(() => {
                if (confirmDiv.parentElement) {
                    confirmDiv.remove();
                }
            }, 10000);
        };
    }

    // 12. Sauvegarde temporaire (draft)
    initDraftSave() {
        document.querySelectorAll('form:not([data-no-draft])').forEach(form => {
            const formId = form.id || 'form-' + Math.random().toString(36).substr(2, 9);
            form.id = formId;
            
            // Sauvegarder automatiquement
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('change', () => {
                    const formData = new FormData(form);
                    const data = {};
                    formData.forEach((value, key) => {
                        // Pour les checkboxes multiples, stocker comme tableau
                        if (key.endsWith('[]')) {
                            if (!data[key]) data[key] = [];
                            data[key].push(value);
                        } else {
                            data[key] = value;
                        }
                    });
                    localStorage.setItem(`draft-${formId}`, JSON.stringify(data));
                });
            });
            
            // Restaurer le brouillon
            const draft = localStorage.getItem(`draft-${formId}`);
            if (draft) {
                try {
                    const data = JSON.parse(draft);
                    Object.keys(data).forEach(key => {
                        if (key.endsWith('[]')) {
                            // Gérer les tableaux (checkboxes multiples)
                            const values = Array.isArray(data[key]) ? data[key] : [data[key]];
                            values.forEach(val => {
                                const field = form.querySelector(`[name="${key}"][value="${val}"]`);
                                if (field && field.type === 'checkbox') {
                                    field.checked = true;
                                }
                            });
                        } else {
                            const field = form.querySelector(`[name="${key}"]`);
                            if (field) {
                                if (field.type === 'checkbox') {
                                    field.checked = field.value == data[key];
                                } else {
                                    field.value = data[key];
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Erreur lors de la restauration du brouillon:', e);
                    localStorage.removeItem(`draft-${formId}`);
                }
            }
            
            // Effacer le brouillon après soumission réussie
            form.addEventListener('submit', () => {
                setTimeout(() => {
                    localStorage.removeItem(`draft-${formId}`);
                }, 1000);
            });
        });
    }

    // 13. Navigation clavier
    initKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Entrée pour enregistrer (si dans un formulaire)
            if (e.key === 'Enter' && e.ctrlKey) {
                const form = e.target.closest('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.click();
                    }
                }
            }
            
            // Esc pour fermer les modales
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.gplanning-modal, #preview-modal');
                modals.forEach(modal => {
                    if (modal.style.display !== 'none') {
                        modal.style.display = 'none';
                    }
                });
            }
        });
    }

    // 14. Chargement progressif
    initLoadingStates() {
        window.showLoading = (element) => {
            const loader = document.createElement('div');
            loader.className = 'gplanning-loader';
            loader.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; padding: 2rem;">
                    <div style="border: 3px solid #f3f3f3; border-top: 3px solid #FF6A3A; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div>
                </div>
            `;
            
            if (element) {
                element.style.position = 'relative';
                element.appendChild(loader);
            } else {
                loader.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000;';
                document.body.appendChild(loader);
            }
            
            return loader;
        };
        
        window.hideLoading = (loader) => {
            if (loader && loader.parentElement) {
                loader.remove();
            }
        };
        
        // Ajouter l'animation CSS pour le spinner
        if (!document.getElementById('loader-styles')) {
            const style = document.createElement('style');
            style.id = 'loader-styles';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

// Initialiser quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    window.gplanningUX = new GplanningUX();
    
    // Exposer showPreview globalement pour compatibilité
    window.showPreview = function(data) {
        if (window.gplanningUX && typeof window.gplanningUX.showPreview === 'function') {
            return window.gplanningUX.showPreview(data);
        }
        // Fallback avec modal simple
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
            `;
            
            const content = `
                <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
                    <h3 style="margin-bottom: 1rem; color: #303030;">Prévisualisation</h3>
                    <div style="line-height: 1.8;">
                        <p><strong>Client:</strong> ${data.client || 'N/A'}</p>
                        <p><strong>Date:</strong> ${data.date || 'N/A'}</p>
                        ${data.contentIdea ? `<p><strong>Idée de contenu:</strong> ${data.contentIdea}</p>` : ''}
                        ${data.shooting ? `<p><strong>Tournage lié:</strong> ${data.shooting}</p>` : ''}
                    </div>
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <button class="preview-cancel btn btn-secondary">Annuler</button>
                        <button class="preview-confirm btn btn-primary">Confirmer</button>
                    </div>
                </div>
            `;
            modal.innerHTML = content;
            document.body.appendChild(modal);
            
            modal.querySelector('.preview-cancel').addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });
            
            modal.querySelector('.preview-confirm').addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });
        });
    };
});
