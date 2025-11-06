// Modern Spectreacle App JavaScript

class SpectreacleApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupMobileNavigation();
        this.setupScrollEffects();
        this.setupLoadingIndicator();
        this.setupBackToTop();
        this.setupToast();
        this.setupModal();
        this.setupFormValidation();
        this.setupTokenCheck();
        this.setupAnimations();
    }

    // Mobile Navigation
    setupMobileNavigation() {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const navMenu = document.getElementById('nav-menu');
        
        if (mobileMenuBtn && navMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenuBtn.classList.toggle('active');
                navMenu.classList.toggle('active');
                document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
            });

            // Close menu when clicking on a link
            navMenu.addEventListener('click', (e) => {
                if (e.target.classList.contains('nav-link')) {
                    mobileMenuBtn.classList.remove('active');
                    navMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!mobileMenuBtn.contains(e.target) && !navMenu.contains(e.target)) {
                    mobileMenuBtn.classList.remove('active');
                    navMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
    }

    // Scroll Effects
    setupScrollEffects() {
        const navbar = document.getElementById('navbar');
        let lastScrollY = window.scrollY;

        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            
            // Add scrolled class for navbar styling
            if (currentScrollY > 100) {
                navbar?.classList.add('scrolled');
            } else {
                navbar?.classList.remove('scrolled');
            }

            lastScrollY = currentScrollY;
        });
    }

    // Loading Indicator
    setupLoadingIndicator() {
        const loadingIndicator = document.getElementById('loading-indicator');
        
        // Show loading on form submissions and navigation
        document.addEventListener('submit', () => {
            this.showLoading();
        });

        // Hide loading when page loads
        window.addEventListener('load', () => {
            this.hideLoading();
        });
    }

    showLoading() {
        const loadingIndicator = document.getElementById('loading-indicator');
        loadingIndicator?.classList.add('visible');
    }

    hideLoading() {
        const loadingIndicator = document.getElementById('loading-indicator');
        setTimeout(() => {
            loadingIndicator?.classList.remove('visible');
        }, 300);
    }

    // Back to Top Button
    setupBackToTop() {
        const backToTopBtn = document.getElementById('back-to-top');
        
        if (backToTopBtn) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    backToTopBtn.classList.add('visible');
                } else {
                    backToTopBtn.classList.remove('visible');
                }
            });

            backToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    // Toast Notifications
    setupToast() {
        this.toastContainer = document.getElementById('toast-container');
    }

    showToast(message, type = 'success', title = '', duration = 4000) {
        if (!this.toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">${icons[type] || icons.info}</div>
                <div class="toast-message">
                    ${title ? `<div class="toast-title">${title}</div>` : ''}
                    <div class="toast-text">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            </div>
        `;

        this.toastContainer.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('visible'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('visible');
            setTimeout(() => toast.remove(), 250);
        }, duration);

        return toast;
    }

    // Modal System
    setupModal() {
        const modalOverlay = document.getElementById('modal-overlay');
        const modalClose = document.getElementById('modal-close');
        
        if (modalClose) {
            modalClose.addEventListener('click', () => this.hideModal());
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    this.hideModal();
                }
            });
        }

        // ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideModal();
            }
        });
    }

    showModal(content) {
        const modalOverlay = document.getElementById('modal-overlay');
        const modalBody = document.getElementById('modal-body');
        
        if (modalOverlay && modalBody) {
            modalBody.innerHTML = content;
            modalOverlay.classList.add('visible');
            document.body.style.overflow = 'hidden';
        }
    }

    hideModal() {
        const modalOverlay = document.getElementById('modal-overlay');
        if (modalOverlay) {
            modalOverlay.classList.remove('visible');
            document.body.style.overflow = '';
        }
    }

    // Form Validation Enhancement
    setupFormValidation() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                // Real-time validation
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });

            // Form submission
            form.addEventListener('submit', async (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    return;
                }

                // Show loading for AJAX forms
                if (form.dataset.ajax) {
                    e.preventDefault();
                    await this.handleAjaxForm(form);
                }
            });
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        const required = field.required;
        let isValid = true;
        let message = '';

        // Remove existing error
        this.clearFieldError(field);

        // Required validation
        if (required && !value) {
            isValid = false;
            message = 'Ce champ est requis';
        }
        // Email validation
        else if (type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Adresse email invalide';
        }
        // Password validation
        else if (type === 'password' && value && value.length < 6) {
            isValid = false;
            message = 'Le mot de passe doit contenir au moins 6 caractères';
        }
        // Pattern validation
        else if (field.pattern && value && !new RegExp(field.pattern).test(value)) {
            isValid = false;
            message = field.title || 'Format invalide';
        }

        if (!isValid) {
            this.showFieldError(field, message);
        }

        return isValid;
    }

    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('error');
        
        let errorDiv = field.parentNode.querySelector('.field-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            field.parentNode.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // AJAX Form Handler
    async handleAjaxForm(form) {
        this.showLoading();
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                credentials: 'include'
            });

            const data = await response.json();

            if (response.ok) {
                this.showToast(data.message || 'Opération réussie', 'success');
                
                // Redirect if specified
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            } else {
                this.showToast(data.error || 'Une erreur est survenue', 'error');
            }
        } catch (error) {
            this.showToast('Erreur de connexion', 'error');
        } finally {
            this.hideLoading();
        }
    }

    // Token Expiration Check
    setupTokenCheck() {
        // Check if we're on a protected page
        const protectedPaths = ['/dashboard', '/profile', '/admin'];
        const isProtectedPage = protectedPaths.some(path => 
            window.location.pathname.startsWith(path)
        );

        if (isProtectedPage) {
            this.startTokenCheck();
        }
    }

    startTokenCheck() {
        setInterval(async () => {
            try {
                const response = await fetch('/profile', {
                    method: 'HEAD',
                    credentials: 'include'
                });
                
                if (response.status === 401) {
                    this.showToast('Votre session a expiré', 'warning', 'Session expirée');
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 2000);
                }
            } catch (error) {
                console.log('Token check failed:', error);
            }
        }, 60000); // Check every minute
    }

    // Animations and Effects
    setupAnimations() {
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observe elements with animation classes
        document.querySelectorAll('.show-card, .feature-card, .reservation-card').forEach(el => {
            observer.observe(el);
        });
    }
}

// Global Functions (for backward compatibility)
async function logout() {
    try {
        const response = await fetch('/auth/logout', {
            method: 'POST',
            credentials: 'include'
        });
        
        if (response.ok) {
            app.showToast('Déconnexion réussie', 'success');
            setTimeout(() => {
                window.location.href = '/';
            }, 1000);
        } else {
            app.showToast('Erreur lors de la déconnexion', 'error');
        }
    } catch (error) {
        app.showToast('Erreur de connexion', 'error');
    }
}

function showMessage(message, type = 'info') {
    if (window.app) {
        window.app.showToast(message, type);
    }
}

function showModal(type) {
    const contents = {
        about: `
            <h2>À propos de Spectreacle</h2>
            <p>Spectreacle est une plateforme moderne de réservation de spectacles en ligne. Notre mission est de rendre la culture accessible à tous en simplifiant la découverte et la réservation d'événements culturels.</p>
            <h3>Nos fonctionnalités</h3>
            <ul>
                <li>🎭 Catalogue complet de spectacles</li>
                <li>🎟️ Réservation en ligne sécurisée</li>
                <li>👤 Gestion de profil personnalisé</li>
                <li>🔐 Authentification à deux facteurs</li>
                <li>📱 Design responsive et moderne</li>
            </ul>
        `,
        contact: `
            <h2>Contact</h2>
            <p>Pour toute question ou assistance, n'hésitez pas à nous contacter :</p>
            <div style="margin: 20px 0;">
                <p><strong>📧 Email :</strong> contact@spectreacle.fr</p>
                <p><strong>📞 Téléphone :</strong> +33 1 23 45 67 89</p>
                <p><strong>📍 Adresse :</strong> 123 Rue de la Culture, 75001 Paris</p>
                <p><strong>🕒 Horaires :</strong> Lun-Ven 9h-18h</p>
            </div>
            <p>Notre équipe vous répondra dans les plus brefs délais.</p>
        `,
        privacy: `
            <h2>Politique de confidentialité</h2>
            <p>Chez Spectreacle, nous respectons votre vie privée et nous nous engageons à protéger vos données personnelles.</p>
            <h3>Collecte des données</h3>
            <p>Nous collectons uniquement les informations nécessaires au fonctionnement de notre service : nom, email, préférences de spectacles.</p>
            <h3>Utilisation des données</h3>
            <p>Vos données sont utilisées pour : la gestion de votre compte, l'envoi de confirmations de réservation, l'amélioration de nos services.</p>
            <h3>Protection des données</h3>
            <p>Nous utilisons des mesures de sécurité avancées, incluant le chiffrement et l'authentification à deux facteurs.</p>
        `,
        terms: `
            <h2>Conditions d'utilisation</h2>
            <h3>Acceptation des conditions</h3>
            <p>En utilisant Spectreacle, vous acceptez ces conditions d'utilisation.</p>
            <h3>Compte utilisateur</h3>
            <p>Vous êtes responsable de la confidentialité de vos identifiants de connexion.</p>
            <h3>Réservations</h3>
            <p>Les réservations sont confirmées après paiement. Les conditions d'annulation varient selon l'événement.</p>
            <h3>Utilisation appropriée</h3>
            <p>L'utilisation frauduleuse ou abusive de la plateforme est interdite.</p>
            <h3>Modifications</h3>
            <p>Nous nous réservons le droit de modifier ces conditions à tout moment.</p>
        `
    };

    if (window.app && contents[type]) {
        window.app.showModal(contents[type]);
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new SpectreacleApp();
    
    // Add CSS for field errors and animations
    const style = document.createElement('style');
    style.textContent = `
        .field-error {
            color: var(--accent-600);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .field-error::before {
            content: '⚠️';
            font-size: 0.75rem;
        }
        
        input.error, textarea.error, select.error {
            border-color: var(--accent-500);
            box-shadow: 0 0 0 3px var(--accent-100);
        }
        
        .show-card, .feature-card, .reservation-card {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        
        .show-card.animate-in, .feature-card.animate-in, .reservation-card.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(style);
});

// Service Worker Registration (for PWA)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registered:', registration);
            })
            .catch(error => {
                console.log('ServiceWorker registration failed:', error);
            });
    });
}