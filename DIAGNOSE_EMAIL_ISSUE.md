# Email Not Reaching Inbox - Diagnostic Guide

## What We Know
✅ OTP is generating correctly (you saw: 013199)
✅ System shows fallback message - this means email function was called
❌ Email isn't arriving in ajudiyaharit75@gmail.com inbox

This means the SMTP connection is likely failing silently.

---

## Step 1: Run SMTP Connection Test

**Open in your browser:**
```
http://localhost/harit/ceramic/test_smtp_connection.php
```

This will show you detailed diagnostic information:
- ✅ or ✗ for each step of the SMTP connection
- Exact error messages  
- Whether authentication succeeds

**What to look for:**
- If you see `✓ Connection to smtp.gmail.com:587 successful` - network is OK
- If you see `✓ TLS encryption enabled` - STARTTLS works
- If you see `✗` anywhere - that's the problem we need to fix

---

## Step 2: Common Issues & Solutions

### Issue A: "Cannot connect to smtp.gmail.com:587"
**Cause:** Firewall or network issue
**Solution:**
1. Try from a different network (mobile hotspot if available)
2. If XAMPP running on Virtual Machine - check VM network settings
3. Temporarily disable Windows Firewall (just for testing)

### Issue B: "TLS upgrade failed"  
**Cause:** OpenSSL issue or XAMPP configuration
**Solution:**
1. Check PHP has OpenSSL enabled:
   - Open: `C:\xampp\php\php.ini`
   - Search for: `extension=openssl`
   - Remove semicolon if it's commented out: `;extension=openssl` → `extension=openssl`
   - Restart Apache in XAMPP Control Panel

### Issue C: "Authentication failed"
**Cause:** Wrong password or Gmail blocking the attempt
**Solution:**
1. Verify app password in Gmail:
   - Go to: https://myaccount.google.com/apppasswords
   - Make sure you're logged into ajudiyaharit75@gmail.com
   - Your app password should show (16 characters)
   - Copy the exact password (no spaces)
2. Update in email_functions.php:
   - Line 17: `define('SMTP_PASS', 'YOUR-16-CHAR-PASSWORD');`

### Issue D: Gmail showing "Less Secure" warning
**If you used regular password instead of app password:**
1. Go to: https://myaccount.google.com/apppasswords
2. Select "Mail" app and "Windows Computer" device
3. Gmail generates a 16-character app password
4. Use ONLY the app password, not your Gmail password
5. Update email_functions.php with this app password

---

## Step 3: Alternative Solution (If SMTP Still Fails)

If SMTP connection has network/firewall issues, use **PHP mail() with a Windows SMTP service:**

### Option A: Use Papercut SMTP (Recommended for Testing)
1. Download: https://github.com/ChangemakerStudios/Papercut-SMTP/releases
2. Run Papercut - it acts as a local SMTP server
3. Edit email_functions.php line 19:
   ```php
   define('USE_SMTP', false);  // Use PHP mail() instead
   ```
4. Now emails go to local service instead of Gmail

### Option B: Install hMailServer (More Professional)
Similar to Papercut but more powerful for production use.

### Option C: Use AWS SES or SendGrid (Production)
More reliable than Gmail, better for production environments.

---

## Step 4: Quick Fix - Enable PHP mail() as Fallback

Even if SMTP works, having PHP mail() as backup is good:

**Edit `C:\xampp\php\php.ini`:**

Find and update these lines:
```ini
SMTP = 127.0.0.1
smtp_port = 25
sendmail_from = noreply@ceramicstore.com
```

This configures PHP mail() to use local services.

---

## Step 5: Verify the Fix

After any changes:

1. **Restart XAMPP:**
   - Open XAMPP Control Panel
   - Click "Stop" on Apache and MySQL
   - Wait 5 seconds
   - Click "Start" on Apache and MySQL

2. **Test again:**
   - Run test_smtp_connection.php again
   - OR place a new test order
   - Check inbox

3. **Watch for email:**
   - Check inbox at ajudiyaharit75@gmail.com
   - Check spam/promotions folder
   - Should arrive within 1-5 seconds

---

## Expected Success Output

When working correctly, test_smtp_connection.php shows:

```
=== Gmail SMTP Connection Diagnostic ===

1. Configuration Check:
   Host: smtp.gmail.com
   Port: 587
   User: ajudiyaharit75@gmail.com
   Pass: Set (16 chars)

2. Network Connectivity Test:
   ✓ Connection to smtp.gmail.com:587 successful
   Server response: 220 smtp.gmail.com ESMTP ...

3. Full SMTP Protocol Test:
   → Sending EHLO...
      250-smtp.gmail.com at your service
      ... more lines ...
   → Sending STARTTLS...
      220 Ready to start TLS
   → Upgrading to TLS...
      ✓ TLS encryption enabled
   → Sending EHLO again (post-TLS)...
      ... auth info ...
   → Testing AUTH LOGIN...
      334 VXNlcm5hbWU6
   → Sending username...
      334 UGFzc3dvcmQ6
   → Sending password...
      235 2.7.0 Accepted

   ✓✓ AUTHENTICATION SUCCESSFUL! Email system should work!
```

---

## Debugging Steps (Advanced)

If still stuck, check XAMPP logs:

1. **PHP Error Log:**
   ```
   C:\xampp\php\logs\php_error_log
   ```
   Look for lines with "SMTP:" or "stream_socket"

2. **Apache Error Log:**
   ```
   C:\xampp\apache\logs\error.log
   ```

3. **MySQL Log:**
   ```
   C:\xampp\mysql\data\MACHINE_NAME.err
   ```

---

## Quick Reference: All Email Files

| File | Purpose | Status |
|------|---------|--------|
| email_functions.php | Core SMTP logic | ✅ Configured |
| checkout.php | Sends email on order | ✅ Configured |
| login.php | OTP login with email | ✅ Configured |
| verify_order_otp.php | Customer OTP verification | ✅ Configured |
| test_email.php | Simple email test | ✅ Created |
| test_smtp_connection.php | SMTP diagnostic | ✅ Created |

---

## Need Help?

**Share information from test_smtp_connection.php output** and I can:
1. Identify the exact failure point
2. Provide specific fix
3. Set up alternative email method if needed

The system is 99% done - just need to get past this SMTP connection!
