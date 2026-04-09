# Email Configuration Guide - Order Confirmation with OTP & Invoice

## Overview
Your email system is now configured to send professional order confirmation emails with:
- ✅ Order ID and details
- ✅ Complete order invoice
- ✅ 6-digit OTP code
- ✅ Customer information (phone, address, payment method)
- ✅ Professional HTML formatting

---

## Setup Instructions

### Method 1: Using Gmail SMTP (Recommended)

#### Step 1: Enable 2-Step Verification in Gmail

1. Go to [myaccount.google.com/security](https://myaccount.google.com/security)
2. Sign in to your Gmail account
3. In the left menu, click **Security**
4. Scroll down and enable **2-Step Verification**
5. Follow the prompts to complete setup

#### Step 2: Create an App Password

1. Go back to [myaccount.google.com/security](https://myaccount.google.com/security)
2. Click **App passwords** (appears after 2-Step Verification is enabled)
3. Select **Mail** and **Windows Computer** from dropdowns
4. Click **Generate**
5. Copy the 16-character password shown (without spaces)

#### Step 3: Configure in email_functions.php

Open `email_functions.php` and update these lines (around line 6-9):

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');      // Your Gmail address
define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');       // Your 16-char app password (without spaces)
define('USE_SMTP', true);                          // Change this to TRUE
```

**Example:**
```php
define('SMTP_USER', 'mystore@gmail.com');
define('SMTP_PASS', 'abcd efgh ijkl mnop');  // Remove spaces: 'abcdefghijklmnop'
define('USE_SMTP', true);
```

#### Step 4: Test Email Sending

1. Go to `checkout.php`
2. Place a test order
3. Check your email inbox for the order confirmation
4. Email should include all order details and OTP

---

### Method 2: Using PHP mail() Function (Alternative)

If you want to use the built-in PHP mail() function:

#### For XAMPP Windows:

1. **Install Sendmail Alternative:**
   - Download [Papercut](https://github.com/ChangemakerStudios/Papercut-SMTP) (free email testing tool)
   - Or configure XAMPP to use a local mail server

2. **Configure in email_functions.php:**
   ```php
   define('USE_SMTP', false);  // Keep this FALSE to use mail()
   ```

3. **Test the system**

---

### Method 3: Using Other Email Services

#### SendGrid (Free tier available)
1. Sign up at [sendgrid.com](https://sendgrid.com)
2. Create API key
3. Use SMTP credentials provided by SendGrid
4. Update `email_functions.php` with SendGrid SMTP details

#### Mailgun (Free tier available)
1. Sign up at [mailgun.com](https://mailgun.com)
2. Get SMTP credentials
3. Update `email_functions.php` with Mailgun SMTP details

#### AWS SES
1. Set up [Amazon SES](https://aws.amazon.com/ses/)
2. Verify email address
3. Use SMTP credentials

---

## Troubleshooting Email Issues

### Problem: "Email notification could not be sent"

**Solution 1: Check Configuration**
```php
// In email_functions.php, verify:
// 1. USE_SMTP is set correctly
// 2. SMTP_USER and SMTP_PASS are entered
// 3. No spaces in SMTP_PASS
```

**Solution 2: Check Gmail App Password**
- Make sure you used an "App Password" (16 characters)
- NOT your regular Gmail password
- Remove spaces: `xxxx xxxx xxxx xxxx` → `xxxxxxxxxxxxxxxx`

**Solution 3: Enable Less Secure Apps**
If using a regular Gmail password (not recommended):
1. Go to [myaccount.google.com/security](https://myaccount.google.com/security)
2. Search for "Less Secure Apps"
3. Turn ON "Allow less secure apps"

**Solution 4: Check Firewall**
- Your server firewall might block port 587 (SMTP)
- Contact your hosting provider if needed

---

## Email Template Contents

The order confirmation email now includes:

### Header Section:
- Thank you message
- Order number

### Order Information:
- Order ID
- Order date and time
- Customer phone number
- Customer email
- Delivery address
- Payment method (supports cash, card, PayPal, Paytm, and UPI QR‑code)

### Order Items:
- Complete list of products ordered
- Professional table format

### Invoice Section:
- Subtotal
- **Total Amount in large, prominent format**

### OTP Verification:
- **Large 6-digit OTP display**
- 10-minute validity reminder
- Security warning

### Next Steps:
- Clear numbered instructions for email verification
- How to find verification code on website

### Professional Footer:
- Contact links
- Copyright information

---

## Testing the Email System

### Test 1: Place an Order
1. Go to your ceramic store website
2. Add products to cart
3. Complete checkout form
4. Click "Place Order"

### Expected Results:
✓ Success message appears  
✓ Email received in 1-5 minutes  
✓ Email contains all order details  
✓ OTP code is clearly visible  

### Test 2: Verify Email
1. Go to "Your Orders"
2. Click "Verify Email" button
3. Copy OTP from email
4. Enter OTP on verification page
5. Should see "Email Verified!" confirmation

### Test 3: Resend OTP
1. Click "Resend OTP" button
2. New OTP should arrive in email
3. Old OTP becomes invalid
4. Use new OTP to verify

---

## Email Features

### ✅ What's Included:
- Professional HTML formatting
- Support for displaying a UPI QR code when customer selects that option at checkout
- Responsive design (mobile-friendly)
- Order invoice with all details
- Prominent OTP display
- Customer information
- Security warnings
- Clear next steps

### 🎯 Professional Design:
- Brand colors and styling
- Clean layout
- Easy to read
- Mobile-optimized
- Professional tone

### 🔒 Security Features:
- OTP warning message
- 10-minute expiration
- No sensitive data exposed
- SMTP encryption (TLS)

---

## Fallback Behavior

If email sending fails:
- Order is still created successfully
- OTP is still generated
- Order appears in customer account
- Customer can retrieve OTP from the fallback success message
- Customer can still verify email using the order ID

---

## Customization Options

### Change Email From Address

In `email_functions.php`, update this line:
```php
$from = SMTP_USER;  // Change SMTP_USER or set custom email
// Or hardcode:
$from = "orders@yourstore.com";
```

### Change Email Subject

In `checkout.php`, update:
```php
$subject = "Order Confirmation - Order #" . $order_id . " | Ceramic Store";
// To:
$subject = "Your Order #" . $order_id . " is Confirmed!";
```

### Customize Email Template

Edit the HTML in `sendOrderConfirmationEmail()` function to match your branding.

---

## Production Deployment

When deploying to production:

1. **Use Gmail SMTP or Professional Service**
   - Gmail: Reliable and free
   - SendGrid: Professional, better deliverability
   - AWS SES: Enterprise solution

2. **Set USE_SMTP to TRUE**
   ```php
   define('USE_SMTP', true);
   ```

3. **Verify Sending**
   - Place test order
   - Confirm email is received
   - Check email content shows all details

4. **Monitor Email Logs**
   - Keep track of failed emails
   - Monitor bounce rates
   - Adjust configuration if needed

5. **Set Up Reply Address**
   - Emails should have a valid reply-to address
   - Ensure your support team monitors that inbox

---

## FAQ

### Q: Why do I need an "App Password" instead of Gmail password?
A: Google requires this for security. App passwords are specifically for apps connecting to Gmail, not for direct login.

### Q: How long does email take to arrive?
A: Usually 1-5 minutes. Gmail to Gmail is almost instant (1-30 seconds).

### Q: What if customer doesn't receive the email?
A: Check spam/junk folder. If problem persists, contact your email provider for logs.

### Q: Can I change the OTP length?
A: Yes, in `email_functions.php`:
```php
function generateOTP($length = 8) {  // Change from 6 to 8
```

### Q: Can I change OTP expiration time?
A: Yes, in `verify_order_otp.php`:
```php
if ($time_diff <= 30) {  // Change from 10 to 30 minutes
```

---

## Support

If you need help:

1. **Check Configuration**: Verify SMTP settings in `email_functions.php`
2. **Check Logs**: Look for PHP errors in XAMPP logs
3. **Test Manually**: Try placing a test order
4. **Check Email**: Verify it's being sent (not just configuration issue)

---

**Version**: 1.0  
**Last Updated**: March 10, 2026  
**Email System**: Enhanced with Invoice & Professional Template
