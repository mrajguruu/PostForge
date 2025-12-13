<?php
/**
 * Single Post View
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();

    // Get post
    $postStmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug, a.full_name as author_name, a.profile_image as author_image
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN admins a ON p.author_id = a.id
        WHERE p.slug = :slug AND p.status = 'published'
    ");
    $postStmt->execute(['slug' => $slug]);
    $post = $postStmt->fetch();

    if (!$post) {
        header('Location: index.php');
        exit;
    }

    // Increment view count
    $viewStmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
    $viewStmt->execute(['id' => $post['id']]);

    // Get approved comments
    $commentsStmt = $db->prepare("
        SELECT * FROM comments
        WHERE post_id = :post_id AND status = 'approved'
        ORDER BY created_at DESC
    ");
    $commentsStmt->execute(['post_id' => $post['id']]);
    $comments = $commentsStmt->fetchAll();

    // Get related posts
    $relatedStmt = $db->prepare("
        SELECT * FROM posts
        WHERE category_id = :category_id
        AND id != :post_id
        AND status = 'published'
        ORDER BY published_at DESC
        LIMIT 3
    ");
    $relatedStmt->execute([
        'category_id' => $post['category_id'],
        'post_id' => $post['id']
    ]);
    $relatedPosts = $relatedStmt->fetchAll();

    $pageTitle = $post['title'];
    $metaDescription = $post['excerpt'];

} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    $errors = [];

    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    if (empty($comment)) {
        $errors[] = 'Comment is required';
    } elseif (strlen($comment) < 10) {
        $errors[] = 'Comment must be at least 10 characters';
    }

    if (empty($errors)) {
        try {
            $insertStmt = $db->prepare("
                INSERT INTO comments (post_id, author_name, author_email, content, status)
                VALUES (:post_id, :name, :email, :content, 'pending')
            ");

            $insertStmt->execute([
                'post_id' => $post['id'],
                'name' => $name,
                'email' => $email,
                'content' => $comment
            ]);

            $_SESSION['comment_success'] = 'Thank you! Your comment is awaiting moderation.';
            header('Location: post.php?slug=' . $slug . '#comments');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error submitting comment';
        }
    }
}

include 'includes/header.php';
?>

<!-- Post Header -->
<div class="single-post-header">
    <div class="container">
        <a href="category.php?slug=<?php echo $post['category_slug']; ?>"
           class="category-badge mb-3">
            <?php echo sanitize($post['category_name'] ?? 'Uncategorized'); ?>
        </a>

        <h1 class="single-post-title"><?php echo sanitize($post['title']); ?></h1>

        <div class="single-post-meta">
            <span class="d-inline-flex align-items-center">
                <?php if ($post['author_image']): ?>
                    <img src="../uploads/profiles/<?php echo sanitize($post['author_image']); ?>"
                         alt="<?php echo sanitize($post['author_name']); ?>"
                         style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                <?php else: ?>
                    <i class="bi bi-person-circle me-1"></i>
                <?php endif; ?>
                By <?php echo sanitize($post['author_name']); ?>
            </span>
            <span>
                <i class="bi bi-calendar3 me-1"></i>
                <?php echo formatDate($post['published_at'], 'F d, Y'); ?>
            </span>
            <span>
                <i class="bi bi-eye me-1"></i>
                <?php echo formatNumber($post['views']); ?> views
            </span>
            <span>
                <i class="bi bi-chat me-1"></i>
                <?php echo count($comments); ?> comments
            </span>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Featured Image -->
            <?php if ($post['featured_image']): ?>
                <div class="featured-image-container">
                    <img src="<?php echo UPLOAD_URL . sanitize($post['featured_image']); ?>"
                         alt="<?php echo sanitize($post['title']); ?>"
                         class="featured-image">
                </div>
            <?php endif; ?>

            <!-- Post Content -->
            <div class="post-content">
                <?php echo $post['content']; ?>
            </div>

            <!-- Related Posts -->
            <?php if (!empty($relatedPosts)): ?>
                <div class="related-posts mb-4">
                    <h4 class="mb-3">Related Posts</h4>
                    <div class="row">
                        <?php foreach ($relatedPosts as $related): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card post-card h-100">
                                    <?php if ($related['featured_image']): ?>
                                        <img src="<?php echo UPLOAD_URL . sanitize($related['featured_image']); ?>"
                                             alt="<?php echo sanitize($related['title']); ?>"
                                             class="post-card-img" style="height: 150px;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <a href="post.php?slug=<?php echo $related['slug']; ?>"
                                           class="post-card-title" style="font-size: 1rem;">
                                            <?php echo sanitize(truncate($related['title'], 50)); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comments Section -->
            <div class="comments-section" id="comments">
                <h4 class="mb-4">
                    <i class="bi bi-chat-dots me-2"></i>
                    Comments (<?php echo count($comments); ?>)
                </h4>

                <!-- Success Message -->
                <?php if (isset($_SESSION['comment_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php
                        echo sanitize($_SESSION['comment_success']);
                        unset($_SESSION['comment_success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo sanitize($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Comments List -->
                <?php if (!empty($comments)): ?>
                    <div class="comments-list mb-4">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                                <div class="comment-author">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <?php echo sanitize($comment['author_name']); ?>
                                </div>
                                <div class="comment-date">
                                    <?php echo timeAgo($comment['created_at']); ?>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(sanitize($comment['content'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-4">No comments yet. Be the first to comment!</p>
                <?php endif; ?>

                <!-- Comment Form -->
                <div class="comment-form">
                    <h5 class="mb-3">Leave a Comment</h5>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>"
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>"
                                       required>
                                <small class="text-muted">Your email will not be published</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment *</label>
                            <textarea class="form-control" id="comment" name="comment" rows="5"
                                      required><?php echo isset($_POST['comment']) ? sanitize($_POST['comment']) : ''; ?></textarea>
                        </div>
                        <button type="submit" name="submit_comment" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Submit Comment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Author Info -->
            <div class="sidebar-widget">
                <h5><i class="bi bi-person me-2"></i>About Author</h5>
                <div class="text-center">
                    <?php if ($post['author_image']): ?>
                        <img src="../uploads/profiles/<?php echo sanitize($post['author_image']); ?>"
                             alt="<?php echo sanitize($post['author_name']); ?>"
                             style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <?php else: ?>
                        <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; font-weight: bold; border: 3px solid #007bff; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <?php echo strtoupper(substr($post['author_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <h6 class="mt-2"><?php echo sanitize($post['author_name']); ?></h6>
                    <p class="text-muted small">Content Creator</p>
                </div>
            </div>

            <!-- Share Widget -->
            <div class="sidebar-widget">
                <h5><i class="bi bi-share me-2"></i>Share This Post</h5>
                <div class="d-flex gap-2">
                    <a href="https://facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/public/post.php?slug=' . $slug); ?>"
                       target="_blank" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/public/post.php?slug=' . $slug); ?>&text=<?php echo urlencode($post['title']); ?>"
                       target="_blank" class="btn btn-info btn-sm flex-fill">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(SITE_URL . '/public/post.php?slug=' . $slug); ?>"
                       target="_blank" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-linkedin"></i>
                    </a>
                </div>
            </div>

            <!-- Back to Posts -->
            <div class="sidebar-widget">
                <a href="index.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-arrow-left me-2"></i>Back to All Posts
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
