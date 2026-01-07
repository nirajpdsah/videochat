# ⚙️ Configuration Guide - config.php

**File:** `config.php`  
**Size:** ~125 lines  
**Purpose:** Database connection, environment variable loading, and authentication utilities  
**Security Level:** 🔐 HIGH - Contains database credentials (never commit to Git)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Environment Variable Loading](#environment-variable-loading)
3. [Database Connection](#database-connection)
4. [Authentication Functions](#authentication-functions)
5. [Input Sanitization](#input-sanitization)
6. [Multi-Host Support](#multi-host-support)
7. [Debugging & Troubleshooting](#debugging--troubleshooting)
8. [Security Best Practices](#security-best-practices)

---

## 📖 Overview

This is the **first file included by all PHP pages** (via `require_once 'config.php'`). It:

1. ✅ Loads database credentials from environment variables
2. ✅ Connects to MySQL database
3. ✅ Provides authentication helper functions
4. ✅ Sanitizes user input to prevent SQL injection
5. ✅ Starts PHP sessions for user tracking

### Key Facts
- **Loaded by:** index.php, login.php, signup.php, call.php, dashboard.php, and all API endpoints
- **Must be included first** before any database queries
- **Contains sensitive data** - never share or commit to Git
- **Supports multiple hosting providers** - InfinityFree, Railway, local, etc.

---

## 🔄 Environment Variable Loading

### How It Works

The config system tries **multiple sources** in order:

```
.env.local (local overrides)
     ↓
  .env (general)
     ↓
config.production.php (array config)
     ↓
Environment Variables (InfinityFree/Railway)
     ↓
Hardcoded Defaults (fallback)
```

### The loadEnvFile() Function

```php
function loadEnvFile($path)
{
    // Check if file exists and is readable
    if (!is_readable($path)) {
        return;
    }

    // Read file line by line
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Skip comments (lines starting with #)
        if ($trimmed[0] === '#') {
            continue;
        }
        
        // Skip lines without = sign
        if (strpos($trimmed, '=') === false) {
            continue;
        }
        
        // Parse KEY=VALUE format
        list($key, $value) = explode('=', $trimmed, 2);
        
        // Register in multiple places so all code can access it
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
```

**What this does:**
- Reads `.env` and `.env.local` files
- Parses `KEY=VALUE` format
- Ignores comments and blank lines
- Stores variables in 3 places (environment, $_ENV, $_SERVER)

### Example .env File

Create `.env` file in root directory:

```env
# Database
DB_HOST=localhost
DB_USER=root
DB_PASS=mypassword
DB_NAME=videochat_db
DB_PORT=3306

# For InfinityFree
IF_DB_HOST=sql312.infinityfree.com
IF_DB_USER=if0_37123456_videochat
IF_DB_PASS=your_complicated_password
IF_DB_NAME=if0_37123456_videochat

# Application
APP_ENV=development
DEBUG=true
```

### The loadPhpConfig() Function

Alternative to `.env` files - uses PHP array instead:

**File: `config.production.php`**
```php
<?php
return [
    'DB_HOST' => 'sql312.infinityfree.com',
    'DB_USER' => 'if0_37123456_videochat',
    'DB_PASS' => 'your_password',
    'DB_NAME' => 'if0_37123456_videochat',
    'DB_PORT' => '3306',
];
?>
```

**Why both?** 
- `.env` is Git-ignored (safe for shared repos)
- `config.production.php` works when hosts block dotfiles
- Fallback system ensures something works everywhere

---

## 💾 Database Connection

### Connection Attempt

```php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please check your configuration.");
}
```

### What Each Constant Does

| Constant | Example | Source | Meaning |
|----------|---------|--------|---------|
| `DB_HOST` | localhost | .env | Server address |
| `DB_USER` | root | .env | MySQL username |
| `DB_PASS` | password | .env | MySQL password |
| `DB_NAME` | videochat_db | .env | Database name |
| `DB_PORT` | 3306 | .env | MySQL port (3306 = default) |

### Character Encoding

```php
$conn->set_charset("utf8mb4");
```

Sets database to use UTF-8 encoding (supports emojis, international characters).

### Why MySQLi vs PDO?

**MySQLi (Object-Oriented):**
- ✅ Built-in PHP, no extra install
- ✅ Procedural OR object-oriented
- ✅ Supports prepared statements (prevents SQL injection)

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
```

**PDO:**
- More abstract, works with multiple databases
- But harder to set up on shared hosts

---

## 🔐 Authentication Functions

### isLoggedIn()

Checks if user has an active session.

```php
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
```

**Usage:**
```php
<?php
require 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// User is logged in, show page
?>
```

### getCurrentUser()

Fetches current user's data from database.

```php
function getCurrentUser() {
    global $conn;
    
    if (!isLoggedIn()) {
        return null;  // No session
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Prepared statement (safe from SQL injection)
    $stmt = $conn->prepare("SELECT id, username, email, profile_picture, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);  // "i" = integer
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();  // Return as array
    }
    
    return null;  // User not found
}
```

**Returns:**
```php
[
    'id' => 1,
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'profile_picture' => '/uploads/john.jpg',
    'status' => 'online'
]
```

**Usage:**
```php
$user = getCurrentUser();

if ($user) {
    echo "Welcome, " . $user['username'];
} else {
    echo "User not found";
}
```

---

## 🧹 Input Sanitization

### cleanInput()

Protects against XSS attacks by escaping HTML.

```php
function cleanInput($data) {
    $data = trim($data);           // Remove whitespace
    $data = stripslashes($data);   // Remove backslashes
    $data = htmlspecialchars($data);  // Convert special chars to HTML entities
    return $data;
}
```

**What it does:**

| Input | After Clean |
|-------|-------------|
| ` hello ` | hello |
| `O\'Reilly` | O'Reilly |
| `<script>alert('xss')</script>` | `&lt;script&gt;alert('xss')&lt;/script&gt;` |

**Usage:**
```php
$username = cleanInput($_POST['username']);  // Safe to display
echo $username;  // Won't execute JavaScript
```

### Best Practices

✅ **Always use prepared statements for database:**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

✅ **Sanitize when displaying user input:**
```php
echo cleanInput($_POST['comment']);
```

❌ **Never concatenate user input into queries:**
```php
$query = "SELECT * FROM users WHERE username = '" . $_POST['username'] . "'";
// VULNERABLE TO SQL INJECTION!
```

---

## 🌍 Multi-Host Support

This config works across **5+ different hosting environments**:

### 1. **Local Development** (XAMPP/WAMP)
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=videochat_db
DB_PORT=3306
```

### 2. **InfinityFree** (Free PHP hosting)
```env
IF_DB_HOST=sql312.infinityfree.com
IF_DB_USER=if0_37123456_videochat
IF_DB_PASS=SecurePassword123
IF_DB_NAME=if0_37123456_videochat
```

Fallback chain:
```php
envOrDefault('DB_HOST', 
    envOrDefault('IF_DB_HOST',  // Try InfinityFree
        envOrDefault('INFINITYFREE_DB_HOST',  // Alternative name
            'localhost'  // Final fallback
        )
    )
)
```

### 3. **Railway** (Modern cloud platform)
Railway sets environment variables automatically:
```
MYSQLHOST=gateway.railway.app
MYSQLUSER=root
MYSQLPASSWORD=...
MYSQLDATABASE=railway
MYSQLPORT=5432
```

Config automatically detects and uses them.

### 4. **Other Platforms** (Heroku, Render, etc.)
Most platforms set `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` directly.

### 5. **Docker/Kubernetes**
Environment variables injected at runtime:
```dockerfile
ENV DB_HOST=mysql-service
ENV DB_USER=root
ENV DB_PASS=secret
```

---

## 🐛 Debugging & Troubleshooting

### Issue: "Database connection failed"

**Step 1:** Verify credentials in .env
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=mypassword
```

**Step 2:** Check error in logs
```php
error_log("Database connection failed: " . $conn->connect_error);
```

Look in `php_errors.log` or check web server error log.

**Step 3:** Test connection directly
```php
<?php
$conn = new mysqli('localhost', 'root', 'mypassword', 'videochat_db', 3306);
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connection successful!";
}
?>
```

### Issue: "Column doesn't exist" or "Unknown database"

**Cause:** Database or tables not created.

**Solution:** Run database.sql:
1. Go to phpMyAdmin
2. Create database `videochat_db`
3. Import `database.sql`
4. Tables are now created

### Issue: "Access denied for user"

**Cause:** Wrong username or password.

**Solution:**
```bash
# MySQL command line
mysql -u root -p
# Enter your password when prompted
```

Then verify user exists:
```sql
SELECT User, Host FROM mysql.user;
```

### Issue: Special characters not displaying (encoding)

**Cause:** Database charset not UTF-8.

**Solution:** The config already does this:
```php
$conn->set_charset("utf8mb4");
```

But if it still doesn't work, update database:
```sql
ALTER DATABASE videochat_db CHARACTER SET utf8mb4;
```

### Debug Mode

Enable detailed error messages:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);  // ⚠️ Turn off in production!
```

In production, set:
```php
ini_set('display_errors', 0);  // Don't show errors to users
error_log("Database query failed");  // Log instead
```

---

## 🔒 Security Best Practices

### 1. Never Commit .env to Git

**Create `.gitignore`:**
```
.env
.env.local
config.production.php
```

Credentials should never be in Git history.

### 2. Use Environment Variables

✅ **Good:**
```php
$password = getenv('DB_PASS');
```

❌ **Bad:**
```php
$password = 'hardcoded_password_123';  // Anyone can see!
```

### 3. Use Prepared Statements

✅ **Good (prevents SQL injection):**
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
```

❌ **Bad (vulnerable):**
```php
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
```

### 4. Hash Passwords

✅ **Good:**
```php
$hashed = password_hash($password, PASSWORD_BCRYPT);
$conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed);
```

❌ **Bad:**
```php
$conn->query("INSERT INTO users (password) VALUES ('$password')");
```

### 5. Use HTTPS in Production

- All data (including passwords) should be encrypted in transit
- Get free SSL certificate from Let's Encrypt
- Configure web server to redirect HTTP → HTTPS

### 6. Set Secure Session Cookies

**In config.php, add:**
```php
session_set_cookie_params([
    'secure' => true,       // HTTPS only
    'httponly' => true,     // No JavaScript access
    'samesite' => 'Strict'  // CSRF protection
]);
```

### 7. Rate Limiting (Prevent Brute Force)

Add after database check:
```php
function checkRateLimit($ip, $action) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM logs WHERE ip = ? AND action = ? AND time > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->bind_param("ss", $ip, $action);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['count'] < 10;  // Max 10 attempts per hour
}
```

### 8. Regular Backups

- Backup database regularly
- Store backups in secure location
- Test restore process

---

## 📝 Configuration Checklist

Before deploying to production:

- [ ] `.env` file created with real credentials
- [ ] `.env` added to `.gitignore`
- [ ] Database created and populated with `database.sql`
- [ ] Database user has correct permissions
- [ ] HTTPS/SSL certificate installed
- [ ] Passwords are hashed (password_hash)
- [ ] Session security configured
- [ ] Error reporting set to log, not display
- [ ] Backups configured
- [ ] Rate limiting implemented
- [ ] Regular security audits planned

---

## 🔗 Related Files

- [database.sql](DATABASE.md) - Database schema
- [config.production.php](config.production.php) - Alternative array-based config
- [login.php](AUTH.md) - Uses getCurrentUser()
- [dashboard.php](DASHBOARD.md) - Uses isLoggedIn()
- [api/](API.md) - All API endpoints use config.php

---

## 📚 Reference Links

- [PHP MySQLi Documentation](https://www.php.net/manual/en/class.mysqli.php)
- [OWASP Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [PHP Prepared Statements](https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php)
- [Environment Variables in PHP](https://www.php.net/manual/en/function.getenv.php)
