<div class="col-md-3 col-lg-2 sidebar bg-light p-3">
    <div class="mb-4">
        <h6 class="text-muted text-uppercase mb-3">Quick Stats</h6>
        <?php
        try {
            $db = getDB();

            // Get total posts
            $postsStmt = $db->query("SELECT COUNT(*) as count FROM posts");
            $totalPosts = $postsStmt->fetch()['count'];

            // Get total categories
            $catsStmt = $db->query("SELECT COUNT(*) as count FROM categories");
            $totalCategories = $catsStmt->fetch()['count'];

            // Get pending comments
            $commentsStmt = $db->query("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'");
            $pendingComments = $commentsStmt->fetch()['count'];

            // Get total admins
            $adminsStmt = $db->query("SELECT COUNT(*) as count FROM admins");
            $totalAdmins = $adminsStmt->fetch()['count'];
        ?>
        <div class="quick-stat mb-2">
            <i class="bi bi-file-text text-primary me-2"></i>
            <span class="text-muted">Posts:</span>
            <strong><?php echo $totalPosts; ?></strong>
        </div>
        <div class="quick-stat mb-2">
            <i class="bi bi-folder text-success me-2"></i>
            <span class="text-muted">Categories:</span>
            <strong><?php echo $totalCategories; ?></strong>
        </div>
        <div class="quick-stat mb-2">
            <i class="bi bi-chat-dots text-warning me-2"></i>
            <span class="text-muted">Pending:</span>
            <strong><?php echo $pendingComments; ?></strong>
        </div>
        <div class="quick-stat mb-2">
            <i class="bi bi-people text-info me-2"></i>
            <span class="text-muted">Admins:</span>
            <strong><?php echo $totalAdmins; ?></strong>
        </div>
        <?php } catch (PDOException $e) { ?>
            <p class="text-danger small">Error loading stats</p>
        <?php } ?>
    </div>

    <hr>

    <div class="mb-4">
        <h6 class="text-muted text-uppercase mb-3">Quick Actions</h6>
        <a href="post-form.php" class="btn btn-primary btn-sm w-100 mb-2">
            <i class="bi bi-plus-circle me-1"></i>New Post
        </a>
        <a href="categories.php" class="btn btn-success btn-sm w-100 mb-2">
            <i class="bi bi-folder-plus me-1"></i>Add Category
        </a>
        <a href="settings.php" class="btn btn-secondary btn-sm w-100 mb-2">
            <i class="bi bi-gear me-1"></i>Settings
        </a>
        <a href="../public/index.php" target="_blank" class="btn btn-info btn-sm w-100">
            <i class="bi bi-eye me-1"></i>View Site
        </a>
    </div>
</div>
