<?php
/**
 * Comments Moderation Page
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$pageTitle = 'Comments';

// Handle comment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $commentId = (int)$_GET['id'];
    $action = $_GET['action'];

    try {
        $db = getDB();

        switch ($action) {
            case 'approve':
                $stmt = $db->prepare("UPDATE comments SET status = 'approved' WHERE id = :id");
                $stmt->execute(['id' => $commentId]);
                redirect('comments.php', 'Comment approved!', 'success');
                break;

            case 'pending':
                $stmt = $db->prepare("UPDATE comments SET status = 'pending' WHERE id = :id");
                $stmt->execute(['id' => $commentId]);
                redirect('comments.php', 'Comment marked as pending', 'info');
                break;

            case 'spam':
                $stmt = $db->prepare("UPDATE comments SET status = 'spam' WHERE id = :id");
                $stmt->execute(['id' => $commentId]);
                redirect('comments.php', 'Comment marked as spam', 'warning');
                break;

            case 'delete':
                $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
                $stmt->execute(['id' => $commentId]);
                redirect('comments.php', 'Comment deleted', 'success');
                break;
        }
    } catch (PDOException $e) {
        redirect('comments.php', 'Error processing comment', 'error');
    }
}

// Get filter
$statusFilter = $_GET['status'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Build query
try {
    $db = getDB();

    $where = '';
    $params = [];

    if ($statusFilter !== 'all') {
        $where = 'WHERE c.status = :status';
        $params['status'] = $statusFilter;
    }

    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) as count FROM comments c $where");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['count'];

    // Get pagination
    $pagination = getPagination($total, COMMENTS_PER_PAGE, $page);

    // Get comments
    $params['offset'] = $pagination['offset'];
    $params['limit'] = COMMENTS_PER_PAGE;

    $stmt = $db->prepare("
        SELECT c.*, p.title as post_title, p.slug as post_slug
        FROM comments c
        JOIN posts p ON c.post_id = p.id
        $where
        ORDER BY c.created_at DESC
        LIMIT :offset, :limit
    ");

    $stmt->execute($params);
    $comments = $stmt->fetchAll();

    // Get status counts
    $countsStmt = $db->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam
        FROM comments
    ");
    $counts = $countsStmt->fetch();

} catch (PDOException $e) {
    $comments = [];
    $error = 'Error loading comments';
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="col-md-9 col-lg-10 main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-chat-dots me-2"></i>Comments</h2>
    </div>

    <?php echo displayFlashMessage(); ?>

    <!-- Status Filter Tabs -->
    <div class="card mb-4">
        <div class="card-body">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?php echo $statusFilter === 'all' ? 'active' : ''; ?>" href="?status=all">
                        All (<?php echo $counts['total']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                        <i class="bi bi-clock"></i> Pending (<?php echo $counts['pending']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>" href="?status=approved">
                        <i class="bi bi-check-circle"></i> Approved (<?php echo $counts['approved']; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $statusFilter === 'spam' ? 'active' : ''; ?>" href="?status=spam">
                        <i class="bi bi-exclamation-triangle"></i> Spam (<?php echo $counts['spam']; ?>)
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Comments List -->
    <?php if (empty($comments)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No comments found.
        </div>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="card mb-3 comment-card <?php echo $comment['status']; ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-person-circle me-2"></i>
                                <strong><?php echo sanitize($comment['author_name']); ?></strong>
                                <small class="text-muted">(<?php echo sanitize($comment['author_email']); ?>)</small>
                            </h6>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i><?php echo timeAgo($comment['created_at']); ?>
                                on
                                <a href="../public/post.php?slug=<?php echo $comment['post_slug']; ?>" target="_blank">
                                    "<?php echo sanitize(truncate($comment['post_title'], 50)); ?>"
                                </a>
                            </small>
                        </div>
                        <div>
                            <?php echo getStatusBadge($comment['status']); ?>
                        </div>
                    </div>

                    <div class="comment-content mb-3">
                        <p class="mb-0"><?php echo nl2br(sanitize($comment['content'])); ?></p>
                    </div>

                    <div class="comment-actions">
                        <?php if ($comment['status'] !== 'approved'): ?>
                            <a href="?action=approve&id=<?php echo $comment['id']; ?>"
                               class="btn btn-sm btn-success">
                                <i class="bi bi-check-circle me-1"></i>Approve
                            </a>
                        <?php endif; ?>

                        <?php if ($comment['status'] !== 'pending'): ?>
                            <a href="?action=pending&id=<?php echo $comment['id']; ?>"
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-clock me-1"></i>Mark Pending
                            </a>
                        <?php endif; ?>

                        <?php if ($comment['status'] !== 'spam'): ?>
                            <a href="?action=spam&id=<?php echo $comment['id']; ?>"
                               class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-exclamation-triangle me-1"></i>Mark as Spam
                            </a>
                        <?php endif; ?>

                        <a href="?action=delete&id=<?php echo $comment['id']; ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Permanently delete this comment?')">
                            <i class="bi bi-trash me-1"></i>Delete
                        </a>

                        <a href="../public/post.php?slug=<?php echo $comment['post_slug']; ?>#comment-<?php echo $comment['id']; ?>"
                           class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>View on Post
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo $statusFilter; ?>&page=<?php echo $page - 1; ?>">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?status=<?php echo $statusFilter; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?status=<?php echo $statusFilter; ?>&page=<?php echo $page + 1; ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.comment-card {
    border-left: 4px solid #dee2e6;
}

.comment-card.pending {
    border-left-color: #f59e0b;
    background-color: #fffbeb;
}

.comment-card.approved {
    border-left-color: #10b981;
}

.comment-card.spam {
    border-left-color: #ef4444;
    background-color: #fef2f2;
}

.comment-content {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.comment-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
</style>

<?php include 'includes/footer.php'; ?>
