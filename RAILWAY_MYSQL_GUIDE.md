# 🔍 How to Add MySQL to Railway - Visual Guide

## Step-by-Step Instructions

### Step 1: Open Your Railway Project
1. Go to [railway.app](https://railway.app)
2. Log in to your account
3. You should see your project dashboard

### Step 2: Find the "New" Button
Look for one of these options:

**Option A: In the Project Dashboard**
- You'll see your **Web Service** (your PHP app)
- Look for a **"+ New"** button (usually at the top right or bottom of the services list)
- It might say **"+ New Service"** or just **"+ New"**

**Option B: In the Services List**
- On the left sidebar or main area, you'll see your services
- There should be a **"+ New"** or **"Add Service"** button
- It might be a **"+" icon** or a button

### Step 3: Click "New" and Select Database
1. Click the **"+ New"** button
2. A menu or modal will appear with options like:
   - **"Database"**
   - **"Empty Service"**
   - **"GitHub Repo"**
   - **"Template"**

3. Click on **"Database"** or look for **"MySQL"** option

### Step 4: Select MySQL
After clicking "Database", you'll see options:
- **MySQL** ← Click this one
- PostgreSQL
- MongoDB
- Redis

Click **"MySQL"**

### Step 5: MySQL Service Created
Railway will automatically:
- Create a MySQL database
- Set up environment variables
- Start the MySQL service

You should now see:
- Your **Web Service** (PHP app)
- A new **MySQL** service

---

## 🎯 Alternative: If You Don't See "New" Button

### Check Your View
1. Make sure you're in the **Project** view (not account settings)
2. Look for tabs: **"Services"**, **"Deployments"**, **"Settings"**
3. Click on **"Services"** tab if available

### Try These Locations:

**Location 1: Top Right Corner**
```
┌─────────────────────────────────┐
│  Project Name          [+ New]  │
└─────────────────────────────────┘
```

**Location 2: Services List**
```
Services:
┌─────────────────┐
│  Web Service    │
└─────────────────┘
┌─────────────────┐
│  [+ New]        │  ← Click here
└─────────────────┘
```

**Location 3: Sidebar**
```
Dashboard
Services
  └─ Web Service
  └─ [+ New Service]  ← Click here
```

**Location 4: Empty State**
If you only have a Web Service and nothing else:
- Look for a message like "Add a service" or "Add database"
- Click on that

---

## 📸 What to Look For

### The Button Might Look Like:
- **"+ New"** (text button)
- **"+"** (icon only)
- **"Add Service"**
- **"New Service"**
- **"Add Database"**

### The Menu Options:
When you click, you should see:
```
┌─────────────────────┐
│  Database           │  ← Click this
│  Empty Service      │
│  GitHub Repo        │
│  Template           │
└─────────────────────┘
```

Then:
```
┌─────────────────────┐
│  MySQL              │  ← Click this
│  PostgreSQL         │
│  MongoDB            │
│  Redis              │
└─────────────────────┘
```

---

## 🔧 If Still Can't Find It

### Check Your Railway Plan
1. Go to your **Account Settings**
2. Check your **Plan** (Free tier should work)
3. Free tier supports MySQL

### Try This Alternative Method:

**Method 1: Via Project Settings**
1. Click on your **Project name** (top left)
2. Look for **"Services"** or **"Add Service"**
3. Click there

**Method 2: Via Web Service**
1. Click on your **Web Service**
2. Look for **"Add Service"** or **"Connect Database"** option
3. Some versions have a database connection option

**Method 3: Direct URL**
Try going directly to:
```
https://railway.app/project/YOUR_PROJECT_ID/services/new
```
(Replace YOUR_PROJECT_ID with your actual project ID)

---

## ✅ After MySQL is Added

Once MySQL is added, you should see:

```
Your Project
├── Web Service (PHP app)
└── MySQL (database)
```

### Next Steps:
1. Click on the **MySQL** service
2. Go to **"Data"** tab
3. Click **"Open MySQL Console"** or **"Query"**
4. Paste your `database.sql` content
5. Run it

---

## 🆘 Still Having Issues?

### Screenshot Help
If you can share a screenshot of your Railway dashboard, I can point out exactly where to click!

### Common Issues:

**Issue 1: "New" button is grayed out**
- Solution: Make sure you're the project owner/admin
- Check if you have permissions

**Issue 2: Only see "Empty Service"**
- Solution: Click "Empty Service" first
- Then you can configure it as MySQL later
- Or look for "Database" option in the empty service

**Issue 3: Different Railway UI**
- Railway updates their UI sometimes
- Look for any button that says "Add", "New", or "+"
- Check the left sidebar for service options

---

## 📞 Quick Alternative: Railway CLI

If the web UI is confusing, you can use Railway CLI:

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link to your project
railway link

# Add MySQL
railway add mysql
```

This will add MySQL via command line!

---

## 🎯 Visual Reference

Your Railway dashboard should look something like this:

```
┌─────────────────────────────────────────────┐
│  My VideoChat Project          [+ New]     │ ← Look here
├─────────────────────────────────────────────┤
│                                             │
│  Services:                                   │
│  ┌──────────────────────┐                  │
│  │  Web Service         │                  │
│  │  (PHP app)          │                  │
│  └──────────────────────┘                  │
│                                             │
│  ┌──────────────────────┐                  │
│  │  [+ New Service]     │  ← Or here      │
│  └──────────────────────┘                  │
│                                             │
└─────────────────────────────────────────────┘
```

After clicking "+ New":
```
┌─────────────────────┐
│  What do you want   │
│  to create?         │
├─────────────────────┤
│  Database      →    │ ← Click this
│  Empty Service      │
│  GitHub Repo        │
└─────────────────────┘
```

Then:
```
┌─────────────────────┐
│  Select Database    │
├─────────────────────┤
│  MySQL         →    │ ← Click this
│  PostgreSQL         │
│  MongoDB            │
└─────────────────────┘
```

---

**Need more help?** Describe what you see on your screen and I'll guide you further!

