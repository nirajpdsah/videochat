# 🔧 Call Request Error - Troubleshooting Guide

## Error: "Failed to send call request. Please try again"

This error usually means one of these issues:

---

## ✅ Solution 1: Update Database Schema (MOST COMMON)

The database `signals` table needs to support `'call-request'` in the `signal_type` column.

### Check if it's updated:

1. **Go to Railway MySQL Console**
2. **Run this query:**
   ```sql
   SHOW COLUMNS FROM signals WHERE Field = 'signal_type';
   ```

3. **Check the output:**
   - ✅ **Good:** Should show `ENUM('offer','answer','ice-candidate','call-request')`
   - ❌ **Bad:** Only shows `ENUM('offer','answer','ice-candidate')`

### Fix it:

**Run this SQL in Railway MySQL Console:**
```sql
ALTER TABLE signals 
MODIFY signal_type ENUM('offer', 'answer', 'ice-candidate', 'call-request') NOT NULL;
```

**Or use the migration file:**
- Run `database_update.sql` in Railway MySQL console

---

## ✅ Solution 2: Test the API Directly

I've created a test file to help debug:

1. **Deploy the new files** (including `test_call_request.php`)
2. **Visit:** `your-app.railway.app/test_call_request.php`
3. **This will show you:**
   - If database schema is correct
   - If API is working
   - Actual error messages
   - Recent signals

---

## ✅ Solution 3: Check Browser Console

1. **Open browser Developer Tools** (F12)
2. **Go to Console tab**
3. **Try making a call**
4. **Look for error messages:**
   - Red error messages will show the actual problem
   - Check Network tab to see API response

---

## ✅ Solution 4: Check API Response

1. **Open browser Developer Tools** (F12)
2. **Go to Network tab**
3. **Try making a call**
4. **Click on `send_call_request.php` request**
5. **Check Response tab:**
   - Should show JSON with `success: true` or `success: false`
   - Error message will tell you what's wrong

---

## Common Error Messages & Fixes

### Error: "Invalid signal type"
**Problem:** Database doesn't have 'call-request' in ENUM
**Fix:** Run Solution 1 above

### Error: "User not found"
**Problem:** The user ID doesn't exist
**Fix:** Make sure you're calling a valid user

### Error: "User is busy"
**Problem:** Target user is already on a call
**Fix:** Wait for them to finish or try another user

### Error: "Failed to send call request: [SQL Error]"
**Problem:** Database query failed
**Fix:** 
- Check database connection
- Verify signals table exists
- Check Railway logs for database errors

### Error: Network error / CORS error
**Problem:** API endpoint not accessible
**Fix:**
- Check file exists: `api/send_call_request.php`
- Verify file permissions
- Check Railway deployment logs

---

## Step-by-Step Debugging

### Step 1: Verify Database Schema
```sql
-- Run in Railway MySQL Console
SHOW COLUMNS FROM signals WHERE Field = 'signal_type';
```

If `call-request` is missing, run:
```sql
ALTER TABLE signals 
MODIFY signal_type ENUM('offer', 'answer', 'ice-candidate', 'call-request') NOT NULL;
```

### Step 2: Test API Endpoint
Visit: `your-app.railway.app/test_call_request.php`

This will:
- ✅ Check database schema
- ✅ Show available users
- ✅ Test the API
- ✅ Show recent signals

### Step 3: Check Browser Console
1. Open Developer Tools (F12)
2. Go to Console
3. Try making a call
4. Look for errors

### Step 4: Check Network Requests
1. Open Developer Tools (F12)
2. Go to Network tab
3. Try making a call
4. Find `send_call_request.php` request
5. Check Status and Response

### Step 5: Check Railway Logs
1. Go to Railway dashboard
2. Click on your Web Service
3. Go to "Deployments" tab
4. Check logs for PHP errors

---

## Quick Fix Checklist

- [ ] Database schema updated (run `database_update.sql`)
- [ ] Files deployed to Railway
- [ ] Browser cache cleared (Ctrl+Shift+R)
- [ ] Test with `test_call_request.php`
- [ ] Check browser console for errors
- [ ] Verify both users are logged in
- [ ] Check Railway logs for errors

---

## Still Not Working?

1. **Share the error message** from browser console
2. **Share the API response** from Network tab
3. **Check Railway logs** for PHP errors
4. **Verify database** has been updated

The test file (`test_call_request.php`) will help identify the exact issue!

---

## Expected Behavior After Fix

1. ✅ User A clicks "Call"
2. ✅ Call request sent to User B
3. ✅ User B sees "Incoming Call" modal
4. ✅ User B clicks "Accept"
5. ✅ Both users connect via WebRTC

---

**Most likely fix:** Update your database schema with Solution 1! 🎯

