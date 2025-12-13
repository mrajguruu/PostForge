# Changelog

All notable changes to PostForge will be documented in this file.

## [1.1.0] - 2025-12-13

### Added
- **Profile Management System**
  - Profile picture upload with WhatsApp-style interface
  - Camera icon overlay for intuitive photo selection
  - Image preview modal before upload
  - Circular profile picture display (150px on settings, 32px on header, 100px on public posts)
  - Gradient placeholder with initials when no profile picture is set
  - Profile pictures visible across all pages (admin header, public posts, author section)

- **Admin Settings Page**
  - Comprehensive settings interface at `/admin/settings.php`
  - Profile picture management
  - Password change functionality with validation
  - Account information display (username, email, full name)
  - Account creation and last login timestamps
  - Total admin users count

- **Enhanced Dashboard**
  - Total admin users count in dashboard header
  - Admin count in sidebar quick stats
  - Settings button in sidebar quick actions

- **Time Display Improvements**
  - Intelligent "time ago" progression:
    - Just now (0-10 seconds)
    - X seconds ago (10-59 seconds)
    - X minutes ago (1-59 minutes)
    - X hours ago (1-23 hours)
    - X days ago (1-6 days)
    - X weeks ago (1-4 weeks)
    - X months ago (1-11 months)
    - X years ago (1-4 years)
    - Full date display for 5+ years old content
  - Proper singular/plural handling ("1 minute ago" vs "5 minutes ago")

- **Timezone Configuration**
  - Configurable timezone support in `config/config.php`
  - Automatic synchronization with MySQL server timezone
  - Global compatibility with any timezone
  - Accurate relative time calculations
  - Documentation with common timezone examples

- **Profile Upload Directory**
  - Created `uploads/profiles/` for admin profile pictures
  - Added to `.gitignore` with `.gitkeep` file
  - Proper path constants in config

### Fixed
- **Authentication Issues**
  - Fixed incorrect password hash in `sql/database.sql`
  - Admin login now works with credentials: admin@blog.com / admin123
  - Password hash properly uses bcrypt algorithm

- **Post Deletion**
  - Added delete functionality to `admin/post-form.php`
  - Delete now works from both posts list and edit page
  - Properly deletes associated images

- **Timezone Mismatch**
  - Fixed PHP and MySQL timezone synchronization
  - Resolved "Just now" showing for all comments regardless of age
  - Comments now display accurate relative time

### Changed
- Updated README.md with comprehensive documentation:
  - Added timezone configuration section
  - Added profile management features
  - Added time display features
  - Added common issues troubleshooting
  - Updated project structure with new files
  - Added installation steps for upload directories

- Enhanced `.gitignore`:
  - Added `uploads/profiles/*` exclusion
  - Added `.gitkeep` tracking for profile directory

### Technical Details
- **Files Modified:**
  - `config/config.php` - Added timezone setting and profile upload paths
  - `includes/functions.php` - Improved timeAgo() function
  - `admin/settings.php` - Created new settings page
  - `admin/post-form.php` - Added delete handler
  - `admin/dashboard.php` - Added admin count
  - `admin/includes/header.php` - Added profile picture display
  - `admin/includes/sidebar.php` - Added admin count and settings button
  - `public/index.php` - Added author profile image to query
  - `public/post.php` - Added author profile picture display
  - `sql/database.sql` - Fixed admin password hash
  - `README.md` - Comprehensive documentation update
  - `.gitignore` - Added profile uploads exclusion

- **New Files:**
  - `admin/settings.php` - Admin settings and profile management
  - `uploads/profiles/.gitkeep` - Git tracking for profiles directory
  - `CHANGELOG.md` - This file

### Security
- All profile uploads validated for file type and size
- Profile images stored with random filenames to prevent overwriting
- MIME type checking using finfo
- Maximum file size: 2MB
- Allowed types: JPEG, PNG, GIF

## [1.0.0] - 2024-12-01

### Initial Release
- Complete blog management system
- Admin authentication
- Post CRUD operations
- Category management
- Comment moderation
- Public frontend
- Responsive design with Bootstrap 5
- Security features (CSRF, XSS, SQL injection prevention)
