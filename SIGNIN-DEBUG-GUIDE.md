# Sign-In Error 500 Debugging Guide

## Quick Access
**Debug Page URL:** `http://localhost/Gatherly-EMS_2025/debug-signin.php`  
**Live URL:** `http://c2lkis.com/debug-signin.php`

## What This Tool Does

The debug page provides:
1. ✅ PHP configuration checks
2. ✅ File permission verification
3. ✅ Database connection testing
4. ✅ Session configuration review
5. ✅ Live sign-in testing
6. ✅ Error log analysis
7. ✅ Troubleshooting recommendations

## How to Use

### Step 1: Access the Debug Page
Open your browser and navigate to:
```
http://localhost/Gatherly-EMS_2025/debug-signin.php
```

### Step 2: Review Each Section

#### 1. PHP Configuration
- Check if all required extensions are loaded (mysqli, pdo, pdo_mysql)
- Verify PHP version compatibility (8.0+)
- Ensure error logging is enabled

#### 2. File Permissions
- All listed files should exist and be readable
- Permissions should be at least 644 for files

#### 3. Database Connection
- Should show "Database connection successful"
- Should list the users table structure
- Should show total user count

#### 4. Session Configuration
- Session should be "Active"
- Save path should be writable

#### 5. Test Sign-In
- Enter actual user credentials from your database
- Click "Test Sign-In" to see detailed authentication flow
- This shows exactly where the login process fails

#### 6. Error Logs
- Review recent PHP errors
- Look for signin-related failures

### Step 3: Test Sign-In Process

Use the test form at the bottom:
1. Enter a valid email from your database
2. Enter the password
3. Click "Test Sign-In"
4. Review the detailed output

The test will show:
- ✓ Database connection status
- ✓ User lookup results
- ✓ Password verification status
- ✓ What the redirect path would be

## Common Issues & Solutions

### Issue 1: Database Connection Failed
**Symptoms:** Red error in section 3
**Solution:**
1. Check `config/database.php` file exists
2. Verify database credentials (host, user, password, database name)
3. Ensure MySQL/MariaDB is running: `sudo /opt/lampp/lampp status`
4. Test connection manually: `mysql -u root -p`

### Issue 2: Users Table Not Found
**Symptoms:** "Users table does not exist" error
**Solution:**
1. Import database: `mysql -u root -p gatherly_db < db/sad_db.sql`
2. Or create the database schema manually

### Issue 3: No User Found
**Symptoms:** "No user found with this email" in test results
**Solution:**
1. Check available users listed in the debug output
2. Verify email spelling is correct
3. Create test user if needed

### Issue 4: Password Verification Failed
**Symptoms:** "Password verification failed" error
**Solution:**
1. Passwords are case-sensitive
2. Ensure password was hashed with `password_hash()` when created
3. Try resetting password in database

### Issue 5: File Not Found Errors
**Symptoms:** Files missing in section 2
**Solution:**
1. Verify file paths are correct
2. Check file permissions: `ls -la /opt/lampp/htdocs/Gatherly-EMS_2025/`
3. Ensure files weren't accidentally deleted

### Issue 6: PHP Extensions Missing
**Symptoms:** Red "Not Loaded" in section 1
**Solution:**
1. Install missing extensions
2. Restart Apache: `sudo /opt/lampp/lampp restart`

## Manual Debugging Commands

### Check Apache Error Logs (Real-time)
```bash
sudo tail -f /opt/lampp/logs/error_log
```

### Check Database Connection
```bash
mysql -u root -p
USE gatherly_db;
SHOW TABLES;
SELECT email, role FROM users LIMIT 5;
```

### Check File Permissions
```bash
ls -la /opt/lampp/htdocs/Gatherly-EMS_2025/src/services/
ls -la /opt/lampp/htdocs/Gatherly-EMS_2025/config/
```

### Restart XAMPP Services
```bash
sudo /opt/lampp/lampp restart
```

### Check PHP Configuration
```bash
/opt/lampp/bin/php -i | grep mysqli
/opt/lampp/bin/php -i | grep pdo
```

## Next Steps After Finding the Issue

### If Database Connection Failed:
1. Update `config/database.php` with correct credentials
2. Restart Apache
3. Test again

### If Password Issues:
1. Reset user password in database:
```sql
UPDATE users 
SET password = '$2y$10$YourNewHashedPasswordHere' 
WHERE email = 'user@example.com';
```

### If File Permission Issues:
```bash
sudo chmod 644 /opt/lampp/htdocs/Gatherly-EMS_2025/src/services/*.php
sudo chmod 644 /opt/lampp/htdocs/Gatherly-EMS_2025/config/*.php
```

### If Session Issues:
```bash
sudo chmod 777 /opt/lampp/temp/
```

## Prevention Tips

1. **Always check error logs first** - They usually contain the exact error
2. **Test database connection** - Use debug page before deploying
3. **Verify file permissions** - Especially after git pull/clone
4. **Keep credentials updated** - Sync config files across environments
5. **Use version control** - Track changes to critical files

## Additional Resources

- **Apache Error Log:** `/opt/lampp/logs/error_log`
- **PHP Error Log:** Check `php.ini` for custom location
- **Database Config:** `/opt/lampp/htdocs/Gatherly-EMS_2025/config/database.php`
- **Sign-In Handler:** `/opt/lampp/htdocs/Gatherly-EMS_2025/src/services/signin-handler.php`

## Support

If issues persist after following this guide:
1. Check all sections of the debug page carefully
2. Review error logs for specific PHP errors
3. Verify database credentials and connection
4. Test with a fresh browser session (clear cache/cookies)

---

**Debug Page Created:** November 13, 2025  
**Last Updated:** November 13, 2025
