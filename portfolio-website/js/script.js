document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        // Close menu when clicking a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });
    }
});

// project modal open
document.addEventListener('DOMContentLoaded', () => {
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    const modals = document.querySelectorAll('.modal');
    let currentItem = null;
    let currentModal = null;

    portfolioItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentItem = this;
            const modalId = this.getAttribute('data-modal');
            currentModal = document.getElementById(modalId);
            
            if (!currentModal) return;
            
            const modalContent = currentModal.querySelector('.modal-content');
            const closeBtn = currentModal.querySelector('.close');
            
            // Create backdrop if it doesn't exist
            let backdrop = currentModal.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop';
                currentModal.insertBefore(backdrop, currentModal.firstChild);
            }
            
            const rect = this.getBoundingClientRect();

            // Set initial position
            modalContent.style.position = 'fixed';
            modalContent.style.top = `${rect.top}px`;
            modalContent.style.left = `${rect.left}px`;
            modalContent.style.width = `${rect.width}px`;
            modalContent.style.height = `${rect.height}px`;
            currentModal.style.display = 'block';

            // Start expansion animation
            requestAnimationFrame(() => {
                backdrop.classList.add('active');
                currentModal.classList.add('active');
                modalContent.classList.add('expanded');
                closeBtn.classList.add('active');
                
                // Clear explicit dimensions after adding expanded class
                modalContent.style.width = '';
                modalContent.style.height = '';
            });
        });
    });

    function closeModal() {
        if (!currentItem || !currentModal) return;
        
        const modalContent = currentModal.querySelector('.modal-content');
        const closeBtn = currentModal.querySelector('.close');
        const backdrop = currentModal.querySelector('.modal-backdrop');
        
        if (!modalContent || !closeBtn || !backdrop) return;
        
        const rect = currentItem.getBoundingClientRect();

        // Set explicit dimensions for closing animation
        modalContent.style.width = `${rect.width}px`;
        modalContent.style.height = `${rect.height}px`;
        modalContent.style.top = `${rect.top}px`;
        modalContent.style.left = `${rect.left}px`;

        // Remove active classes
        backdrop.classList.remove('active');
        currentModal.classList.remove('active');
        modalContent.classList.remove('expanded');
        closeBtn.classList.remove('active');

        setTimeout(() => {
            currentModal.style.display = 'none';
            currentItem = null;
            currentModal = null;
            // Clear inline styles
            modalContent.style.width = '';
            modalContent.style.height = '';
        }, 500);
    }

    // Add close event listeners to all modals
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.close');
        const backdrop = modal.querySelector('.modal-backdrop');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        
        // Add click listener to the modal itself (outside of content)
        modal.addEventListener('click', function(e) {
            // Check if the click is on the modal but not on the modal content
            if (e.target === modal) {
                closeModal();
            }
        });
    });

    // Add escape key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentModal) {
            closeModal();
        }
    });
});

document.querySelectorAll('.copy-btn').forEach(button => {
    button.addEventListener('click', () => {
        const codeElement = button.parentElement.querySelector('code');
        const textToCopy = codeElement.textContent;
        
        navigator.clipboard.writeText(textToCopy).then(() => {
            // Show feedback
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.style.backgroundColor = 'rgba(49, 49, 49, 0.76)';
            
            // Reset after animation
            setTimeout(() => {
                button.innerHTML = originalIcon;
                button.style.backgroundColor = '';
            }, 1500);
        });
    });
});

// Image full screen
document.addEventListener('DOMContentLoaded', function() {
    // Créer un conteneur pour l'image en plein écran
    const fullscreenContainer = document.createElement('div');
    fullscreenContainer.className = 'fullscreen-image-container';
    fullscreenContainer.style.display = 'none';
    document.body.appendChild(fullscreenContainer);

    // Ajouter un gestionnaire d'événements à toutes les images demo-image
    document.querySelectorAll('.demo-image').forEach(img => {
        img.addEventListener('click', function(e) {
            e.stopPropagation(); // Empêcher la propagation pour ne pas fermer la modal
            
            // Créer l'élément img pour l'affichage en plein écran
            const fullscreenImg = document.createElement('img');
            fullscreenImg.src = this.src;
            fullscreenImg.alt = this.alt;
            fullscreenImg.className = 'fullscreen-image';
            
            // Vider et remplir le conteneur
            fullscreenContainer.innerHTML = '';
            fullscreenContainer.appendChild(fullscreenImg);
            
            // Afficher le conteneur
            fullscreenContainer.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Empêcher le défilement de la page
        });
    });

    // Fermer l'image en plein écran en cliquant en dehors ou avec Escape
    fullscreenContainer.addEventListener('click', function(e) {
        // Vérifier si le clic est sur le conteneur mais pas sur l'image
        if (e.target === fullscreenContainer) {
            closeFullscreenImage();
        }
    });

    // Gestionnaire d'événement pour la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && fullscreenContainer.style.display === 'flex') {
            closeFullscreenImage();
        }
    });

    // Fonction pour fermer l'image en plein écran
    function closeFullscreenImage() {
        fullscreenContainer.style.display = 'none';
        document.body.style.overflow = ''; // Restaurer le défilement de la page
    }
});