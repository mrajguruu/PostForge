<?php
/**
 * Admin Settings Page
 * Manage profile, password, and account settings
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$pageTitle = 'Settings';
$success = '';
$errors = [];

// Get current admin data
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = :id");
    $stmt->execute(['id' => getCurrentAdminId()]);
    $admin = $stmt->fetch();

    if (!$admin) {
        redirect('login.php', 'Session expired', 'error');
    }
} catch (PDOException $e) {
    redirect('dashboard.php', 'Error loading settings', 'error');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $currentImage = $_POST['current_image'] ?? '';

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    // Handle profile image upload
    $imageName = $currentImage;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/profiles/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadResult = uploadImage($_FILES['profile_image'], $uploadDir);

        if ($uploadResult['success']) {
            // Delete old image if exists
            if ($currentImage) {
                $oldPath = $uploadDir . $currentImage;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $imageName = $uploadResult['filename'];
        } else {
            $errors = array_merge($errors, $uploadResult['errors']);
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE admins SET
                    username = :username,
                    email = :email,
                    full_name = :full_name,
                    profile_image = :profile_image
                WHERE id = :id
            ");

            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'full_name' => $fullName,
                'profile_image' => $imageName,
                'id' => getCurrentAdminId()
            ]);

            // Update session
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_name'] = $fullName;

            $success = 'Profile updated successfully!';

            // Refresh admin data
            $stmt = $db->prepare("SELECT * FROM admins WHERE id = :id");
            $stmt->execute(['id' => getCurrentAdminId()]);
            $admin = $stmt->fetch();

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'Username or email already exists';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($currentPassword)) {
        $errors[] = 'Current password is required';
    } elseif (!password_verify($currentPassword, $admin['password'])) {
        $errors[] = 'Current password is incorrect';
    }

    if (empty($newPassword)) {
        $errors[] = 'New password is required';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE admins SET password = :password WHERE id = :id");
            $stmt->execute([
                'password' => $hashedPassword,
                'id' => getCurrentAdminId()
            ]);

            $success = 'Password changed successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Failed to update password';
        }
    }
}

// Get total admin users count
try {
    $countStmt = $db->query("SELECT COUNT(*) as count FROM admins");
    $totalAdmins = $countStmt->fetch()['count'];
} catch (PDOException $e) {
    $totalAdmins = 0;
}

include 'includes/header.php';
?>

<style>
.profile-picture-container {
    position: relative;
    display: inline-block;
}

.profile-picture-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.profile-picture-placeholder {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
    font-weight: bold;
    border: 4px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.profile-edit-overlay {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #007bff;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    transition: all 0.3s;
}

.profile-edit-overlay:hover {
    background: #0056b3;
    transform: scale(1.1);
}

.image-preview-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.image-preview-content {
    background: white;
    padding: 30px;
    border-radius: 15px;
    max-width: 500px;
    width: 90%;
    text-align: center;
}

.preview-image-container {
    width: 300px;
    height: 300px;
    margin: 20px auto;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.preview-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.crop-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin: 10px auto;
    border: 3px solid #007bff;
}
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="col-md-9 col-lg-10 main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gear me-2"></i>Settings</h2>
        <span class="badge bg-info">Total Admin Users: <?php echo $totalAdmins; ?></span>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo sanitize($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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

    <div class="row">
        <!-- Profile Settings -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="profile-form">
                        <input type="hidden" name="update_profile" value="1">
                        <input type="hidden" name="current_image" value="<?php echo $admin['profile_image'] ?? ''; ?>">

                        <!-- Profile Picture Section -->
                        <div class="mb-4 text-center">
                            <label class="form-label d-block">Profile Picture</label>
                            <div class="profile-picture-container">
                                <?php if ($admin['profile_image']): ?>
                                    <img src="../uploads/profiles/<?php echo sanitize($admin['profile_image']); ?>"
                                         alt="Profile" class="profile-picture-preview" id="current-profile-pic">
                                <?php else: ?>
                                    <div class="profile-picture-placeholder" id="profile-placeholder">
                                        <?php echo strtoupper(substr($admin['full_name'] ?? $admin['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="profile-edit-overlay" onclick="document.getElementById('profile_image').click()">
                                    <i class="bi bi-camera-fill"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <input type="file" class="d-none" id="profile_image" name="profile_image"
                                       accept="image/jpeg,image/png,image/gif" onchange="previewProfileImage(this)">
                                <small class="text-muted d-block">Click the camera icon to upload a new picture</small>
                                <small class="text-muted">Max size: 2MB • Formats: JPG, PNG, GIF</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?php echo sanitize($admin['username']); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo sanitize($admin['email']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?php echo sanitize($admin['full_name'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Account Created</label>
                            <p class="text-muted mb-1">
                                <i class="bi bi-calendar-check me-2"></i><?php echo formatDate($admin['created_at'], 'F d, Y \a\t h:i A'); ?>
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Last Login</label>
                            <p class="text-muted mb-1">
                                <i class="bi bi-clock-history me-2"></i><?php echo $admin['last_login'] ? formatDate($admin['last_login'], 'F d, Y \a\t h:i A') : 'Never'; ?>
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Change -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="password-form">
                        <input type="hidden" name="change_password" value="1">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="current_password"
                                       name="current_password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="new_password"
                                       name="new_password" required minlength="6">
                            </div>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-check2"></i></span>
                                <input type="password" class="form-control" id="confirm_password"
                                       name="confirm_password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Info Card -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">User ID</small>
                        <strong><?php echo $admin['id']; ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong><?php echo sanitize($admin['email']); ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Role</small>
                        <span class="badge bg-primary">Administrator</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="image-preview-modal" id="imagePreviewModal">
    <div class="image-preview-content">
        <h5 class="mb-3">Preview Your Profile Picture</h5>
        <div class="preview-image-container">
            <img id="previewImage" class="preview-image" alt="Preview">
        </div>
        <div class="crop-preview" id="cropPreview"></div>
        <p class="text-muted small mt-3">This is how your profile picture will look</p>
        <div class="mt-3">
            <button class="btn btn-success" onclick="closePreview(true)">
                <i class="bi bi-check2 me-2"></i>Looks Good
            </button>
            <button class="btn btn-secondary" onclick="closePreview(false)">
                <i class="bi bi-x me-2"></i>Cancel
            </button>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('password-form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
});

// Profile image preview
function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const file = input.files[0];

        // Check file size
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }

        reader.onload = function(e) {
            const modal = document.getElementById('imagePreviewModal');
            const previewImg = document.getElementById('previewImage');
            const cropPreview = document.getElementById('cropPreview');

            previewImg.src = e.target.result;
            cropPreview.style.backgroundImage = `url(${e.target.result})`;
            cropPreview.style.backgroundSize = 'cover';
            cropPreview.style.backgroundPosition = 'center';

            modal.style.display = 'flex';

            // Update current profile pic
            const currentPic = document.getElementById('current-profile-pic');
            const placeholder = document.getElementById('profile-placeholder');

            if (currentPic) {
                currentPic.src = e.target.result;
            } else if (placeholder) {
                placeholder.outerHTML = `<img src="${e.target.result}" alt="Profile" class="profile-picture-preview" id="current-profile-pic">`;
            }
        };

        reader.readAsDataURL(file);
    }
}

function closePreview(confirm) {
    const modal = document.getElementById('imagePreviewModal');
    modal.style.display = 'none';

    if (!confirm) {
        // Reset file input and image if cancelled
        document.getElementById('profile_image').value = '';
        // Restore original image if exists
        location.reload();
    }
}

// Close modal on outside click
document.getElementById('imagePreviewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview(false);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
