<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $metaDescription ?? 'A modern blog powered by PHP and MySQL'; ?>">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/public.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-journal-text me-2 text-primary"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                           href="index.php">Home</a>
                    </li>
                    <?php
                    // Get categories for navigation
                    try {
                        $db = getDB();
                        $navCatsStmt = $db->query("SELECT * FROM categories ORDER BY name ASC LIMIT 5");
                        $navCategories = $navCatsStmt->fetchAll();

                        if (!empty($navCategories)):
                    ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button"
                               data-bs-toggle="dropdown">
                                Categories
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($navCategories as $navCat): ?>
                                    <li>
                                        <a class="dropdown-item" href="category.php?slug=<?php echo $navCat['slug']; ?>">
                                            <?php echo sanitize($navCat['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php
                        endif;
                    } catch (PDOException $e) {
                        // Silent fail for navigation
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/login.php">
                            <i class="bi bi-lock"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
