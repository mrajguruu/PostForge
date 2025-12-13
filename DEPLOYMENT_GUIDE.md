# 🚀 Complete Deployment Guide for Beginners

## 📋 Table of Contents
1. [Final Project Status](#final-project-status)
2. [How to Push to GitHub](#how-to-push-to-github)
3. [How to Switch to Production](#how-to-switch-to-production)
4. [Deploying to a Live Server](#deploying-to-a-live-server)
5. [Troubleshooting](#troubleshooting)

---

## ✅ Final Project Status

Your **PostForge** project is now **100% READY** for GitHub! Here's what we've done:

### Security Improvements ✅
- ✅ Environment-based configuration (development/production)
- ✅ Error reporting disabled in production mode
- ✅ Environment variables support (.env file)
- ✅ Rate limiting for login (5 attempts per 15 minutes)
- ✅ Brute force attack protection
- ✅ Demo credentials hidden in production
- ✅ Secure cookie settings
- ✅ Apache security headers (.htaccess)
- ✅ SECURITY.md documentation

### Files Added ✅
- ✅ `.env.example` - Environment configuration template
- ✅ `.htaccess` - Apache security and performance rules
- ✅ `SECURITY.md` - Security policy and best practices
- ✅ `logs/` directory - For error logging
- ✅ Updated `.gitignore` - Protects sensitive files

### What Gets Uploaded to GitHub ✅
✅ Source code (PHP files)
✅ Documentation (README, CHANGELOG, LICENSE, SECURITY)
✅ Configuration templates (.env.example)
✅ Database schema (sql/database.sql)
✅ Directory structure (.gitkeep files)

### What Stays Private ❌
❌ Uploaded images (uploads/posts/*, uploads/profiles/*)
❌ Environment variables (.env file)
❌ Error logs (logs/*.log)
❌ IDE settings (.claude/, .vscode/, .idea/)

---

## 📤 How to Push to GitHub

### Step 1: Create a GitHub Account (If you don't have one)

1. Go to https://github.com
2. Click "Sign up"
3. Follow the registration process
4. Verify your email

### Step 2: Install Git (If not installed)

**Windows:**
1. Download from https://git-scm.com/download/win
2. Run the installer
3. Use default settings
4. Restart your terminal

**Verify installation:**
```bash
git --version
```

### Step 3: Configure Git (First Time Only)

Open your terminal/command prompt and run:

```bash
# Set your name (replace with your actual name)
git config --global user.name "Your Name"

# Set your email (use your GitHub email)
git config --global user.email "youremail@example.com"

# Verify configuration
git config --list
```

### Step 4: Initialize Git Repository

Navigate to your project folder and run these commands **ONE BY ONE**:

```bash
# Navigate to project directory
cd P:\Xampp\htdocs\PostForge

# Initialize Git repository
git init

# Check what files will be tracked
git status
```

You should see a list of files. The uploaded images should NOT appear (they're in .gitignore).

### Step 5: Stage Your Files

```bash
# Add all files to staging area
git add .

# Check what's staged
git status
```

You should see files in green color. These will be committed.

### Step 6: Create Your First Commit

```bash
# Create the initial commit
git commit -m "Initial commit: PostForge blog management system v1.1.0"
```

### Step 7: Create a GitHub Repository

1. Go to https://github.com
2. Click the **"+"** icon (top right) → **"New repository"**
3. Fill in:
   - **Repository name:** `PostForge` or `postforge-blog-cms`
   - **Description:** "A modern, secure blog management system built with PHP and MySQL"
   - **Visibility:** Choose **Public** or **Private**
   - **DO NOT** check "Initialize with README" (we already have one)
   - **License:** Choose "MIT License" (we already have one, so skip)
4. Click **"Create repository"**

### Step 8: Connect Local Repository to GitHub

GitHub will show you commands. Use these (replace `yourusername` with your GitHub username):

```bash
# Add remote repository
git remote add origin https://github.com/yourusername/PostForge.git

# Verify remote
git remote -v

# Rename branch to main (if needed)
git branch -M main

# Push to GitHub
git push -u origin main
```

**If GitHub asks for credentials:**
- Username: Your GitHub username
- Password: Use a **Personal Access Token** (not your GitHub password)

**To create a Personal Access Token:**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token → Select "repo" scope
3. Copy the token and use it as password

### Step 9: Verify Upload

1. Go to your GitHub repository page
2. You should see all your files
3. Check that `.env` file is **NOT** there (good!)
4. Check that upload images are **NOT** there (good!)

---

## 🔄 How to Switch to Production

When you're ready to deploy your blog to a live server:

### Option 1: Using .env File (Recommended)

1. **On your production server**, create a `.env` file:

```bash
# Create .env file
cp .env.example .env

# Edit the file
nano .env  # or use any text editor
```

2. **Update the values** in `.env`:

```env
# IMPORTANT: Set to production!
APP_ENV=production

# Your live website URL
SITE_NAME=My Awesome Blog
SITE_URL=https://yourdomain.com
ADMIN_EMAIL=admin@yourdomain.com

# Database credentials
DB_HOST=localhost
DB_NAME=blog_management
DB_USER=blog_user
DB_PASS=YOUR_STRONG_PASSWORD_HERE

# Match your server timezone
TIMEZONE=UTC
```

3. **Save the file** and ensure it's NOT uploaded to Git:

```bash
# Verify .env is ignored
git status

# If .env appears, add it to .gitignore
echo ".env" >> .gitignore
```

### Option 2: Direct Configuration Edit

If you can't use .env files, edit `config/config.php` directly:

Change this line:
```php
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');
```

To:
```php
define('ENVIRONMENT', 'production');  // Force production mode
```

**⚠️ WARNING:** If you edit `config.php` directly, DO NOT commit these changes to Git!

---

## 🌐 Deploying to a Live Server

### Step 1: Choose a Hosting Provider

**Recommended options:**
- **Shared Hosting:** Hostinger, Bluehost, SiteGround, Namecheap
- **VPS:** DigitalOcean, Linode, Vultr
- **Free (for testing):** InfinityFree, 000webhost

**Requirements:**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with .htaccess support

### Step 2: Upload Your Files

**Method 1: Using Git (Best)**
```bash
# SSH into your server
ssh username@yourserver.com

# Navigate to web directory
cd /var/www/html  # or public_html

# Clone your repository
git clone https://github.com/yourusername/PostForge.git

# Navigate to project
cd PostForge
```

**Method 2: Using FTP/SFTP**
1. Download FileZilla (https://filezilla-project.org)
2. Connect to your server using credentials from hosting provider
3. Upload all files from `PostForge` folder to `public_html` or `www` directory

### Step 3: Create Database

**Using cPanel:**
1. Login to cPanel
2. Go to **MySQL Databases**
3. Create new database: `blog_management`
4. Create new user with strong password
5. Add user to database with ALL privileges

**Using phpMyAdmin:**
1. Open phpMyAdmin
2. Click "New" to create database
3. Name: `blog_management`
4. Collation: `utf8mb4_unicode_ci`
5. Import `sql/database.sql` file

**Using command line:**
```bash
mysql -u root -p

CREATE DATABASE blog_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'blog_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON blog_management.* TO 'blog_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u blog_user -p blog_management < sql/database.sql
```

### Step 4: Configure Environment

Create `.env` file on server:

```bash
cp .env.example .env
nano .env
```

Update with production values:
```env
APP_ENV=production
SITE_URL=https://yourdomain.com
DB_HOST=localhost
DB_NAME=blog_management
DB_USER=blog_user
DB_PASS=your_database_password
```

### Step 5: Set File Permissions

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make uploads and logs writable
chmod 755 uploads uploads/posts uploads/profiles logs
```

### Step 6: Enable HTTPS (SSL Certificate)

**Using Let's Encrypt (Free):**

```bash
# Install certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

**Or use cPanel:**
1. cPanel → SSL/TLS Status
2. Click "Run AutoSSL"

### Step 7: Enable HTTPS Redirect

Edit `.htaccess` and uncomment these lines:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### Step 8: Test Your Website

1. Visit: `https://yourdomain.com/public/index.php`
2. Admin panel: `https://yourdomain.com/admin/login.php`
3. Login with: `admin@blog.com` / `admin123`
4. **IMMEDIATELY** change the admin password!

### Step 9: Post-Deployment Checklist

- [ ] Website loads correctly
- [ ] HTTPS is working (green padlock)
- [ ] Admin login works
- [ ] Can create/edit posts
- [ ] Images upload successfully
- [ ] Comments work
- [ ] Changed default admin password
- [ ] Error display is OFF (no errors visible to users)
- [ ] Logs directory is writable
- [ ] Database connection works

---

## 🔐 Security Checklist (Production)

Before launching:

- [ ] `APP_ENV=production` in `.env`
- [ ] Strong database password set
- [ ] Default admin password changed
- [ ] HTTPS enabled
- [ ] `.env` file NOT in Git repository
- [ ] File permissions set correctly (755/644)
- [ ] Error logging enabled, display disabled
- [ ] Backup system configured
- [ ] `.htaccess` security rules active

---

## 🛠️ Troubleshooting

### Problem: "Database connection failed"
**Solution:**
1. Check database credentials in `.env`
2. Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`
3. Test connection: `mysql -u blog_user -p blog_management`

### Problem: "Blank white page"
**Solution:**
1. Check PHP error logs: `tail -f logs/php-errors.log`
2. Enable error display temporarily in `config/config.php`
3. Check Apache error log: `tail -f /var/log/apache2/error.log`

### Problem: "Permission denied" when uploading images
**Solution:**
```bash
chmod 755 uploads uploads/posts uploads/profiles
chown www-data:www-data uploads -R  # Linux
```

### Problem: "Can't write to logs directory"
**Solution:**
```bash
chmod 755 logs
chown www-data:www-data logs  # Linux
```

### Problem: "404 Not Found" on admin pages
**Solution:**
1. Check .htaccess is uploaded
2. Verify Apache mod_rewrite is enabled: `sudo a2enmod rewrite`
3. Restart Apache: `sudo service apache2 restart`

### Problem: ".env file not working"
**Solution:**
1. Verify `.env` exists: `ls -la .env`
2. Check file permissions: `chmod 644 .env`
3. Alternative: Edit `config/config.php` directly

---

## 📊 Development vs Production

### Development Mode (Current)
- Shows all errors on screen
- Demo credentials visible on login page
- Detailed error messages
- No HTTPS required

### Production Mode
- Errors hidden from users
- Errors logged to file
- Demo credentials hidden
- HTTPS recommended
- Optimized for performance

---

## 🔄 Making Changes After Initial Push

### Updating Your Code

```bash
# Check what files changed
git status

# Add all changes
git add .

# Commit changes
git commit -m "Description of what you changed"

# Push to GitHub
git push origin main
```

### Deploying Updates to Production

**Using Git:**
```bash
# SSH to server
ssh username@yourserver.com
cd /var/www/html/PostForge

# Pull latest changes
git pull origin main
```

**Using FTP:**
1. Upload only the changed files
2. Don't overwrite `.env` or uploaded images

---

## 📞 Need Help?

1. **Check the logs:**
   - `logs/php-errors.log` - PHP errors
   - Apache error log - Server errors

2. **Read the docs:**
   - `README.md` - Installation and features
   - `SECURITY.md` - Security best practices
   - This file - Deployment guide

3. **Common resources:**
   - PHP Documentation: https://php.net/docs.php
   - MySQL Documentation: https://dev.mysql.com/doc/
   - Apache Documentation: https://httpd.apache.org/docs/

---

## 🎉 Congratulations!

You now know how to:
- ✅ Push your code to GitHub
- ✅ Switch between development and production
- ✅ Deploy to a live server
- ✅ Secure your application
- ✅ Troubleshoot common issues

**Your PostForge blog is production-ready!** 🚀

---

**Last Updated:** 2025-12-13
**Version:** 1.1.0
**Author:** PostForge Team
