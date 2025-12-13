<?php
/**
 * Admin Dashboard
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$pageTitle = 'Dashboard';

try {
    $db = getDB();

    // Get statistics
    $stats = [];

    // Total posts
    $stmt = $db->query("SELECT COUNT(*) as count FROM posts");
    $stats['total_posts'] = $stmt->fetch()['count'];

    // Published posts
    $stmt = $db->query("SELECT COUNT(*) as count FROM posts WHERE status = 'published'");
    $stats['published_posts'] = $stmt->fetch()['count'];

    // Draft posts
    $stmt = $db->query("SELECT COUNT(*) as count FROM posts WHERE status = 'draft'");
    $stats['draft_posts'] = $stmt->fetch()['count'];

    // Total categories
    $stmt = $db->query("SELECT COUNT(*) as count FROM categories");
    $stats['total_categories'] = $stmt->fetch()['count'];

    // Total comments
    $stmt = $db->query("SELECT COUNT(*) as count FROM comments");
    $stats['total_comments'] = $stmt->fetch()['count'];

    // Pending comments
    $stmt = $db->query("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'");
    $stats['pending_comments'] = $stmt->fetch()['count'];

    // Total views
    $stmt = $db->query("SELECT COALESCE(SUM(views), 0) as total FROM posts");
    $stats['total_views'] = $stmt->fetch()['total'];

    // Total admin users
    $stmt = $db->query("SELECT COUNT(*) as count FROM admins");
    $stats['total_admins'] = $stmt->fetch()['count'];

    // Recent posts
    $recentPostsStmt = $db->query("
        SELECT p.*, c.name as category_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $recentPosts = $recentPostsStmt->fetchAll();

    // Recent comments
    $recentCommentsStmt = $db->query("
        SELECT cm.*, p.title as post_title, p.slug as post_slug
        FROM comments cm
        JOIN posts p ON cm.post_id = p.id
        ORDER BY cm.created_at DESC
        LIMIT 5
    ");
    $recentComments = $recentCommentsStmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="col-md-9 col-lg-10 main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
        <div>
            <span class="badge bg-secondary me-2">
                <i class="bi bi-people me-1"></i>Total Admin Users: <?php echo $stats['total_admins']; ?>
            </span>
            <span class="text-muted">Welcome, <?php echo sanitize($_SESSION['admin_name']); ?>!</span>
        </div>
    </div>

    <?php echo displayFlashMessage(); ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Posts</h6>
                            <h2 class="mb-0"><?php echo formatNumber($stats['total_posts']); ?></h2>
                        </div>
                        <i class="bi bi-file-text stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Categories</h6>
                            <h2 class="mb-0"><?php echo formatNumber($stats['total_categories']); ?></h2>
                        </div>
                        <i class="bi bi-folder stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Pending Comments</h6>
                            <h2 class="mb-0"><?php echo formatNumber($stats['pending_comments']); ?></h2>
                        </div>
                        <i class="bi bi-chat-dots stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Views</h6>
                            <h2 class="mb-0"><?php echo formatNumber($stats['total_views']); ?></h2>
                        </div>
                        <i class="bi bi-eye stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Posts and Comments -->
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Recent Posts</h5>
                    <a href="posts.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentPosts)): ?>
                        <p class="text-center text-muted p-4">No posts yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPosts as $post): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo sanitize(truncate($post['title'], 40)); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo timeAgo($post['created_at']); ?></small>
                                            </td>
                                            <td><?php echo sanitize($post['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td><?php echo getStatusBadge($post['status']); ?></td>
                                            <td><i class="bi bi-eye me-1"></i><?php echo formatNumber($post['views']); ?></td>
                                            <td>
                                                <a href="post-form.php?id=<?php echo $post['id']; ?>"
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
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

        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Recent Comments</h5>
                    <a href="comments.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentComments)): ?>
                        <p class="text-center text-muted">No comments yet</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentComments as $comment): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong><?php echo sanitize($comment['author_name']); ?></strong>
                                        <?php echo getStatusBadge($comment['status']); ?>
                                    </div>
                                    <p class="mb-2 small"><?php echo sanitize(truncate($comment['content'], 80)); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            On: <a href="../public/post.php?slug=<?php echo $comment['post_slug']; ?>" target="_blank">
                                                <?php echo sanitize(truncate($comment['post_title'], 30)); ?>
                                            </a>
                                        </small>
                                        <small class="text-muted"><?php echo timeAgo($comment['created_at']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="post-form.php" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-2"></i>Create New Post
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="categories.php" class="btn btn-success w-100">
                                <i class="bi bi-folder-plus me-2"></i>Add Category
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="comments.php?status=pending" class="btn btn-warning w-100">
                                <i class="bi bi-chat-dots me-2"></i>Review Comments
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="../public/index.php" target="_blank" class="btn btn-info w-100">
                                <i class="bi bi-eye me-2"></i>View Public Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
