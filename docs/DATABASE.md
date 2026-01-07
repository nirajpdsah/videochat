# 📊 Database Schema Guide - database.sql

**File:** `database.sql`  
**Purpose:** Database blueprint - creates tables, indexes, and relationships  
**How to Use:** Import in phpMyAdmin or run via MySQL CLI  
**Database:** MySQL 5.7+ or MariaDB 10.3+

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Tables & Structure](#tables--structure)
3. [Relationships (Foreign Keys)](#relationships-foreign-keys)
4. [Indexes for Performance](#indexes-for-performance)
5. [Data Types Explained](#data-types-explained)
6. [Signal Types Reference](#signal-types-reference)
7. [Setting Up Database](#setting-up-database)
8. [Common Queries](#common-queries)
9. [Maintenance & Cleanup](#maintenance--cleanup)
10. [Troubleshooting](#troubleshooting)

---

## 📖 Overview

Wartalaap uses **3 core tables**:

| Table | Purpose | Rows Per Day | Cleanup Needed? |
|-------|---------|-------------|-----------------|
| `users` | Store user accounts | ~10 | No |
| `signals` | WebRTC signaling | ~500 | Yes (auto-cleanup) |
| `messages` | Chat messages | ~100 | No (keep history) |

### Database Diagram

```
users (1)
  ├─→ signals (many)  [from_user_id, to_user_id]
  ├─→ messages (many) [from_user_id, to_user_id]
  └─→ (relationships via foreign keys)
```

---

## 🏗️ Tables & Structure

### 1. USERS Table - Core User Data

```sql
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `profile_picture` VARCHAR(255) DEFAULT 'default-avatar.png',
    `status` ENUM('online', 'offline', 'on_call') DEFAULT 'offline',
    `last_seen` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Column Reference

| Column | Type | Example | Notes |
|--------|------|---------|-------|
| `id` | INT(11) | 1, 2, 3 | User ID, auto-incremented |
| `username` | VARCHAR(50) | "john_doe" | UNIQUE - no duplicates |
| `email` | VARCHAR(100) | "john@example.com" | UNIQUE - for login/recovery |
| `password` | VARCHAR(255) | `$2y$10$hash...` | Always hashed, never plain text |
| `profile_picture` | VARCHAR(255) | "/uploads/john.jpg" | Path to profile image file |
| `status` | ENUM | "online" or "offline" | Shows if user is active |
| `last_seen` | DATETIME | "2026-01-07 14:30:45" | When user last logged in |
| `created_at` | DATETIME | "2025-12-01 10:15:22" | Account creation timestamp |

#### Sample Data

```sql
INSERT INTO users VALUES (
    1,
    'john_doe',
    'john@example.com',
    '$2y$10$N9qo8uLOickgx2ZMRZoM2eZst...',  -- password_hash('password')
    '/uploads/john.jpg',
    'online',
    '2026-01-07 14:30:45',
    '2025-12-01 10:15:22'
);
```

#### Dashboard SQL Query

Show all online users except current user:

```sql
SELECT id, username, profile_picture, status, last_seen 
FROM users 
WHERE status = 'online' AND id != 1
ORDER BY last_seen DESC;
```

---

### 2. SIGNALS Table - WebRTC Call Signaling

```sql
CREATE TABLE IF NOT EXISTS `signals` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `from_user_id` INT(11) NOT NULL,
    `to_user_id` INT(11) NOT NULL,
    `signal_type` ENUM(
        'offer', 'answer', 'ice-candidate', 'call-request',
        'call-accepted', 'call-rejected', 'call-ended',
        'receiver-ready', 'video-status', 'audio-status'
    ) NOT NULL,
    `signal_data` TEXT NOT NULL,
    `call_type` ENUM('video', 'audio') DEFAULT 'video',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_to_user` (`to_user_id`, `is_read`),
    INDEX `idx_from_user` (`from_user_id`),
    FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Column Reference

| Column | Type | Example | Notes |
|--------|------|---------|-------|
| `id` | INT(11) | 1, 2, 3 | Signal ID, auto-incremented |
| `from_user_id` | INT(11) | 1 | Who sent the signal |
| `to_user_id` | INT(11) | 2 | Who receives the signal |
| `signal_type` | ENUM | "offer" | See signal types below |
| `signal_data` | TEXT | `{...SDP...}` | JSON with connection details |
| `call_type` | ENUM | "video" or "audio" | Type of call |
| `is_read` | TINYINT | 0 or 1 | Whether receiver fetched it |
| `created_at` | DATETIME | "2026-01-07 14:30:45" | When signal was sent |

#### Signal Types Explained

| Signal Type | Purpose | Sender | Receiver | Data Example |
|------------|---------|--------|----------|--------------|
| `call-request` | Initial call attempt | User A | User B | `{initiator: 'user_a'}` |
| `offer` | WebRTC connection offer | User A | User B | `{type: "offer", sdp: "..."}` |
| `answer` | Response to offer | User B | User A | `{type: "answer", sdp: "..."}` |
| `ice-candidate` | Network path info | Both | Both | `{candidate: "...", ...}` |
| `call-accepted` | User accepted call | User B | User A | `{status: "accepted"}` |
| `call-rejected` | User rejected call | User B | User A | `{status: "rejected"}` |
| `call-ended` | Call disconnected | Both | Both | `{status: "ended"}` |
| `receiver-ready` | Receiver ready to accept | User B | User A | `{ready: true}` |
| `video-status` | Camera on/off | Both | Both | `{enabled: true/false}` |
| `audio-status` | Mic on/off | Both | Both | `{enabled: true/false}` |

#### Sample Data

**User A calls User B:**

```sql
-- 1. Call Request
INSERT INTO signals VALUES (
    NULL,
    1,                   -- from_user_id: User A
    2,                   -- to_user_id: User B
    'call-request',      -- signal_type
    '{"initiator":"user_a"}',  -- signal_data
    'video',
    0,
    NOW()
);

-- 2. WebRTC Offer
INSERT INTO signals VALUES (
    NULL,
    1,
    2,
    'offer',
    '{"type":"offer","sdp":"v=0\no=..."}',
    'video',
    0,
    NOW()
);

-- 3. ICE Candidate (network path)
INSERT INTO signals VALUES (
    NULL,
    1,
    2,
    'ice-candidate',
    '{"candidate":"candidate:842...","sdpMLineIndex":0}',
    'video',
    0,
    NOW()
);

-- 4. User B accepts
INSERT INTO signals VALUES (
    NULL,
    2,
    1,
    'answer',
    '{"type":"answer","sdp":"v=0\no=..."}',
    'video',
    0,
    NOW()
);
```

#### Common Signal Queries

Get unread signals for user 2:
```sql
SELECT * FROM signals 
WHERE to_user_id = 2 AND is_read = 0
ORDER BY created_at DESC;
```

Check if call is active:
```sql
SELECT * FROM signals 
WHERE (from_user_id IN (1,2) AND to_user_id IN (1,2))
  AND signal_type IN ('offer', 'answer', 'ice-candidate')
  AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE);
```

Cleanup old signals (delete after 1 hour):
```sql
DELETE FROM signals 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
  AND signal_type != 'call-ended';
```

---

### 3. MESSAGES Table - Chat History

```sql
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `from_user_id` INT(11) NOT NULL,
    `to_user_id` INT(11) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_conversation` (`from_user_id`, `to_user_id`),
    INDEX `idx_to_user` (`to_user_id`, `is_read`),
    FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Column Reference

| Column | Type | Example | Notes |
|--------|------|---------|-------|
| `id` | INT(11) | 1, 2, 3 | Message ID |
| `from_user_id` | INT(11) | 1 | Sender |
| `to_user_id` | INT(11) | 2 | Recipient |
| `message` | TEXT | "Hey, how are you?" | Message content |
| `is_read` | TINYINT | 0 or 1 | Whether recipient read it |
| `created_at` | DATETIME | "2026-01-07 14:30:45" | Timestamp |

#### Sample Data

```sql
INSERT INTO messages VALUES (
    NULL,
    1,                        -- from_user_id: John
    2,                        -- to_user_id: Jane
    'Hey, how are you doing?',
    1,                        -- is_read: yes
    '2026-01-07 14:30:45'
);
```

#### Common Message Queries

Get conversation between two users:
```sql
SELECT * FROM messages 
WHERE (from_user_id = 1 AND to_user_id = 2)
   OR (from_user_id = 2 AND to_user_id = 1)
ORDER BY created_at ASC;
```

Get unread messages for user:
```sql
SELECT * FROM messages 
WHERE to_user_id = 1 AND is_read = 0
ORDER BY created_at DESC;
```

Mark messages as read:
```sql
UPDATE messages 
SET is_read = 1 
WHERE to_user_id = 1 AND from_user_id = 2;
```

---

## 🔗 Relationships (Foreign Keys)

### Foreign Key Concept

A **foreign key** ensures data integrity:

```
users.id = 1
  ↓
signals.from_user_id = 1
signals.to_user_id = 1
  ↓
Only valid if user with id=1 exists
```

### Cascade Delete

```sql
FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
```

**What this means:**
- If user 1 is deleted from `users` table
- All signals/messages from/to that user are automatically deleted
- Prevents orphaned records

### Example

Delete a user:
```sql
DELETE FROM users WHERE id = 1;
```

Automatically cascades:
```
users: 1 row deleted
signals: 15 rows deleted (all signals from/to this user)
messages: 8 rows deleted (all messages from/to this user)
```

---

## ⚡ Indexes for Performance

### What Are Indexes?

Like a book's index - instead of reading every page, you jump to the right chapter.

```
Without index: Check all 100,000 signals
With index: Jump directly to signals for user 2 (found in milliseconds)
```

### Indexes in Wartalaap

| Table | Index | Columns | Usage |
|-------|-------|---------|-------|
| users | PRIMARY | id | Find user by ID |
| users | idx_status | status | Get all online users |
| users | idx_email | email | Check if email exists |
| signals | PRIMARY | id | Find signal by ID |
| signals | idx_to_user | to_user_id, is_read | Get unread signals |
| signals | idx_from_user | from_user_id | Get user's sent signals |
| messages | PRIMARY | id | Find message by ID |
| messages | idx_conversation | from_user_id, to_user_id | Get conversation history |
| messages | idx_to_user | to_user_id, is_read | Get unread messages |

### When Queries Use Indexes

**With index (fast):**
```sql
SELECT * FROM signals WHERE to_user_id = 2 AND is_read = 0;
-- Uses idx_to_user → instant
```

**Without index (slow):**
```sql
SELECT * FROM signals WHERE signal_data LIKE '%something%';
-- Checks every row → slow with 100k+ rows
```

---

## 📊 Data Types Explained

### INT(11)
- Integer numbers from -2 billion to +2 billion
- Used for: IDs, counts, statuses
- Size: 4 bytes

### VARCHAR(255)
- Text up to 255 characters
- Used for: Usernames, emails, URLs, small text
- Size: 1 byte per character + 2 byte overhead

### TEXT
- Large text (up to 65KB)
- Used for: JSON data, long messages
- Size: 2 bytes per character + 2 byte overhead

### ENUM('value1', 'value2')
- Fixed list of allowed values
- Used for: Status, signal types, call types
- Benefits: Saves space, validates data, prevents typos

```sql
status ENUM('online', 'offline', 'on_call')
-- Only these 3 values allowed
-- If you try: INSERT status='away' → ERROR!
```

### DATETIME
- Date and time: "2026-01-07 14:30:45"
- Used for: Timestamps, last_seen, created_at
- Size: 8 bytes

### TINYINT
- 0 or 1 (boolean)
- Used for: Flags, is_read, is_active
- Size: 1 byte

### utf8mb4
Character encoding that supports:
- English, Arabic, Chinese, Emoji 😀
- Without it: emojis and special chars → corrupted

---

## 🔄 Signal Types Reference

### Call Flow with Signal Types

```
User A (id=1)                    User B (id=2)
    ↓                                ↓
    └──→ signal='call-request'  ──→  Rings
         └──→ signal='offer'    ──→  User B accepts
              └──→ signal='answer'  → User A gets answer
                   └──→ signal='ice-candidate' ↔→ Both (multiple)
                        └──→ VIDEO CALL ESTABLISHED
                             └──→ User A disables camera
                                  └──→ signal='video-status' ──→ User B's overlay shows
                                       └──→ User A hangs up
                                            └──→ signal='call-ended' ──→ End call
```

### Signal Type Details

#### Signaling Phase (Connection)
- `call-request`: Initial "ring" signal
- `offer`: "I want to connect, here's my details"
- `answer`: "Yes, connect to me, here's my details"
- `ice-candidate`: Network routing info (sent multiple times)
- `receiver-ready`: "I'm ready to receive video/audio"

#### Call Control Phase (Active Call)
- `video-status`: Camera enabled/disabled
- `audio-status`: Microphone enabled/disabled
- `call-ended`: Hangup signal

#### Call Response Phase
- `call-accepted`: User explicitly accepted
- `call-rejected`: User declined call

---

## 🛠️ Setting Up Database

### Method 1: phpMyAdmin (Easiest)

1. Open `http://localhost/phpmyadmin`
2. Click "Databases"
3. Create database: `videochat_db`
4. Select it
5. Click "Import"
6. Upload `database.sql`
7. Click "Go"

### Method 2: MySQL CLI (Command Line)

```bash
mysql -u root -p

mysql> CREATE DATABASE videochat_db;
mysql> USE videochat_db;
mysql> SOURCE /path/to/database.sql;
mysql> SHOW TABLES;  # Verify: should show 3 tables
```

### Method 3: Programmatically (PHP)

```php
<?php
$conn = new mysqli('localhost', 'root', '', 'mysql');

// Read SQL file
$sql = file_get_contents('database.sql');

// Execute each statement
$queries = explode(';', $sql);
foreach ($queries as $query) {
    if (trim($query)) {
        $conn->query($query);
    }
}

echo "Database created successfully!";
?>
```

### Verify Installation

```sql
-- Check tables exist
SHOW TABLES;

-- Check users table structure
DESCRIBE users;

-- Check sample data (should be empty initially)
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM signals;
SELECT COUNT(*) FROM messages;
```

---

## 📝 Common Queries

### User Management

**Create user (signup):**
```php
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$hashed_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$stmt->bind_param("sss", $_POST['username'], $_POST['email'], $hashed_password);
$stmt->execute();
```

**Get user (login):**
```php
$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $_POST['email']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (password_verify($_POST['password'], $user['password'])) {
    // Password correct!
}
```

**Update user status:**
```sql
UPDATE users SET status = 'online' WHERE id = 1;
UPDATE users SET last_seen = NOW() WHERE id = 1;
UPDATE users SET status = 'offline' WHERE id = 1;
```

### Call Management

**Get online users:**
```sql
SELECT id, username, profile_picture, last_seen 
FROM users 
WHERE status = 'online'
ORDER BY last_seen DESC;
```

**Store call signal:**
```php
$stmt = $conn->prepare("INSERT INTO signals (from_user_id, to_user_id, signal_type, signal_data) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $from, $to, $type, $data);
$data = json_encode(['type' => 'offer', 'sdp' => $sdp]);
$stmt->execute();
```

**Get unread signals:**
```sql
SELECT * FROM signals 
WHERE to_user_id = 2 AND is_read = 0
ORDER BY created_at DESC
LIMIT 10;
```

**Mark signals as read:**
```sql
UPDATE signals SET is_read = 1 
WHERE to_user_id = 2 AND id = 5;
```

---

## 🧹 Maintenance & Cleanup

### Cleanup Script (Run Daily)

Create `cleanup.php`:

```php
<?php
require 'config.php';

// Delete signals older than 1 hour (except call-ended)
$conn->query("DELETE FROM signals WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

// Set offline users who haven't logged in 30 minutes
$conn->query("UPDATE users SET status = 'offline' WHERE last_seen < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");

// Delete orphaned messages (shouldn't happen with cascade, but just in case)
$conn->query("DELETE FROM messages WHERE from_user_id NOT IN (SELECT id FROM users)");

echo "Cleanup complete!";
?>
```

Schedule with cron job:
```bash
# Run daily at 2 AM
0 2 * * * /usr/bin/php /var/www/html/videochat/cleanup.php
```

### Database Optimization

```sql
-- Defragment table (run monthly)
OPTIMIZE TABLE users;
OPTIMIZE TABLE signals;
OPTIMIZE TABLE messages;

-- Check table health
CHECK TABLE users;

-- Repair if corrupted
REPAIR TABLE signals;
```

---

## 🐛 Troubleshooting

### Error: "Table already exists"

**Cause:** Running setup twice.

**Solution:** Drop table first:
```sql
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS signals;
DROP TABLE IF EXISTS messages;
```

Then re-run database.sql.

### Error: "Unknown column"

**Cause:** Schema mismatch - table exists but outdated.

**Solution:** Check column names:
```sql
DESCRIBE users;
```

If column missing, add it:
```sql
ALTER TABLE users ADD COLUMN new_column VARCHAR(255);
```

### Error: "Foreign key constraint fails"

**Cause:** Trying to insert signal with non-existent user_id.

**Example (wrong):**
```sql
-- User 999 doesn't exist
INSERT INTO signals (from_user_id, to_user_id, ...) VALUES (999, 1, ...);
-- ERROR!
```

**Solution:** Ensure user exists first:
```sql
SELECT COUNT(*) FROM users WHERE id = 1;  -- Returns 1 if exists
```

### Error: "Data too long for column"

**Cause:** Text exceeds column max length.

**Solution:** Use larger column type:
```sql
-- Old: VARCHAR(255) - too small
-- New: TEXT - up to 65KB
ALTER TABLE signals MODIFY COLUMN signal_data TEXT;
```

### Slow Queries

**Problem:** Queries taking > 1 second.

**Solution:** Add index:
```sql
-- Before (slow)
SELECT * FROM signals WHERE from_user_id = 1;  -- Checks all rows

-- After (fast)
CREATE INDEX idx_from_user ON signals(from_user_id);
SELECT * FROM signals WHERE from_user_id = 1;  -- Uses index
```

---

## 🔗 Related Files

- [config.php](CONFIG.md) - Database connection
- [database_add_signal_types.sql](database_add_signal_types.sql) - Signal type updates
- [API.md](API.md) - Queries used in API
- [DATABASE_SETUP_GUIDE.md](../DATABASE_SETUP_GUIDE.md) - Setup instructions

---

## 📊 Quick Reference Card

```
┌─────────────────────────────────────┐
│ USERS TABLE                         │
├─────────────────────────────────────┤
│ id (int, PK, AUTO_INCREMENT)        │
│ username (varchar 50, UNIQUE)       │
│ email (varchar 100, UNIQUE)         │
│ password (varchar 255, HASHED)      │
│ profile_picture (varchar 255)       │
│ status (enum: online/offline/on_call)
│ last_seen (datetime)                │
│ created_at (datetime)               │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ SIGNALS TABLE                       │
├─────────────────────────────────────┤
│ id (int, PK, AUTO_INCREMENT)        │
│ from_user_id (int, FK → users.id)   │
│ to_user_id (int, FK → users.id)     │
│ signal_type (enum: 10 types)        │
│ signal_data (text, JSON)            │
│ call_type (enum: video/audio)       │
│ is_read (tinyint, 0/1)              │
│ created_at (datetime)               │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ MESSAGES TABLE                      │
├─────────────────────────────────────┤
│ id (int, PK, AUTO_INCREMENT)        │
│ from_user_id (int, FK → users.id)   │
│ to_user_id (int, FK → users.id)     │
│ message (text)                      │
│ is_read (tinyint, 0/1)              │
│ created_at (datetime)               │
└─────────────────────────────────────┘
```
