<?php
/**
 * All Posts Page
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$pageTitle = 'All Posts';

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
        $error = 'Failed to delete post';
    }
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

try {
    $db = getDB();

    // Build query
    $conditions = [];
    $params = [];

    if ($search) {
        $conditions[] = "p.title LIKE :search";
        $params['search'] = "%$search%";
    }

    if ($status !== 'all') {
        $conditions[] = "p.status = :status";
        $params['status'] = $status;
    }

    if ($category > 0) {
        $conditions[] = "p.category_id = :category";
        $params['category'] = $category;
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) as count FROM posts p $where");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['count'];

    // Get pagination
    $pagination = getPagination($total, POSTS_PER_PAGE, $page);

    // Get posts
    $params['offset'] = $pagination['offset'];
    $params['limit'] = POSTS_PER_PAGE;

    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, a.full_name as author_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN admins a ON p.author_id = a.id
        $where
        ORDER BY p.created_at DESC
        LIMIT :offset, :limit
    ");

    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    // Get categories for filter
    $categoriesStmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $categoriesStmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="col-md-9 col-lg-10 main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-text me-2"></i>All Posts</h2>
        <a href="post-form.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Post
        </a>
    </div>

    <?php echo displayFlashMessage(); ?>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search posts..."
                           value="<?php echo sanitize($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                    <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo sanitize($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($posts)): ?>
                <p class="text-center text-muted p-5">No posts found</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <?php if ($post['featured_image']): ?>
                                            <img src="<?php echo UPLOAD_URL . sanitize($post['featured_image']); ?>"
                                                 alt="<?php echo sanitize($post['title']); ?>"
                                                 class="post-thumbnail">
                                        <?php else: ?>
                                            <div class="post-thumbnail bg-secondary d-flex align-items-center justify-content-center">
                                                <i class="bi bi-image text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo sanitize($post['title']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo sanitize(truncate($post['excerpt'] ?? '', 60)); ?></small>
                                    </td>
                                    <td><?php echo sanitize($post['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo sanitize($post['author_name']); ?></td>
                                    <td><?php echo getStatusBadge($post['status']); ?></td>
                                    <td><i class="bi bi-eye me-1"></i><?php echo formatNumber($post['views']); ?></td>
                                    <td>
                                        <small><?php echo formatDate($post['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <a href="post-form.php?id=<?php echo $post['id']; ?>"
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?delete=<?php echo $post['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this post?')"
                                           title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                Showing <?php echo $pagination['offset'] + 1; ?> -
                                <?php echo min($pagination['offset'] + POSTS_PER_PAGE, $total); ?>
                                of <?php echo $total; ?> posts
                            </span>
                            <nav>
                                <ul class="pagination mb-0">
                                    <?php if ($pagination['has_prev']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagination['has_next']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
