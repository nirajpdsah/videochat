# InfinityFree Deployment Guide

## 📁 Files to Upload

### ✅ Upload These Files/Folders
```
/css/                          → /css/
/js/                           → /js/
/api/                          → /api/
/uploads/                      → /uploads/
.htaccess                      → /.htaccess (root)
.env.local                     → /.env.local (root) - MUST BE UPDATED WITH YOUR CREDENTIALS
config.php                     → /config.php
index.php                      → /index.php
dashboard.php                  → /dashboard.php
call.php                       → /call.php
login.php                      → /login.php
logout.php                      → /logout.php
signup.php                     → /signup.php
composer.json                  → /composer.json (optional, if using composer)
```

### ❌ DO NOT Upload These Files
```
.env                           (Local testing only)
.env.example                   (Template file)
.git/                          (Git repository - not needed on server)
.gitignore                     (Git config - not needed)
*.md files                     (Documentation - not needed on production)
database.sql                   (Keep locally for reference, upload separately)
test_*.php                     (Testing files - not needed on production)
debug_*.php                    (Debugging files - remove before production)
procfile                       (Railway deployment only)
railway.json                   (Railway deployment only)
uploads/*                      (Use .htaccess to protect, don't pre-upload user data)
```

---

## 🔧 Step-by-Step Deployment Instructions

### Step 1: Prepare for Upload
1. Open your `.env.local` file locally
2. Update it with your **actual InfinityFree database credentials:**
   - `DB_HOST=sql123.epizy.com` (or your InfinityFree database host)
   - `DB_USER=epiz_xxxxx` (your InfinityFree database user)
   - `DB_PASS=your_actual_password` (your database password)
   - `DB_NAME=epiz_xxxxx_videochat` (your database name)
   - `APP_URL=https://yourdomain.infinityfree.com` (your actual domain)

### Step 2: Upload Files to InfinityFree
1. Log into InfinityFree Control Panel
2. Go to **File Manager**
3. Navigate to **public_html** folder (your web root)
4. Upload all the files listed in "Upload These Files/Folders" section above
5. Keep the directory structure intact (folders like `/css`, `/js`, `/api` should be under public_html)

### Step 3: Set Up Database
1. In InfinityFree Control Panel, go to **MySQL Databases**
2. Create a new database (or use existing one)
3. Note down your database credentials

### Step 4: Import Database Schema
1. Open **phpMyAdmin** from InfinityFree Control Panel
2. Select your database
3. Go to **Import** tab
4. Upload the `database.sql` file from your computer
5. Click **Go** to execute
6. Verify tables are created: `users`, `signals`, `messages`

### Step 5: Verify .env.local is in Place
1. In File Manager, check that `.env.local` is in the **public_html** root
2. Make sure it contains your correct database credentials
3. Files starting with `.` are hidden - check "Show hidden files" option in File Manager if you can't see it

### Step 6: Test the Connection
1. Open your browser and go to: `https://yourdomain.infinityfree.com/test_connection.php`
2. You should see a success message: "Connection successful!"
3. If connection fails, check your `.env.local` credentials

### Step 7: Create uploads Directory (if not uploaded)
1. In File Manager, right-click in **public_html**
2. Create new folder: `uploads`
3. Set permissions to **755** (read/write)

---

## 🔐 Security Checklist

- [ ] `.env.local` is uploaded but contains **real credentials**
- [ ] `.env` file is **NOT uploaded** (local only)
- [ ] All `.md` documentation files are **NOT uploaded**
- [ ] `debug_*.php` and `test_*.php` files are **removed or not uploaded**
- [ ] `.htaccess` is uploaded (protects sensitive files)
- [ ] `uploads/` folder permissions are set to **755**
- [ ] `config.php` is protected by `.htaccess` (no direct access)
- [ ] HTTPS is enabled on your domain (check DNS/SSL settings)

---

## 🚀 Post-Deployment Verification

1. **Test Login:**
   - Go to `https://yourdomain.infinityfree.com/login.php`
   - Create a new account
   - Verify signup works

2. **Test Dashboard:**
   - Log in and verify dashboard loads
   - Check that WebRTC features are accessible

3. **Test API Endpoints:**
   - Check `/api/get_signals.php` is accessible
   - Verify database queries work

4. **Check Logs:**
   - In InfinityFree, monitor error logs
   - Check for connection errors or permission issues

---

## ⚠️ Common Issues & Solutions

### Issue: "Connection failed" error
**Solution:**
- Verify database credentials in `.env.local`
- Check database host (usually `sql123.epizy.com` but may vary)
- Ensure database user has proper permissions
- Contact InfinityFree support for database details

### Issue: `.env.local` not found
**Solution:**
- File Manager → Show hidden files
- Upload `.env.local` to public_html root
- Verify filename is exactly `.env.local` (with the dot)

### Issue: 403 Forbidden error
**Solution:**
- Check folder permissions (should be 755)
- Verify `.htaccess` is properly formatted
- Check if PHP execution is enabled on your hosting plan

### Issue: WebRTC not working
**Solution:**
- Verify HTTPS is enabled (required for WebRTC)
- Check that `.htaccess` HTTPS rewrite rule is in place
- Some InfinityFree plans may have WebRTC restrictions

---

## 📋 File Upload Checklist

Use this checklist when uploading:

```
☐ Uploaded /css/ folder with style.css
☐ Uploaded /js/ folder with dashboard.js, webrtc.js
☐ Uploaded /api/ folder with all PHP files
☐ Uploaded .htaccess to public_html root
☐ Uploaded .env.local with real InfinityFree credentials
☐ Uploaded all main PHP files (index.php, login.php, etc.)
☐ Uploaded composer.json
☐ Created /uploads/ folder with 755 permissions
☐ Imported database.sql to InfinityFree MySQL
☐ Tested connection with test_connection.php
☐ Verified HTTPS is working
☐ DID NOT upload .git/, .env, *.md files, debug files
```

---

## 💡 Tips for Success

1. **Use FTP/SFTP** - Upload via File Manager can be slow for many files. Consider using FTP.
2. **Test locally first** - Verify everything works on XAMPP before uploading.
3. **Backup database** - Before importing, backup any existing database.
4. **Monitor logs** - Check InfinityFree logs after deployment for errors.
5. **Keep production separate** - Don't use the same database for testing and production.
