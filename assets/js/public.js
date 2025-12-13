/**
 * Public Site JavaScript
 * Blog Management System
 */

document.addEventListener('DOMContentLoaded', function() {

    // ===== Smooth Scroll for Anchor Links =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = this.getAttribute('href');
            if (target !== '#') {
                e.preventDefault();
                const element = document.querySelector(target);
                if (element) {
                    element.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ===== Auto-dismiss Alerts =====
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // ===== Comment Form Validation =====
    const commentForm = document.querySelector('form[name="comment_form"], form:has([name="submit_comment"])');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            const name = this.querySelector('[name="name"]');
            const email = this.querySelector('[name="email"]');
            const comment = this.querySelector('[name="comment"]');

            let isValid = true;
            let errors = [];

            // Validate name
            if (!name.value.trim()) {
                errors.push('Name is required');
                isValid = false;
            }

            // Validate email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim()) {
                errors.push('Email is required');
                isValid = false;
            } else if (!emailPattern.test(email.value)) {
                errors.push('Please enter a valid email address');
                isValid = false;
            }

            // Validate comment
            if (!comment.value.trim()) {
                errors.push('Comment is required');
                isValid = false;
            } else if (comment.value.trim().length < 10) {
                errors.push('Comment must be at least 10 characters');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert(errors.join('\n'));
                return false;
            }
        });
    }

    // ===== Character Counter for Comment Textarea =====
    const commentTextarea = document.querySelector('[name="comment"]');
    if (commentTextarea) {
        const counter = document.createElement('small');
        counter.className = 'text-muted d-block mt-1';
        commentTextarea.parentNode.appendChild(counter);

        function updateCounter() {
            const length = commentTextarea.value.length;
            const minLength = 10;
            const remaining = minLength - length;

            if (length === 0) {
                counter.textContent = 'Minimum 10 characters required';
                counter.classList.remove('text-success');
                counter.classList.add('text-muted');
            } else if (remaining > 0) {
                counter.textContent = `${remaining} more character${remaining !== 1 ? 's' : ''} needed`;
                counter.classList.remove('text-success');
                counter.classList.add('text-warning');
            } else {
                counter.textContent = `${length} characters`;
                counter.classList.remove('text-warning');
                counter.classList.add('text-success');
            }
        }

        commentTextarea.addEventListener('input', updateCounter);
        updateCounter();
    }

    // ===== Reading Progress Bar =====
    const progressBar = createReadingProgressBar();
    if (progressBar && document.querySelector('.post-content')) {
        document.body.insertBefore(progressBar, document.body.firstChild);

        window.addEventListener('scroll', updateReadingProgress);
        updateReadingProgress();
    }

    // ===== Scroll to Top Button =====
    const scrollTopBtn = createScrollToTopButton();
    document.body.appendChild(scrollTopBtn);

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollTopBtn.classList.add('show');
        } else {
            scrollTopBtn.classList.remove('show');
        }
    });

    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // ===== Image Lazy Loading Enhancement =====
    const images = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // ===== Copy Code Blocks =====
    const codeBlocks = document.querySelectorAll('pre code');
    codeBlocks.forEach(block => {
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-wrapper';
        block.parentNode.parentNode.insertBefore(wrapper, block.parentNode);
        wrapper.appendChild(block.parentNode);

        const copyBtn = document.createElement('button');
        copyBtn.className = 'btn btn-sm btn-outline-light copy-code-btn';
        copyBtn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
        copyBtn.style.cssText = 'position: absolute; top: 10px; right: 10px;';

        wrapper.style.position = 'relative';
        wrapper.appendChild(copyBtn);

        copyBtn.addEventListener('click', function() {
            const code = block.textContent;
            navigator.clipboard.writeText(code).then(() => {
                copyBtn.innerHTML = '<i class="bi bi-check"></i> Copied!';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                }, 2000);
            });
        });
    });

    // ===== Social Share Click Tracking =====
    const shareButtons = document.querySelectorAll('[href*="facebook.com/sharer"], [href*="twitter.com/intent"], [href*="linkedin.com/share"]');
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Share button clicked tracking can be implemented here
        });
    });

    // ===== External Links in New Tab =====
    const externalLinks = document.querySelectorAll('a[href^="http"]');
    externalLinks.forEach(link => {
        if (!link.hostname.includes(window.location.hostname)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });

    // ===== Table Responsive Wrapper =====
    const tables = document.querySelectorAll('.post-content table');
    tables.forEach(table => {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
            table.classList.add('table', 'table-bordered');
        }
    });

    // ===== Initialize Tooltips =====
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

});

// ===== UTILITY FUNCTIONS =====

/**
 * Create reading progress bar
 */
function createReadingProgressBar() {
    const progress = document.createElement('div');
    progress.id = 'reading-progress';
    progress.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #2563eb, #06b6d4);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    return progress;
}

/**
 * Update reading progress
 */
function updateReadingProgress() {
    const progressBar = document.getElementById('reading-progress');
    if (!progressBar) return;

    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight - windowHeight;
    const scrolled = window.pageYOffset;
    const progress = (scrolled / documentHeight) * 100;

    progressBar.style.width = progress + '%';
}

/**
 * Create scroll to top button
 */
function createScrollToTopButton() {
    const button = document.createElement('button');
    button.id = 'scroll-to-top';
    button.innerHTML = '<i class="bi bi-arrow-up"></i>';
    button.className = 'scroll-to-top-btn';
    button.setAttribute('aria-label', 'Scroll to top');

    const style = document.createElement('style');
    style.textContent = `
        .scroll-to-top-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .scroll-to-top-btn:hover {
            background-color: #1e40af;
            transform: translateY(-3px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .scroll-to-top-btn.show {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 768px) {
            .scroll-to-top-btn {
                bottom: 20px;
                right: 20px;
                width: 40px;
                height: 40px;
            }
        }
    `;
    document.head.appendChild(style);

    return button;
}

/**
 * Format date to relative time
 */
function formatRelativeTime(date) {
    const now = new Date();
    const then = new Date(date);
    const diffInSeconds = Math.floor((now - then) / 1000);

    if (diffInSeconds < 60) {
        return 'just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} hour${hours !== 1 ? 's' : ''} ago`;
    } else if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} day${days !== 1 ? 's' : ''} ago`;
    } else {
        return then.toLocaleDateString();
    }
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Animate number counter
 */
function animateCounter(element, target, duration = 1000) {
    let start = 0;
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = Math.floor(target);
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

/**
 * Check if element is in viewport
 */
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

/**
 * Show notification toast
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.textContent = message;

    const style = document.createElement('style');
    style.textContent = `
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            animation: slideUp 0.3s ease;
        }

        .toast-success { border-left: 4px solid #10b981; }
        .toast-error { border-left: 4px solid #ef4444; }
        .toast-warning { border-left: 4px solid #f59e0b; }
        .toast-info { border-left: 4px solid #06b6d4; }

        @keyframes slideUp {
            from {
                transform: translateX(-50%) translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }
    `;

    if (!document.querySelector('style[data-toast-styles]')) {
        style.setAttribute('data-toast-styles', 'true');
        document.head.appendChild(style);
    }

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideUp 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
