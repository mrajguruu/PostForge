<?php
/**
 * Category Archive Page
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

$categorySlug = $_GET['slug'] ?? '';

if (empty($categorySlug)) {
    header('Location: index.php');
    exit;
}

try {
    $db = getDB();

    // Get category
    $catStmt = $db->prepare("SELECT * FROM categories WHERE slug = :slug");
    $catStmt->execute(['slug' => $categorySlug]);
    $category = $catStmt->fetch();

    if (!$category) {
        header('Location: index.php');
        exit;
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Get total posts in category
    $countStmt = $db->prepare("
        SELECT COUNT(*) as count FROM posts
        WHERE category_id = :category_id AND status = 'published'
    ");
    $countStmt->execute(['category_id' => $category['id']]);
    $total = $countStmt->fetch()['count'];

    // Get pagination
    $pagination = getPagination($total, POSTS_PER_PAGE, $page);

    // Get posts in category
    $postsStmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug, a.full_name as author_name,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN admins a ON p.author_id = a.id
        WHERE p.category_id = :category_id AND p.status = 'published'
        ORDER BY p.published_at DESC
        LIMIT :offset, :limit
    ");

    $postsStmt->execute([
        'category_id' => $category['id'],
        'offset' => $pagination['offset'],
        'limit' => POSTS_PER_PAGE
    ]);
    $posts = $postsStmt->fetchAll();

    $pageTitle = $category['name'];
    $metaDescription = $category['description'];

} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<!-- Category Header -->
<div class="hero-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">
                    <?php echo sanitize($category['name']); ?>
                </li>
            </ol>
        </nav>
        <h1><i class="bi bi-folder me-2"></i><?php echo sanitize($category['name']); ?></h1>
        <?php if ($category['description']): ?>
            <p class="lead"><?php echo sanitize($category['description']); ?></p>
        <?php endif; ?>
        <p class="mb-0">
            <i class="bi bi-file-text me-2"></i>
            <?php echo formatNumber($total); ?> post<?php echo $total != 1 ? 's' : ''; ?> in this category
        </p>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Posts Grid -->
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No posts in this category yet.
                    <a href="index.php" class="alert-link">Browse all posts</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card post-card">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?php echo UPLOAD_URL . sanitize($post['featured_image']); ?>"
                                 alt="<?php echo sanitize($post['title']); ?>"
                                 class="post-card-img">
                        <?php else: ?>
                            <div class="post-card-img bg-secondary d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>

                        <div class="post-card-body">
                            <a href="post.php?slug=<?php echo $post['slug']; ?>"
                               class="post-card-title">
                                <?php echo sanitize($post['title']); ?>
                            </a>

                            <p class="post-card-excerpt">
                                <?php echo sanitize(truncate($post['excerpt'], 100)); ?>
                            </p>

                            <div class="post-meta">
                                <span class="post-meta-item">
                                    <i class="bi bi-calendar3"></i>
                                    <?php echo formatDate($post['published_at'], 'M d, Y'); ?>
                                </span>
                                <span class="post-meta-item">
                                    <i class="bi bi-eye"></i>
                                    <?php echo formatNumber($post['views']); ?>
                                </span>
                                <span class="post-meta-item">
                                    <i class="bi bi-chat"></i>
                                    <?php echo $post['comment_count']; ?>
                                </span>
                            </div>

                            <a href="post.php?slug=<?php echo $post['slug']; ?>"
                               class="btn btn-outline-primary btn-sm mt-3 w-100">
                                Read More <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_prev']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?slug=<?php echo $categorySlug; ?>&page=<?php echo $page - 1; ?>">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?slug=<?php echo $categorySlug; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagination['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?slug=<?php echo $categorySlug; ?>&page=<?php echo $page + 1; ?>">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Back to Home -->
    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-house me-2"></i>Back to Home
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
