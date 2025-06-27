// Scripts personnalisés pour le portfolio - Design Moderne

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialisation des popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Validation des formulaires
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Prévisualisation d'image pour l'upload
    const imageInput = document.getElementById('project_image');
    const imagePreview = document.getElementById('image_preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Confirmation de suppression
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });

    // Filtrage des compétences amélioré
    const skillFilter = document.getElementById('skill_filter');
    const skillCards = document.querySelectorAll('.skill-card');
    
    if (skillFilter) {
        skillFilter.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            
            skillCards.forEach(card => {
                const skillName = card.querySelector('.skill-name').textContent.toLowerCase();
                const skillCategory = card.querySelector('.badge')?.textContent.toLowerCase() || '';
                
                if (skillName.includes(searchTerm) || skillCategory.includes(searchTerm)) {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                    visibleCount++;
                } else {
                    card.style.opacity = '0.3';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        if (card.style.opacity === '0.3') {
                            card.style.display = 'none';
                        }
                    }, 200);
                }
            });
            
            // Afficher un message si aucune compétence trouvée
            const noResultsMessage = document.getElementById('no-results-message');
            if (visibleCount === 0 && searchTerm.length > 0) {
                if (!noResultsMessage) {
                    const message = document.createElement('div');
                    message.id = 'no-results-message';
                    message.className = 'text-center py-4 text-muted';
                    message.innerHTML = `
                        <i class="fas fa-search fa-2x mb-3"></i>
                        <h5>Aucune compétence trouvée</h5>
                        <p>Essayez avec d'autres termes de recherche</p>
                    `;
                    skillCards[0]?.parentElement?.appendChild(message);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        });
        
        // Effet de focus sur le filtre
        skillFilter.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        skillFilter.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    }

    // Animation des cartes au scroll avec Intersection Observer
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });

    // Gestion des onglets
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-bs-target');
            const tab = new bootstrap.Tab(this);
            tab.show();
            
            // Sauvegarder l'onglet actif dans localStorage
            localStorage.setItem('activeTab', target);
        });
    });

    // Restaurer l'onglet actif
    const activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        const tabElement = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }

    // Validation en temps réel des mots de passe
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (passwordInput && confirmPasswordInput) {
        function validatePasswords() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }
        
        passwordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
    }

    // Gestion des messages d'alerte avec animation
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Animation d'entrée
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            alert.style.opacity = '1';
            alert.style.transform = 'translateY(0)';
        }, 100);
        
        // Auto-fermeture
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Effet de hover sur les cartes de compétences
    document.querySelectorAll('.skill-card .card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Animation des badges de niveau
    document.querySelectorAll('.badge').forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Fonction pour afficher un message de chargement moderne
    window.showLoading = function(message = 'Chargement...') {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'loading-overlay';
        loadingDiv.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p class="mt-3">${message}</p>
            </div>
        `;
        document.body.appendChild(loadingDiv);
    };

    // Fonction pour masquer le message de chargement
    window.hideLoading = function() {
        const loadingDiv = document.querySelector('.loading-overlay');
        if (loadingDiv) {
            loadingDiv.style.opacity = '0';
            setTimeout(() => {
                loadingDiv.remove();
            }, 300);
        }
    };

    // Fonction pour copier du texte dans le presse-papiers avec notification moderne
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(function() {
            const notification = document.createElement('div');
            notification.className = 'copy-notification';
            notification.innerHTML = `
                <i class="fas fa-check me-2"></i>
                Copié dans le presse-papiers !
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 2000);
        });
    };

    // Amélioration des boutons avec effet de ripple
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Styles CSS pour les éléments dynamiques modernes
const style = document.createElement('style');
style.textContent = `
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(4px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.3s ease;
    }
    
    .loading-content {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid #e2e8f0;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .copy-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #0f172a;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        transition: opacity 0.3s ease;
        font-weight: 500;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .skill-card .card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .badge {
        transition: transform 0.2s ease;
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;

document.head.appendChild(style); 