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
    const modal = document.getElementById('project1-modal');
    const modalContent = modal.querySelector('.modal-content');
    const closeBtn = modal.querySelector('.close');
    let currentItem = null;

    // Add backdrop div to modal
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    modal.insertBefore(backdrop, modal.firstChild);

    portfolioItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentItem = this;
            const rect = this.getBoundingClientRect();

            // Set initial position
            modalContent.style.position = 'fixed';
            modalContent.style.top = `${rect.top}px`;
            modalContent.style.left = `${rect.left}px`;
            modalContent.style.width = `${rect.width}px`;
            modalContent.style.height = `${rect.height}px`;
            modal.style.display = 'block';

            // Start expansion animation
            requestAnimationFrame(() => {
                backdrop.classList.add('active');
                modal.classList.add('active');
                modalContent.classList.add('expanded');
                closeBtn.classList.add('active');
                
                // Clear explicit dimensions after adding expanded class
                modalContent.style.width = '';
                modalContent.style.height = '';
            });

            // Update content
            modalContent.innerHTML = `
                ${this.innerHTML}
                <div class="modal-content-info">
                    <h2>Chat type Discord</h2>
                    <p>Detailed information about the project, including technologies used, challenges faced, and solutions implemented.</p>
                    <p>Additional details and insights into the development process.</p>
                </div>
            `;
        });
    });

    function closeModal() {
        if (!currentItem) return;
        const rect = currentItem.getBoundingClientRect();

        // Set explicit dimensions for closing animation
        modalContent.style.width = `${rect.width}px`;
        modalContent.style.height = `${rect.height}px`;
        modalContent.style.top = `${rect.top}px`;
        modalContent.style.left = `${rect.left}px`;

        // Remove active classes
        backdrop.classList.remove('active');
        modal.classList.remove('active');
        modalContent.classList.remove('expanded');
        closeBtn.classList.remove('active');

        setTimeout(() => {
            modal.style.display = 'none';
            currentItem = null;
            // Clear inline styles
            modalContent.style.width = '';
            modalContent.style.height = '';
        }, 500);
    }

    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
});