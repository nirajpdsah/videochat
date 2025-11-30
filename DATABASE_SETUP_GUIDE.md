# 🗄️ Database Setup Guide - Step by Step

## After Adding MySQL to Railway

Once you've added MySQL service to Railway, follow these steps to create your database tables.

---

## 🎯 Method 1: Railway MySQL Console (Easiest)

### Step 1: Open MySQL Service
1. In your Railway project dashboard
2. You'll see two services:
   - **Web Service** (your PHP app)
   - **MySQL** (your database)
3. **Click on the MySQL service**

### Step 2: Find the Query/Console Tab
Look for one of these tabs:
- **"Data"** tab → Then look for **"Query"** or **"Console"**
- **"Query"** tab (directly visible)
- **"MySQL Console"** button

### Step 3: Open the Query Editor
Click on:
- **"Open MySQL Console"** button, OR
- **"Query"** tab, OR
- **"Run Query"** button

You should see a text area where you can type SQL commands.

### Step 4: Copy Your SQL File
1. Open `database.sql` file from your project (on your computer)
2. **Select ALL** the text (Ctrl+A or Cmd+A)
3. **Copy** it (Ctrl+C or Cmd+C)

### Step 5: Paste and Run
1. **Paste** the SQL into the Railway query editor (Ctrl+V or Cmd+V)
2. Click **"Run"** or **"Execute"** button
   - Or press **Ctrl+Enter** (Windows/Linux)
   - Or press **Cmd+Enter** (Mac)

### Step 6: Verify Success
You should see:
- ✅ **"Query OK"** message
- ✅ Or a success notification
- ✅ No error messages

### Step 7: Check Tables Were Created
In the same query editor, type:
```sql
SHOW TABLES;
```
Click **"Run"** again.

You should see:
```
+-------------------+
| Tables_in_db      |
+-------------------+
| messages          |
| signals           |
| users             |
+-------------------+
```

If you see all 3 tables, **you're done!** ✅

---

## 🖥️ Method 2: Using Railway Query Tab

### Step 1: Navigate to Query Tab
1. Click on **MySQL** service
2. Click on **"Query"** tab (or **"Data"** → **"Query"**)

### Step 2: Paste SQL
1. Copy all content from `database.sql`
2. Paste into the query editor

### Step 3: Execute
- Click **"Run Query"** button
- Or use keyboard shortcut (usually shown in the UI)

---

## 💻 Method 3: Using External MySQL Client

### Option A: MySQL Workbench

1. **Get Connection Details from Railway:**
   - Click MySQL service → **"Connect"** tab
   - Note down:
     - Host
     - Port
     - Database
     - User
     - Password

2. **Connect in MySQL Workbench:**
   - Open MySQL Workbench
   - Click **"+"** to add new connection
   - Enter Railway connection details
   - Click **"Test Connection"**
   - Click **"OK"** to save

3. **Run SQL File:**
   - Double-click your connection to connect
   - Go to **File** → **Open SQL Script**
   - Select `database.sql`
   - Click **"Execute"** (lightning bolt icon) or press **Ctrl+Shift+Enter**

### Option B: Command Line

1. **Get Connection Details:**
   - Railway MySQL service → **"Connect"** tab
   - Copy connection details

2. **Run Command:**
   ```bash
   mysql -h [HOST] -P [PORT] -u [USER] -p [DATABASE] < database.sql
   ```
   
   Replace:
   - `[HOST]` with Railway MySQL host
   - `[PORT]` with Railway MySQL port
   - `[USER]` with Railway MySQL user
   - `[DATABASE]` with Railway MySQL database name
   
   Example:
   ```bash
   mysql -h containers-us-west-123.railway.app -P 3306 -u root -p videochat_db < database.sql
   ```
   
   It will ask for password - enter Railway MySQL password

### Option C: phpMyAdmin (if you have it)

1. **Get Connection Details** from Railway
2. **Login to phpMyAdmin** with Railway credentials
3. **Select Database** from left sidebar
4. Click **"Import"** tab
5. Click **"Choose File"** → Select `database.sql`
6. Click **"Go"** button

---

## ✅ Verification Checklist

After running `database.sql`, verify:

- [ ] No error messages appeared
- [ ] Success message shown
- [ ] Can see `users` table
- [ ] Can see `signals` table
- [ ] Can see `messages` table

**Test Query:**
```sql
SHOW TABLES;
```
Should return 3 tables.

**Check Table Structure:**
```sql
DESCRIBE users;
```
Should show columns: id, username, email, password, profile_picture, status, etc.

---

## 🐛 Troubleshooting

### Error: "Table already exists"
**Solution:** The tables are already created. You can either:
- Drop tables first: `DROP TABLE messages, signals, users;`
- Or ignore the error if tables are correct

### Error: "Access denied"
**Solution:** 
- Check you're using correct MySQL credentials from Railway
- Verify you're connected to the right database

### Error: "Syntax error"
**Solution:**
- Make sure you copied the ENTIRE `database.sql` file
- Check for any missing semicolons
- Try running one table at a time

### Can't find Query/Console tab
**Solution:**
- Try refreshing the page
- Look for "Data" tab first, then Query inside it
- Check if Railway UI has updated (they change it sometimes)
- Try clicking around different tabs

### Tables not showing
**Solution:**
- Make sure you selected the correct database
- Run `USE your_database_name;` first
- Then run `SHOW TABLES;`

---

## 📝 Quick Reference

**What you're doing:**
- Creating 3 tables: `users`, `signals`, `messages`
- Setting up the database structure for your app

**Why it's needed:**
- Your PHP app needs these tables to store:
  - User accounts (`users`)
  - WebRTC signaling data (`signals`)
  - Chat messages (`messages`)

**When to do it:**
- Once, right after adding MySQL service
- Before testing your app

---

## 🎯 Recommended Method

**Use Method 1 (Railway MySQL Console)** because:
- ✅ No external tools needed
- ✅ Works directly in Railway
- ✅ Easiest and fastest
- ✅ No connection setup required

---

**Need help?** If you're stuck, describe what you see in Railway and I'll guide you!

