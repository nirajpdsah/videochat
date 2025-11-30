# Deployment Guide for VideoChat

## 🚀 Railway Deployment (Recommended)

### Step 1: Prepare Your Repository
1. Push your code to GitHub
2. Make sure `database.sql` is in the root directory

### Step 2: Set Up Railway
1. Go to [railway.app](https://railway.app) and sign up
2. Click "New Project" → "Deploy from GitHub repo"
3. Select your repository

### Step 3: Add MySQL Database
1. In your Railway project, click "+ New"
2. Select "Database" → "MySQL"
3. Railway will automatically create the database

### Step 4: Run Database Setup

After MySQL is added, you need to create the database tables. Here are **3 easy methods**:

#### **Method 1: Using Railway's Built-in MySQL Console (Easiest - Recommended)**

1. **Open MySQL Service:**
   - In your Railway project dashboard, click on the **MySQL** service (it should be listed alongside your Web Service)

2. **Open MySQL Console:**
   - Click on the **"Data"** tab (or **"Query"** tab, depending on Railway version)
   - Look for a button that says **"Open MySQL Console"**, **"Query"**, or **"Run Query"**
   - Click it to open the MySQL query editor

3. **Run the SQL Script:**
   - Open the `database.sql` file from your project (on your computer)
   - **Copy ALL the contents** of `database.sql` (Ctrl+A, then Ctrl+C)
   - **Paste it** into the Railway MySQL console/query editor
   - Click **"Run"** or **"Execute"** button
   - Wait for success message - you should see "Query OK" or similar

4. **Verify Tables Created:**
   - In the same console, run: `SHOW TABLES;`
   - You should see: `users`, `signals`, and `messages` tables

#### **Method 2: Using Railway's Query Tab**

1. Click on your **MySQL** service in Railway
2. Go to **"Query"** tab (or **"Data"** → **"Query"**)
3. You'll see a text area where you can type SQL
4. Copy and paste the entire `database.sql` content
5. Click **"Run Query"** or press **Ctrl+Enter**
6. Done! ✅

#### **Method 3: Using External MySQL Client (Advanced)**

If you prefer using a MySQL client like MySQL Workbench, phpMyAdmin, or command line:

1. **Get Connection Details:**
   - Click on your **MySQL** service in Railway
   - Go to **"Connect"** tab (or **"Variables"** tab)
   - You'll see connection details like:
     - **Host:** (something like `containers-us-west-xxx.railway.app`)
     - **Port:** (usually `3306`)
     - **Database:** (your database name)
     - **User:** (your MySQL username)
     - **Password:** (click to reveal)

2. **Connect with MySQL Client:**
   - Open your MySQL client (MySQL Workbench, phpMyAdmin, or command line)
   - Enter the connection details from Railway
   - Connect to the database

3. **Run the SQL File:**
   - In MySQL Workbench: File → Open SQL Script → Select `database.sql` → Execute
   - In phpMyAdmin: Select database → Import → Choose `database.sql` → Go
   - Command line: `mysql -h HOST -u USER -p DATABASE < database.sql`

**Note:** Method 1 or 2 is recommended as it's the easiest and doesn't require external tools!

### Step 5: Configure Environment Variables
Railway automatically sets these for MySQL:
- `MYSQLHOST`
- `MYSQLUSER`
- `MYSQLPASSWORD`
- `MYSQLDATABASE`
- `MYSQLPORT`

### Step 6: Deploy
1. Railway will auto-deploy on git push
2. Your app will be available at `your-app.railway.app`
3. HTTPS is automatically enabled

---

## 🌐 Render Deployment

### Step 1: Create Account
1. Sign up at [render.com](https://render.com)

### Step 2: Create Web Service
1. Click "New" → "Web Service"
2. Connect your GitHub repository
3. Settings:
   - **Name**: videochat
   - **Environment**: PHP
   - **Build Command**: (leave empty)
   - **Start Command**: `php -S 0.0.0.0:$PORT`

### Step 3: Add PostgreSQL Database
1. Click "New" → "PostgreSQL"
2. Note: You'll need to adapt your code for PostgreSQL or use MySQL addon

### Step 4: Set Environment Variables
Add these in the Environment tab:
- `DB_HOST`
- `DB_USER`
- `DB_PASS`
- `DB_NAME`
- `DB_PORT`

---

## 📦 InfinityFree Deployment

### Step 1: Sign Up
1. Go to [infinityfree.net](https://infinityfree.net)
2. Create a free account

### Step 2: Create Website
1. Go to Control Panel
2. Click "Create Account"
3. Choose a subdomain (e.g., `yourname.infinityfreeapp.com`)

### Step 3: Upload Files
1. Use File Manager or FTP
2. Upload all your files to `htdocs` directory

### Step 4: Create Database
1. Go to MySQL Databases in Control Panel
2. Create a new database
3. Create a user and assign to database
4. Run `database.sql` using phpMyAdmin

### Step 5: Update Config
Edit `config.php` with your database credentials

---

## ⚠️ Important Notes

### WebRTC Requirements
- **HTTPS is mandatory** for WebRTC to work
- All free hosting platforms provide free SSL certificates
- Make sure your site is accessed via HTTPS

### File Uploads
- Ensure `uploads/` directory has write permissions (755 or 777)
- Add `default-avatar.png` to the uploads folder
- Some hosts may have upload size limits

### Performance
- Free tiers have resource limitations
- For production use, consider paid hosting
- Monitor your usage to avoid hitting limits

### Database
- Run `database.sql` to create all tables
- Make sure foreign key constraints are enabled
- Regular backups recommended

---

## 🔧 Troubleshooting

### WebRTC Not Working
- Check browser console for errors
- Ensure HTTPS is enabled
- Verify STUN servers are accessible
- Check firewall/network restrictions

### Database Connection Errors
- Verify environment variables are set correctly
- Check database credentials
- Ensure database is running
- Test connection with a simple PHP script

### File Upload Issues
- Check directory permissions
- Verify upload_max_filesize in PHP settings
- Check disk space quota

---

## 📝 Post-Deployment Checklist

- [ ] Database tables created
- [ ] Default avatar image uploaded
- [ ] HTTPS enabled and working
- [ ] Test user registration
- [ ] Test login functionality
- [ ] Test video call between two users
- [ ] Test chat functionality
- [ ] Verify file uploads work
- [ ] Check error logs for issues

---

## 🆘 Support

For issues:
1. Check Railway/Render/InfinityFree documentation
2. Review application logs
3. Test locally first
4. Check browser console for errors

