<?php
/**
 * Add/Edit Post Form
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$pageTitle = 'Add New Post';
$isEdit = false;
$post = null;

// Handle post deletion
if (isset($_GET['delete'])) {
    $postId = (int)$_GET['delete'];

    try {
        $db = getDB();

        // Check if this is demo data
        $stmt = $db->prepare("SELECT featured_image, is_demo FROM posts WHERE id = :id");
        $stmt->execute(['id' => $postId]);
        $post = $stmt->fetch();

        // Block deletion of demo content
        if ($post && $post['is_demo'] == 1) {
            redirect('posts.php', 'Cannot delete demo content. Feel free to create your own posts to test the delete functionality!', 'warning');
        }

        // Check if user created this post in current session
        if (!isset($_SESSION['user_created_posts']) || !in_array($postId, $_SESSION['user_created_posts'])) {
            redirect('posts.php', 'You can only delete posts you created in this session', 'warning');
        }

        if ($post && $post['featured_image']) {
            deleteUploadedFile($post['featured_image']);
        }

        // Delete post
        $deleteStmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $deleteStmt->execute(['id' => $postId]);

        // Remove from session tracking
        $_SESSION['user_created_posts'] = array_diff($_SESSION['user_created_posts'], [$postId]);

        redirect('posts.php', 'Post deleted successfully', 'success');
    } catch (PDOException $e) {
        redirect('posts.php', 'Failed to delete post', 'error');
    }
}

// Check if editing
if (isset($_GET['id'])) {
    $isEdit = true;
    $pageTitle = 'Edit Post';
    $postId = (int)$_GET['id'];

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute(['id' => $postId]);
        $post = $stmt->fetch();

        if (!$post) {
            redirect('posts.php', 'Post not found', 'error');
        }
    } catch (PDOException $e) {
        redirect('posts.php', 'Error loading post', 'error');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $status = $_POST['status'] ?? 'draft';
    $currentImage = $_POST['current_image'] ?? '';

    // Validation
    $errors = [];

    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Title must be less than 255 characters';
    }

    if (empty($slug)) {
        $slug = createSlug($title);
    }

    if (empty($content)) {
        $errors[] = 'Content is required';
    } elseif (strlen($content) < 100) {
        $errors[] = 'Content must be at least 100 characters';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Please select a category';
    }

    // Generate excerpt if empty
    if (empty($excerpt)) {
        $excerpt = generateExcerpt($content, 200);
    }

    // Handle image upload
    $imageName = $currentImage;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['featured_image']);

        if ($uploadResult['success']) {
            // Delete old image if exists
            if ($currentImage) {
                deleteUploadedFile($currentImage);
            }
            $imageName = $uploadResult['filename'];
        } else {
            $errors = array_merge($errors, $uploadResult['errors']);
        }
    }

    if (empty($errors)) {
        try {
            $db = getDB();

            if ($isEdit) {
                // Update existing post
                $stmt = $db->prepare("
                    UPDATE posts SET
                        title = :title,
                        slug = :slug,
                        content = :content,
                        excerpt = :excerpt,
                        featured_image = :image,
                        category_id = :category_id,
                        status = :status,
                        published_at = :published_at
                    WHERE id = :id
                ");

                $stmt->execute([
                    'title' => $title,
                    'slug' => $slug,
                    'content' => sanitizeHTML($content),
                    'excerpt' => $excerpt,
                    'image' => $imageName,
                    'category_id' => $categoryId,
                    'status' => $status,
                    'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
                    'id' => $postId
                ]);

                redirect('posts.php', 'Post updated successfully!', 'success');
            } else {
                // Create new post
                $stmt = $db->prepare("
                    INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, author_id, status, published_at)
                    VALUES (:title, :slug, :content, :excerpt, :image, :category_id, :author_id, :status, :published_at)
                ");

                $stmt->execute([
                    'title' => $title,
                    'slug' => $slug,
                    'content' => sanitizeHTML($content),
                    'excerpt' => $excerpt,
                    'image' => $imageName,
                    'category_id' => $categoryId,
                    'author_id' => getCurrentAdminId(),
                    'status' => $status,
                    'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null
                ]);

                // Track user-created post in session
                $newPostId = $db->lastInsertId();
                if (!isset($_SESSION['user_created_posts'])) {
                    $_SESSION['user_created_posts'] = [];
                }
                $_SESSION['user_created_posts'][] = $newPostId;

                redirect('posts.php', 'Post created successfully! This is a temporary post that will be removed when you close your browser.', 'success');
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'A post with this slug already exists';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get categories
try {
    $db = getDB();
    $categoriesStmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="col-md-9 col-lg-10 main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-<?php echo $isEdit ? 'pencil' : 'plus-circle'; ?> me-2"></i>
            <?php echo $pageTitle; ?>
        </h2>
        <a href="posts.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Posts
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo sanitize($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" id="post-form" class="needs-validation" novalidate>
        <input type="hidden" name="current_image" value="<?php echo $post['featured_image'] ?? ''; ?>">

        <div class="row">
            <div class="col-lg-8">
                <!-- Title -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Post Title *</label>
                            <input type="text" class="form-control form-control-lg" id="title" name="title"
                                   value="<?php echo sanitize($post['title'] ?? $_POST['title'] ?? ''); ?>"
                                   placeholder="Enter post title..." required>
                            <div class="invalid-feedback">Please enter a title</div>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">URL Slug *</label>
                            <input type="text" class="form-control" id="slug" name="slug"
                                   value="<?php echo sanitize($post['slug'] ?? $_POST['slug'] ?? ''); ?>"
                                   placeholder="post-url-slug" required>
                            <small class="text-muted">Auto-generated from title. Edit if needed.</small>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Content *</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo sanitize($post['content'] ?? $_POST['content'] ?? ''); ?></textarea>
                        <small class="text-muted">Minimum 100 characters required</small>
                    </div>
                </div>

                <!-- Excerpt -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Excerpt (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"
                                  placeholder="Brief summary... (auto-generated if left empty)"><?php echo sanitize($post['excerpt'] ?? $_POST['excerpt'] ?? ''); ?></textarea>
                        <small class="text-muted">Short description for post previews</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Publish -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Publish</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_draft"
                                       value="draft" <?php echo (!isset($post) || $post['status'] === 'draft') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status_draft">
                                    <i class="bi bi-file-earmark text-warning"></i> Save as Draft
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_published"
                                       value="published" <?php echo (isset($post) && $post['status'] === 'published') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status_published">
                                    <i class="bi bi-check-circle text-success"></i> Publish
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i><?php echo $isEdit ? 'Update Post' : 'Create Post'; ?>
                            </button>
                            <?php if ($isEdit): ?>
                                <a href="?delete=<?php echo $post['id']; ?>"
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this post?')">
                                    <i class="bi bi-trash me-2"></i>Delete Post
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Category *</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"
                                        <?php echo (isset($post) && $post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a category</div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Featured Image</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($post) && $post['featured_image']): ?>
                            <div class="mb-3">
                                <img src="<?php echo UPLOAD_URL . sanitize($post['featured_image']); ?>"
                                     alt="Current image" class="img-fluid rounded" id="current-image-preview">
                            </div>
                        <?php endif; ?>

                        <input type="file" class="form-control" id="featured_image" name="featured_image"
                               accept="image/jpeg,image/png,image/gif">
                        <small class="text-muted d-block mt-2">Max size: 2MB. Formats: JPG, PNG, GIF</small>

                        <img id="image-preview" class="img-fluid rounded mt-3" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
