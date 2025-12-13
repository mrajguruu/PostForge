# Security Policy

## Supported Versions

Currently supported versions with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.1.x   | :white_check_mark: |
| 1.0.x   | :x:                |

## Security Features

PostForge implements multiple layers of security:

### Authentication & Session Management
- ✅ Bcrypt password hashing (PASSWORD_BCRYPT)
- ✅ Session fixation prevention (session_regenerate_id)
- ✅ Secure session cookies (httponly, samesite=Strict)
- ✅ Session timeout (1 hour default)
- ✅ Brute force protection (rate limiting: 5 attempts per 15 minutes)
- ✅ Secure remember-me cookies

### Input Validation & Output Encoding
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (htmlspecialchars with ENT_QUOTES)
- ✅ CSRF token validation (timing-safe comparison)
- ✅ Email validation (FILTER_VALIDATE_EMAIL)
- ✅ File upload validation (MIME type, size, extension)

### File Upload Security
- ✅ MIME type verification using finfo
- ✅ File size limits (2MB default)
- ✅ Extension whitelist (jpg, jpeg, png, gif)
- ✅ Random filename generation
- ✅ Uploaded files stored outside web root (recommended)

### Database Security
- ✅ Parameterized queries (PDO)
- ✅ Foreign key constraints
- ✅ UTF-8 encoding (utf8mb4_unicode_ci)
- ✅ Connection error handling

### HTTP Security Headers
- ✅ X-Content-Type-Options: nosniff
- ✅ X-XSS-Protection: 1; mode=block
- ✅ X-Frame-Options: SAMEORIGIN
- ✅ Referrer-Policy: strict-origin-when-cross-origin

### Error Handling
- ✅ Production mode hides errors from users
- ✅ Errors logged to file (logs/php-errors.log)
- ✅ Generic error messages for users
- ✅ Detailed logging for administrators

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability, please follow these steps:

### 1. **DO NOT** Open a Public Issue
Security vulnerabilities should not be disclosed publicly until a fix is available.

### 2. Report Privately
Email security details to: **security@postforge.com** (or your configured ADMIN_EMAIL)

Include in your report:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if available)

### 3. Response Timeline
- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Timeline**: Depends on severity
  - Critical: 24-48 hours
  - High: 7 days
  - Medium: 30 days
  - Low: 90 days

### 4. Disclosure Policy
- We will acknowledge your contribution in the CHANGELOG
- Public disclosure only after patch is released
- We may request a CVE if appropriate

## Security Best Practices for Deployment

### Before Going Live

1. **Environment Configuration**
   ```bash
   # Set production environment
   APP_ENV=production
   ```

2. **Database Security**
   - Use a dedicated database user (not root)
   - Set a strong, unique password
   - Grant only necessary privileges
   - Restrict database access to localhost

3. **File Permissions**
   ```bash
   # Directories: 755
   find . -type d -exec chmod 755 {} \;

   # Files: 644
   find . -type f -exec chmod 644 {} \;

   # Writable directories: 755
   chmod 755 uploads/ logs/
   ```

4. **Enable HTTPS**
   - Use Let's Encrypt or commercial SSL certificate
   - Redirect HTTP to HTTPS (uncomment in .htaccess)
   - Use HSTS headers for enhanced security

5. **Secure Configuration Files**
   - Never commit `.env` to version control
   - Restrict access to config directory
   - Use environment variables for sensitive data

6. **Regular Updates**
   - Keep PHP updated (7.4+ recommended)
   - Update MySQL/MariaDB regularly
   - Monitor dependencies for vulnerabilities

7. **Monitoring & Logging**
   - Monitor logs/php-errors.log regularly
   - Set up intrusion detection (fail2ban, etc.)
   - Enable server-side logging
   - Review failed login attempts

8. **Backup Strategy**
   - Automated daily database backups
   - Backup uploads directory
   - Store backups securely off-site
   - Test restore procedures

### Production Hardening Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Strong database password configured
- [ ] Default admin password changed
- [ ] HTTPS enabled with valid SSL certificate
- [ ] File permissions correctly set (755/644)
- [ ] Error display disabled (`display_errors=0`)
- [ ] Error logging enabled to logs/
- [ ] `.htaccess` security rules active
- [ ] Database user has minimal privileges
- [ ] Backup system configured
- [ ] Monitoring/alerting set up
- [ ] Security headers verified
- [ ] Rate limiting tested
- [ ] File upload restrictions tested

## Known Security Considerations

### Rate Limiting
- Current implementation uses session-based rate limiting
- For production, consider Redis/Memcached for distributed systems
- IP-based rate limiting recommended for enhanced protection

### Password Policy
- Minimum 6 characters (increase to 8+ for production)
- Consider adding complexity requirements
- Implement password expiration for high-security environments

### File Uploads
- Current limit: 2MB
- Supported formats: JPG, PNG, GIF
- Consider antivirus scanning for production
- Store uploads outside document root if possible

### Session Storage
- Default: File-based sessions
- For production: Consider database or Redis sessions
- Ensure session directory has proper permissions

## Security Updates

Subscribe to security updates:
1. Watch this repository on GitHub
2. Check CHANGELOG.md for security fixes
3. Subscribe to security mailing list (if available)

## Attribution

We appreciate security researchers who help improve PostForge's security. Acknowledgments are listed in CHANGELOG.md for each release.

---

**Last Updated:** 2025-12-13
**Version:** 1.1.0
