/**
 * Admin Panel JavaScript
 * Blog Management System
 */

document.addEventListener('DOMContentLoaded', function() {

    // ===== Auto-dismiss alerts after 5 seconds =====
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // ===== Confirm delete actions =====
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-delete') ||
                          'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // ===== Auto-generate slug from title =====
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            // Only auto-generate if slug is empty or hasn't been manually edited
            if (!slugInput.dataset.manuallyEdited) {
                const slug = createSlug(this.value);
                slugInput.value = slug;
            }
        });

        // Mark as manually edited when user types in slug field
        slugInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
        });
    }

    // ===== Image preview before upload =====
    const imageInput = document.getElementById('featured_image');
    const imagePreview = document.getElementById('image-preview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                // Validate file size (2MB max)
                const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                if (file.size > maxSize) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, PNG, and GIF files are allowed');
                    this.value = '';
                    imagePreview.style.display = 'none';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ===== Form validation =====
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // ===== Character counter for textarea =====
    const textareas = document.querySelectorAll('[data-max-length]');
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('data-max-length'));
        const counter = document.createElement('small');
        counter.className = 'text-muted';
        textarea.parentNode.appendChild(counter);

        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;

            if (remaining < 0) {
                counter.classList.add('text-danger');
                counter.classList.remove('text-muted');
            } else {
                counter.classList.add('text-muted');
                counter.classList.remove('text-danger');
            }
        }

        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });

    // ===== Search with debounce =====
    const searchInput = document.querySelector('[data-search]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Auto-submit form or trigger search
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }, 500);
        });
    }

    // ===== Bulk actions =====
    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBtn = document.getElementById('bulk-action-btn');

    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButton();
        });

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionButton);
        });

        function updateBulkActionButton() {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            if (bulkActionBtn) {
                bulkActionBtn.disabled = checkedCount === 0;
                bulkActionBtn.textContent = `Apply to ${checkedCount} item(s)`;
            }
        }
    }

    // ===== Rich text editor initialization (TinyMCE alternative - simple) =====
    const contentEditor = document.getElementById('content');
    if (contentEditor) {
        // Add formatting toolbar
        createEditorToolbar(contentEditor);
    }

    // ===== Tooltips =====
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ===== Auto-save draft (optional) =====
    const postForm = document.getElementById('post-form');
    if (postForm) {
        let autoSaveTimeout;
        const formInputs = postForm.querySelectorAll('input, textarea, select');

        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    // Implement auto-save logic here
                }, 3000);
            });
        });
    }

});

// ===== UTILITY FUNCTIONS =====

/**
 * Create URL-friendly slug from string
 */
function createSlug(str) {
    return str
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Create simple editor toolbar
 */
function createEditorToolbar(textarea) {
    const toolbar = document.createElement('div');
    toolbar.className = 'editor-toolbar mb-2 p-2 bg-light border rounded';
    toolbar.innerHTML = `
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary" data-command="bold" title="Bold">
                <i class="bi bi-type-bold"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="italic" title="Italic">
                <i class="bi bi-type-italic"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="underline" title="Underline">
                <i class="bi bi-type-underline"></i>
            </button>
        </div>
        <div class="btn-group btn-group-sm ms-2" role="group">
            <button type="button" class="btn btn-outline-secondary" data-command="h2" title="Heading 2">
                <i class="bi bi-type-h2"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="h3" title="Heading 3">
                <i class="bi bi-type-h3"></i>
            </button>
        </div>
        <div class="btn-group btn-group-sm ms-2" role="group">
            <button type="button" class="btn btn-outline-secondary" data-command="ul" title="Bullet List">
                <i class="bi bi-list-ul"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-command="ol" title="Numbered List">
                <i class="bi bi-list-ol"></i>
            </button>
        </div>
    `;

    textarea.parentNode.insertBefore(toolbar, textarea);

    // Handle toolbar button clicks
    toolbar.querySelectorAll('[data-command]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const command = this.dataset.command;
            insertFormatting(textarea, command);
        });
    });
}

/**
 * Insert formatting at cursor position
 */
function insertFormatting(textarea, command) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    let replacement = selectedText;

    switch(command) {
        case 'bold':
            replacement = `<strong>${selectedText || 'bold text'}</strong>`;
            break;
        case 'italic':
            replacement = `<em>${selectedText || 'italic text'}</em>`;
            break;
        case 'underline':
            replacement = `<u>${selectedText || 'underlined text'}</u>`;
            break;
        case 'h2':
            replacement = `<h2>${selectedText || 'Heading 2'}</h2>`;
            break;
        case 'h3':
            replacement = `<h3>${selectedText || 'Heading 3'}</h3>`;
            break;
        case 'ul':
            replacement = `<ul>\n  <li>${selectedText || 'List item'}</li>\n</ul>`;
            break;
        case 'ol':
            replacement = `<ol>\n  <li>${selectedText || 'List item'}</li>\n</ol>`;
            break;
    }

    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + replacement.length, start + replacement.length);
}

/**
 * Show loading spinner
 */
function showLoading() {
    const spinner = document.createElement('div');
    spinner.id = 'loading-spinner';
    spinner.className = 'spinner-overlay';
    spinner.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    document.body.appendChild(spinner);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}
