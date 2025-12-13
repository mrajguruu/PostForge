<?php
/**
 * Public Homepage
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

$pageTitle = 'Home';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

try {
    $db = getDB();

    // Get featured post (most recent published post)
    $featuredStmt = $db->query("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
        ORDER BY p.published_at DESC
        LIMIT 1
    ");
    $featuredPost = $featuredStmt->fetch();

    // Get total published posts
    $countStmt = $db->query("SELECT COUNT(*) as count FROM posts WHERE status = 'published'");
    $total = $countStmt->fetch()['count'];

    // Get pagination
    $pagination = getPagination($total, POSTS_PER_PAGE, $page);

    // Get posts
    $postsStmt = $db->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug, a.full_name as author_name, a.profile_image as author_image,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN admins a ON p.author_id = a.id
        WHERE p.status = 'published'
        ORDER BY p.published_at DESC
        LIMIT :offset, :limit
    ");

    $postsStmt->execute([
        'offset' => $pagination['offset'],
        'limit' => POSTS_PER_PAGE
    ]);
    $posts = $postsStmt->fetchAll();

    // Get categories for sidebar
    $categoriesStmt = $db->query("
        SELECT c.*, COUNT(p.id) as post_count
        FROM categories c
        LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
        GROUP BY c.id
        HAVING post_count > 0
        ORDER BY c.name ASC
    ");
    $categories = $categoriesStmt->fetchAll();

    // Get recent posts for sidebar
    $recentStmt = $db->query("
        SELECT p.*, c.name as category_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published'
        ORDER BY p.published_at DESC
        LIMIT 5
    ");
    $recentPosts = $recentStmt->fetchAll();

} catch (PDOException $e) {
    $error = 'Error loading posts';
    $posts = [];
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1>Welcome to <?php echo SITE_NAME; ?></h1>
        <p class="lead">Discover amazing stories, tutorials, and insights</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Featured Post -->
            <?php if ($featuredPost && $page == 1): ?>
                <div class="featured-post">
                    <?php if ($featuredPost['featured_image']): ?>
                        <img src="<?php echo UPLOAD_URL . sanitize($featuredPost['featured_image']); ?>"
                             alt="<?php echo sanitize($featuredPost['title']); ?>"
                             class="featured-post-img">
                    <?php else: ?>
                        <div class="featured-post-img bg-primary d-flex align-items-center justify-content-center">
                            <i class="bi bi-image text-white" style="font-size: 5rem;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="featured-post-overlay">
                        <a href="category.php?slug=<?php echo $featuredPost['category_slug']; ?>"
                           class="category-badge mb-2">
                            <?php echo sanitize($featuredPost['category_name'] ?? 'Uncategorized'); ?>
                        </a>
                        <h2><?php echo sanitize($featuredPost['title']); ?></h2>
                        <p><?php echo sanitize(truncate($featuredPost['excerpt'], 150)); ?></p>
                        <div class="post-meta">
                            <span class="post-meta-item">
                                <i class="bi bi-calendar3"></i>
                                <?php echo formatDate($featuredPost['published_at']); ?>
                            </span>
                            <span class="post-meta-item">
                                <i class="bi bi-eye"></i>
                                <?php echo formatNumber($featuredPost['views']); ?> views
                            </span>
                        </div>
                        <a href="post.php?slug=<?php echo $featuredPost['slug']; ?>"
                           class="btn btn-light mt-3">
                            Read More <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Posts Grid -->
            <div class="row">
                <?php if (empty($posts)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>No posts available yet.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6 mb-4">
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
                                    <a href="category.php?slug=<?php echo $post['category_slug']; ?>"
                                       class="category-badge mb-2">
                                        <?php echo sanitize($post['category_name'] ?? 'Uncategorized'); ?>
                                    </a>

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
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Categories Widget -->
            <?php if (!empty($categories)): ?>
                <div class="sidebar-widget">
                    <h5><i class="bi bi-folder me-2"></i>Categories</h5>
                    <?php foreach ($categories as $category): ?>
                        <div class="category-list-item">
                            <a href="category.php?slug=<?php echo $category['slug']; ?>">
                                <?php echo sanitize($category['name']); ?>
                            </a>
                            <span class="category-count"><?php echo $category['post_count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Recent Posts Widget -->
            <?php if (!empty($recentPosts)): ?>
                <div class="sidebar-widget">
                    <h5><i class="bi bi-clock-history me-2"></i>Recent Posts</h5>
                    <?php foreach ($recentPosts as $recent): ?>
                        <div class="recent-post-item">
                            <?php if ($recent['featured_image']): ?>
                                <img src="<?php echo UPLOAD_URL . sanitize($recent['featured_image']); ?>"
                                     alt="<?php echo sanitize($recent['title']); ?>"
                                     class="recent-post-img">
                            <?php else: ?>
                                <div class="recent-post-img bg-secondary"></div>
                            <?php endif; ?>
                            <div class="recent-post-info">
                                <h6>
                                    <a href="post.php?slug=<?php echo $recent['slug']; ?>">
                                        <?php echo sanitize(truncate($recent['title'], 50)); ?>
                                    </a>
                                </h6>
                                <small class="recent-post-date">
                                    <?php echo formatDate($recent['published_at'], 'M d, Y'); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
