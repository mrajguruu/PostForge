<?php
/**
 * Categories Management Page
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$pageTitle = 'Categories';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            redirect('categories.php', 'Category name is required', 'error');
        }

        $slug = createSlug($name);

        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)");
            $stmt->execute([
                'name' => $name,
                'slug' => $slug,
                'description' => $description
            ]);

            // Track user-created category in session
            $newCategoryId = $db->lastInsertId();
            if (!isset($_SESSION['user_created_categories'])) {
                $_SESSION['user_created_categories'] = [];
            }
            $_SESSION['user_created_categories'][] = $newCategoryId;

            redirect('categories.php', 'Category added successfully! This is a temporary category that will be removed when you close your browser.', 'success');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                redirect('categories.php', 'Category already exists', 'error');
            }
            redirect('categories.php', 'Error adding category', 'error');
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            redirect('categories.php', 'Category name is required', 'error');
        }

        $slug = createSlug($name);

        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id");
            $stmt->execute([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'id' => $id
            ]);

            redirect('categories.php', 'Category updated successfully!', 'success');
        } catch (PDOException $e) {
            redirect('categories.php', 'Error updating category', 'error');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    try {
        $db = getDB();

        // Check if this is demo data
        $stmt = $db->prepare("SELECT is_demo FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();

        // Block deletion of demo content
        if ($category && $category['is_demo'] == 1) {
            redirect('categories.php', 'Cannot delete demo categories. Feel free to create your own categories to test the delete functionality!', 'warning');
        }

        // Check if user created this category in current session
        if (!isset($_SESSION['user_created_categories']) || !in_array($id, $_SESSION['user_created_categories'])) {
            redirect('categories.php', 'You can only delete categories you created in this session', 'warning');
        }

        // Check if category has posts
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM posts WHERE category_id = :id");
        $checkStmt->execute(['id' => $id]);
        $result = $checkStmt->fetch();

        if ($result['count'] > 0) {
            redirect('categories.php', 'Cannot delete category with existing posts', 'error');
        }

        // Delete category
        $deleteStmt = $db->prepare("DELETE FROM categories WHERE id = :id");
        $deleteStmt->execute(['id' => $id]);

        // Remove from session tracking
        $_SESSION['user_created_categories'] = array_diff($_SESSION['user_created_categories'], [$id]);

        redirect('categories.php', 'Category deleted successfully!', 'success');
    } catch (PDOException $e) {
        redirect('categories.php', 'Error deleting category', 'error');
    }
}

// Get all categories with post count
try {
    $db = getDB();
    $stmt = $db->query("
        SELECT c.*, COUNT(p.id) as post_count
        FROM categories c
        LEFT JOIN posts p ON c.id = p.category_id
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error = 'Error loading categories';
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="col-md-9 col-lg-10 main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-folder me-2"></i>Categories</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle me-2"></i>Add Category
        </button>
    </div>

    <?php echo displayFlashMessage(); ?>

    <!-- Categories Grid View -->
    <div class="row mb-4">
        <?php if (empty($categories)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>No categories yet. Add your first category!
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-hover">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-folder-fill text-primary me-2"></i>
                                    <?php echo sanitize($category['name']); ?>
                                </h5>
                                <span class="badge bg-primary"><?php echo $category['post_count']; ?> posts</span>
                            </div>

                            <?php if ($category['description']): ?>
                                <p class="card-text text-muted small">
                                    <?php echo sanitize(truncate($category['description'], 100)); ?>
                                </p>
                            <?php else: ?>
                                <p class="card-text text-muted small fst-italic">No description</p>
                            <?php endif; ?>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-link-45deg me-1"></i>
                                    <?php echo sanitize($category['slug']); ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-fill"
                                        onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                <?php if ($category['post_count'] == 0): ?>
                                    <a href="?delete=<?php echo $category['id']; ?>"
                                       class="btn btn-sm btn-outline-danger flex-fill"
                                       onclick="return confirm('Delete this category?')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary flex-fill" disabled
                                            title="Cannot delete category with posts">
                                        <i class="bi bi-lock me-1"></i>Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Table View (Alternative) -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">All Categories</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($categories)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Posts</th>
                                <th>Created</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><strong><?php echo sanitize($category['name']); ?></strong></td>
                                    <td><code><?php echo sanitize($category['slug']); ?></code></td>
                                    <td><?php echo sanitize(truncate($category['description'] ?? 'No description', 50)); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $category['post_count']; ?></span></td>
                                    <td><small><?php echo formatDate($category['created_at']); ?></small></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($category['post_count'] == 0): ?>
                                            <a href="?delete=<?php echo $category['id']; ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Delete this category?')"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description || '';

    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>
