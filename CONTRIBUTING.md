# Contributing to PostForge

First off, thank you for considering contributing to PostForge! 🎉

This document provides guidelines for contributing to this project. Following these guidelines helps communicate that you respect the time of the developers managing and developing this open source project.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)

---

## 📜 Code of Conduct

This project adheres to a Code of Conduct that all contributors are expected to follow. Please be respectful and professional in all interactions.

### Our Standards

- **Be Respectful:** Treat everyone with respect and kindness
- **Be Collaborative:** Work together towards common goals
- **Be Professional:** Keep discussions focused and constructive
- **Be Inclusive:** Welcome and support people of all backgrounds

---

## 🤝 How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates.

**Good Bug Report Includes:**
- Clear descriptive title
- Steps to reproduce the issue
- Expected behavior vs actual behavior
- Screenshots (if applicable)
- Environment details (PHP version, OS, etc.)

**Example:**
```
Title: Login fails with blank page on PHP 8.0

Steps to Reproduce:
1. Navigate to /admin/login.php
2. Enter valid credentials
3. Click login button

Expected: Redirect to dashboard
Actual: Blank white page

Environment: PHP 8.0, MySQL 8.0, Windows 10
```

### Suggesting Enhancements

Enhancement suggestions are welcome! Please provide:
- Clear use case
- Expected behavior
- Benefits to users
- Possible implementation approach

### Code Contributions

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/AmazingFeature`)
3. **Make your changes** following coding standards
4. **Test thoroughly**
5. **Commit with clear messages**
6. **Push to your branch**
7. **Open a Pull Request**

---

## 🛠️ Development Setup

### Prerequisites

- PHP >= 7.4
- MySQL >= 5.7 or MariaDB >= 10.3
- Apache with mod_rewrite
- Git

### Local Setup

```bash
# Clone your fork
git clone https://github.com/your-username/PostForge.git
cd PostForge

# Create database
mysql -u root -p -e "CREATE DATABASE blog_management"

# Import schema
mysql -u root -p blog_management < sql/database.sql

# Copy to web server directory
cp -r . /path/to/htdocs/PostForge

# Access at http://localhost/PostForge/public/index.php
```

### Testing

Before submitting, please test:
- All CRUD operations work
- Security features function correctly
- No PHP errors/warnings
- Responsive design on mobile
- Browser compatibility (Chrome, Firefox, Safari)

---

## 💻 Coding Standards

### PHP Standards

Follow PSR-12 coding standards:

```php
// Good
class UserController
{
    public function index()
    {
        $users = $this->getUsers();
        return view('users.index', compact('users'));
    }
}

// Use meaningful variable names
$adminEmail = 'admin@example.com';  // Good
$ae = 'admin@example.com';          // Bad

// Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
```

### Security Requirements

- **Always use PDO prepared statements** (never string concatenation)
- **Sanitize all output** with `htmlspecialchars()`
- **Validate all input** before processing
- **Never store plain text passwords** (use `password_hash()`)
- **Use CSRF tokens** on all state-changing forms
- **Validate file uploads** (MIME type, size, extension)

### Commenting

```php
// Bad - obvious comment
$total = $price * $quantity; // Calculate total

// Good - explains WHY
// Apply early bird discount if booking made 30+ days in advance
if ($daysUntilEvent > 30) {
    $price *= 0.8;
}

// Excellent - documents complex logic
/**
 * Calculate time difference with timezone awareness
 *
 * Handles edge cases:
 * - Negative differences (clock skew)
 * - Very old timestamps (5+ years)
 * - Timezone differences between PHP and MySQL
 *
 * @param string $datetime MySQL datetime string
 * @return string Human-readable time difference
 */
function timeAgo($datetime) {
    // Implementation
}
```

### Database

- Use migrations (add to `sql/` directory)
- Follow naming conventions:
  - Tables: plural lowercase (`posts`, `categories`)
  - Columns: snake_case (`created_at`, `user_id`)
  - Foreign keys: `table_id` (e.g., `author_id`)
- Add indexes for frequently queried columns
- Use foreign key constraints

---

## 📝 Commit Guidelines

### Commit Message Format

```
<type>: <subject>

<body (optional)>

<footer (optional)>
```

### Types

- **feat:** New feature
- **fix:** Bug fix
- **docs:** Documentation changes
- **style:** Code style changes (formatting, no logic change)
- **refactor:** Code refactoring
- **perf:** Performance improvements
- **test:** Adding or updating tests
- **chore:** Build process or auxiliary tool changes

### Examples

```bash
# Good
feat: Add rate limiting to login system

Implements session-based rate limiting with 5 attempt threshold
and 15-minute lockout period to prevent brute force attacks.

Closes #42

# Good
fix: Correct timezone synchronization in timeAgo function

The timeAgo function was not accounting for MySQL timezone
differences, causing incorrect relative times.

# Bad
git commit -m "fixed stuff"
git commit -m "updates"
```

---

## 🔄 Pull Request Process

### Before Submitting

- [ ] Code follows PSR-12 standards
- [ ] All security checks pass
- [ ] No PHP warnings/errors
- [ ] Tested on PHP 7.4 and 8.0+
- [ ] Updated documentation if needed
- [ ] Added comments for complex logic
- [ ] No debugging code left behind (`var_dump`, `die`, `console.log`)

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
How to test these changes:
1. Step 1
2. Step 2
3. Step 3

## Screenshots (if applicable)
Attach screenshots showing the changes

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Commented complex code
- [ ] Documentation updated
- [ ] No new warnings
- [ ] Changes tested locally
```

### Review Process

1. **Automated checks** run on submission
2. **Code review** by maintainer
3. **Requested changes** (if any) must be addressed
4. **Approval** and merge

**Review Timeline:**
- Initial response: Within 3-5 days
- Full review: Within 1-2 weeks
- Emergency fixes: Within 24-48 hours

---

## 🎯 Priority Areas

We especially welcome contributions in these areas:

### High Priority
- [ ] Unit tests (PHPUnit)
- [ ] API endpoints for mobile app
- [ ] Multi-language support (i18n)
- [ ] Dark mode theme

### Medium Priority
- [ ] Email notifications
- [ ] Social media sharing
- [ ] Export/import functionality
- [ ] Advanced search with filters

### Nice to Have
- [ ] Markdown support in posts
- [ ] Code syntax highlighting
- [ ] Post scheduling
- [ ] Draft autosave

---

## ❓ Questions?

- **General Questions:** Open a GitHub Discussion
- **Bug Reports:** Create a GitHub Issue
- **Security Issues:** Email security@postforge.com (or configured ADMIN_EMAIL)
- **Feature Requests:** Create a GitHub Issue with [FEATURE] tag

---

## 🏆 Recognition

Contributors will be:
- Listed in CHANGELOG.md for each release
- Mentioned in release notes
- Added to contributors list (once we have 5+ contributors)

---

## 📚 Resources

- [PSR-12 Coding Standards](https://www.php-fig.org/psr/psr-12/)
- [OWASP Security Guidelines](https://owasp.org/www-project-top-ten/)
- [PHP Best Practices](https://phptherightway.com/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

---

**Thank you for contributing to PostForge!** 🎉

Your contributions help make this project better for everyone.

---

*Last Updated: 2025-12-13*
