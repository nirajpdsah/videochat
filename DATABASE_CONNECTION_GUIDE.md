# 🔌 How to Connect Database in Railway

## Understanding Railway Database Connection

In Railway, there are **TWO types of connections**:

1. **Automatic Connection** - Railway automatically connects your PHP app to MySQL (this is what you need!)
2. **Manual Connection** - For using external tools like MySQL Workbench

---

## ✅ Method 1: Automatic Connection (Already Done!)

### How It Works:

When you add MySQL service to Railway, Railway **automatically**:

1. ✅ Creates environment variables with database credentials
2. ✅ Makes them available to your PHP app
3. ✅ Your `config.php` already reads these variables!

### Your `config.php` Already Does This:

```php
// Railway automatically provides these:
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'videochat_db');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
```

**This means your app is ALREADY CONNECTED!** 🎉

### How to Verify Automatic Connection:

1. **Check Environment Variables:**
   - Click on your **Web Service** (PHP app) in Railway
   - Go to **"Variables"** tab
   - You should see these variables automatically set:
     ```
     MYSQLHOST=containers-us-west-xxx.railway.app
     MYSQLUSER=root
     MYSQLPASSWORD=xxxxx
     MYSQLDATABASE=railway
     MYSQLPORT=3306
     ```

2. **Test the Connection:**
   - Deploy your app
   - Visit your app URL
   - Try to register a user
   - If it works, database is connected! ✅

---

## 🔧 Method 2: Manual Connection (For External Tools)

If you want to connect using MySQL Workbench, phpMyAdmin, or command line:

### Step 1: Get Connection Details

1. **Click on your MySQL service** in Railway
2. **Go to "Connect" tab** (or "Variables" tab)
3. You'll see connection information like:

   ```
   Host: containers-us-west-123.railway.app
   Port: 3306
   Database: railway
   User: root
   Password: [click to reveal]
   ```

4. **Copy these details** (click "Reveal" for password)

### Step 2: Connect with MySQL Workbench

1. **Open MySQL Workbench**
2. **Click "+"** to add new connection
3. **Enter details:**
   - **Connection Name:** Railway DB (or any name)
   - **Hostname:** `containers-us-west-123.railway.app` (from Railway)
   - **Port:** `3306` (from Railway)
   - **Username:** `root` (from Railway)
   - **Password:** Click "Store in Keychain" and enter password from Railway
4. **Click "Test Connection"**
5. **Click "OK"** to save
6. **Double-click** the connection to connect

### Step 3: Connect with Command Line

```bash
mysql -h containers-us-west-123.railway.app \
      -P 3306 \
      -u root \
      -p
```

Then enter the password when prompted.

### Step 4: Connect with PHP (for testing)

Create a test file `test_db.php`:

```php
<?php
require_once 'config.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "✅ Database connected successfully!<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<br>Tables found:<br>";
        while ($row = $result->fetch_array()) {
            echo "- " . $row[0] . "<br>";
        }
    }
}
?>
```

Upload this to Railway and visit: `your-app.railway.app/test_db.php`

---

## 🎯 Quick Connection Checklist

### For Your PHP App (Automatic):

- [x] MySQL service added to Railway
- [x] Environment variables automatically set
- [x] `config.php` reads environment variables
- [ ] **Deploy your app** (git push)
- [ ] **Test connection** (try registering a user)

**That's it!** Your app connects automatically.

### For External Tools (Manual):

- [ ] Get connection details from Railway MySQL service
- [ ] Use details in MySQL Workbench/phpMyAdmin/command line
- [ ] Test connection

---

## 🔍 Where to Find Connection Details

### In Railway Dashboard:

1. **Click on MySQL service**
2. **Look for these tabs:**
   - **"Connect"** tab ← Connection details here
   - **"Variables"** tab ← Environment variables here
   - **"Data"** tab ← Query console here

### What You'll See:

**In "Connect" tab:**
```
MySQL Connection Details
─────────────────────────
Host: containers-us-west-123.railway.app
Port: 3306
Database: railway
User: root
Password: [Reveal]
```

**In "Variables" tab:**
```
MYSQLHOST=containers-us-west-123.railway.app
MYSQLPORT=3306
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=xxxxx
```

---

## ⚠️ Important Notes

### 1. Your App Connects Automatically
- You don't need to do anything special
- Railway handles the connection
- Your `config.php` is already configured correctly

### 2. Environment Variables Are Shared
- When you add MySQL, Railway automatically adds variables to your Web Service
- These variables are available to your PHP app
- No manual configuration needed

### 3. Connection is Secure
- Railway uses secure connections
- Passwords are encrypted
- No need to worry about security

---

## 🐛 Troubleshooting Connection

### Problem: "Connection failed"

**Check 1: MySQL Service is Running**
- Go to MySQL service in Railway
- Make sure it shows "Running" status (green dot)

**Check 2: Environment Variables Exist**
- Go to Web Service → Variables tab
- Verify MYSQLHOST, MYSQLUSER, etc. are set
- If missing, Railway might not have linked them automatically

**Check 3: Database Tables Exist**
- Connect to MySQL console
- Run: `SHOW TABLES;`
- Should see: users, signals, messages

**Check 4: Test Connection Manually**
- Create `test_db.php` (code above)
- Deploy and visit it
- Check for error messages

### Problem: "Access denied"

**Solution:**
- Verify you're using correct credentials from Railway
- Check if password is correct (click "Reveal" in Railway)
- Make sure you're connecting to the right database

### Problem: "Can't find connection details"

**Solution:**
- Click on MySQL service
- Look for "Connect" or "Variables" tab
- If you don't see them, try refreshing the page
- Check if MySQL service is fully provisioned (might take a minute)

---

## 📝 Summary

### For Your PHP App:
✅ **Already connected!** Railway does this automatically.
✅ Just deploy your app and it will connect.
✅ No manual configuration needed.

### For External Tools:
1. Get connection details from Railway MySQL service
2. Use them in your MySQL client
3. Connect and manage database

---

## 🎯 Next Steps

After confirming connection:

1. ✅ Database is connected (automatic)
2. ⬜ Run `database.sql` to create tables
3. ⬜ Test your app
4. ⬜ Verify everything works

**Your database connection is handled automatically by Railway!** You just need to make sure:
- MySQL service is added ✅
- Tables are created (run database.sql) ⬜
- App is deployed ⬜

---

**Need more help?** Let me know what specific connection issue you're facing!

