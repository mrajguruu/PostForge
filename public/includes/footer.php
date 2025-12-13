    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="bi bi-journal-text me-2"></i><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">A modern blog management system built with PHP and MySQL.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <?php
                        try {
                            $db = getDB();
                            $footerCatsStmt = $db->query("SELECT * FROM categories ORDER BY name ASC LIMIT 3");
                            $footerCategories = $footerCatsStmt->fetchAll();

                            foreach ($footerCategories as $footerCat):
                        ?>
                            <li>
                                <a href="category.php?slug=<?php echo $footerCat['slug']; ?>"
                                   class="text-muted text-decoration-none">
                                    <?php echo sanitize($footerCat['name']); ?>
                                </a>
                            </li>
                        <?php
                            endforeach;
                        } catch (PDOException $e) {
                            // Silent fail
                        }
                        ?>
                        <li><a href="../admin/login.php" class="text-muted text-decoration-none">Admin Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h6>Connect</h6>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                <small>Built with PHP, MySQL & Bootstrap</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/public.js"></script>
</body>
</html>
