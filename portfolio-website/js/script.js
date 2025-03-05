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