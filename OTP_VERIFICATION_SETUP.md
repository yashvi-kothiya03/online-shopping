# Order Email Verification with OTP - Setup Guide

## Overview
This implementation adds email notification with OTP (One-Time Password) verification when an order is successfully placed. Customers will receive an email with order details and a 6-digit OTP code that they must verify to confirm their order.

## Features
✓ Automatic OTP generation on order placement  
✓ HTML-formatted confirmation emails with order details  
✓ 10-minute OTP expiration for security  
✓ OTP resend functionality  
✓ Order verification status tracking  
✓ User-friendly verification interface  

---

## Installation Steps

### 1. Update Database Schema
Execute the SQL script to add OTP columns to the `orders` table:

```sql
-- Method 1: Using phpMyAdmin
1. Open your database in phpMyAdmin
2. Go to the "SQL" tab
3. Paste the contents of: add_otp_columns.sql
4. Click "Go"

-- OR Method 2: Using MySQL Command Line
mysql -u root -p ceramic < add_otp_columns.sql
```

**Columns added to `orders` table:**
- `otp` (VARCHAR 6) - Stores the 6-digit OTP
- `otp_verified` (TINYINT 1) - Tracks if email is verified (0 = pending, 1 = verified)
- `otp_created_at` (TIMESTAMP) - Records when OTP was generated

### 2. Files Created/Modified

**New Files Created:**
- `email_functions.php` - Email sending and OTP verification functions
- `verify_order_otp.php` - OTP verification page for customers
- `add_otp_columns.sql` - Database schema update script

**Files Modified:**
- `checkout.php` - Integrated email sending with order placement
- `orders.php` - Added verification status display

### 3. Email Configuration

#### For Local Testing (XAMPP):
By default, PHP uses the system's mail configuration. For testing:

**On Windows (XAMPP):**
1. Install and configure a local mail server (e.g., Papercut or similar)
2. OR use the fallback message display that shows the OTP in the success message

**For Production (Live Server):**
You can upgrade to use PHPMailer for more reliable email sending:

```php
// Example: Update email_functions.php to use PHPMailer if needed
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
```

---

## How It Works

### Order Placement Flow:
1. Customer fills in order details on `checkout.php`
2. ✓ Order is created in database
3. ✓ 6-digit OTP is generated
4. ✓ OTP is stored in the database
5. ✓ Confirmation email sent with OTP
6. ✓ Customer redirected with success message

### Verification Flow:
1. Customer receives email with order details and OTP
2. Customer visits `verify_order_otp.php` (from order page)
3. Customer enters the OTP from email
4. System verifies OTP (must be within 10 minutes)
5. Order marked as verified
6. Confirmation message displayed

---

## Usage Guide

### For Customers:

#### After Placing Order:
1. **Checkout Page**: Fill all required information and click "Place Order"
2. **Confirmation Message**: See success message with instruction to check email
3. **Email**: Check inbox for confirmation email with:
   - Order ID and details
   - 6-digit OTP code
   - Instructions for verification
4. **Verification**: 
   - Go to "Your Orders" in the customer account
   - Click "Verify Email" button on unverified orders
   - Enter the 6-digit OTP
   - Click "Verify OTP"

#### If OTP Expires:
- Click "Resend OTP" button on verification page
- New OTP will be sent to registered email
- Use new OTP within 10 minutes

### For Admins:

#### View Verification Status:
- Go to `admin_orders.php` to see which orders are verified
- Verified orders show: **✓ Verified** (green)
- Pending orders show: **⏳ Pending Verification** (orange)

#### Resend OTP:
- Customers can resend OTP from `verify_order_otp.php`
- System generates new OTP and updates the database

---

## Function Reference

### email_functions.php

#### 1. `generateOTP($length = 6)`
Generates a random OTP of specified length (default: 6 digits)
```php
$otp = generateOTP();  // Returns: "123456"
```

#### 2. `sendOrderConfirmationEmail($email, $name, $order_id, $otp, $order_details, $total_price)`
Sends HTML-formatted confirmation email with order details and OTP
```php
sendOrderConfirmationEmail(
    'customer@example.com',
    'John Doe',
    123,
    '456789',
    'Product1, Product2',
    5000
);
```

#### 3. `verifyOTP($conn, $order_id, $otp)`
Verifies OTP against database (checks expiration, validity)
```php
if (verifyOTP($conn, 123, '456789')) {
    echo "OTP Verified!";
}
```

#### 4. `resendOTP($conn, $order_id, $email, $name)`
Generates new OTP and sends confirmation email
```php
resendOTP($conn, 123, 'customer@example.com', 'John Doe');
```

---

## Database Schema

### Updated Orders Table:
```sql
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `otp` VARCHAR(6),
  `otp_verified` TINYINT(1) DEFAULT 0,
  `otp_created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Email Template

The email sent to customers includes:
- **Header**: Order confirmation message
- **Order Details**: Order ID, total amount, items
- **OTP Section**: 6-digit code in prominent display
- **Instructions**: How to verify the order
- **Security Notice**: Warning not to share OTP
- **Footer**: Company info and copyright

---

## Security Features

✓ **10-minute OTP expiration** - OTP automatically expires  
✓ **Random generation** - Cryptographically random OTP  
✓ **Database validation** - OTP verified against stored value  
✓ **Single-use verification** - Cannot verify same OTP twice  
✓ **Email validation** - Email address verified during order placement  

---

## Troubleshooting

### Emails Not Sending?

1. **Check XAMPP Configuration:**
   ```
   - Open php.ini
   - Look for: [mail function]
   - Ensure sendmail_path or SMTP is configured
   ```

2. **For Testing:**
   - Check browser console for JavaScript errors
   - Look at PHP error logs in `xampp/php/logs/`
   - Verify email address format is correct

3. **For Production:**
   - Use PHPMailer with SMTP credentials
   - Contact hosting provider for mail settings
   - Enable "Less Secure Apps" if using Gmail SMTP

### OTP Verification Issues?

1. **OTP Expired:**
   - Click "Resend OTP" to get a new one
   - OTP valid for only 10 minutes

2. **Wrong OTP:**
   - Check email again for correct OTP
   - Make sure there's no extra space

3. **Order Not Found:**
   - Verify order ID is correct
   - Ensure you're logged into correct account

---

## Customization

### Change OTP Length:
In `checkout.php`, change:
```php
$otp = generateOTP();  // Change parameter
$otp = generateOTP(8);  // 8-digit OTP
```

### Change OTP Expiration Time:
In `email_functions.php`, function `verifyOTP()`, change:
```php
if ($time_diff <= 10) {  // 10 minutes
// Change to:
if ($time_diff <= 30) {  // 30 minutes
```

### Customize Email Template:
Edit the HTML in `sendOrderConfirmationEmail()` function to match your branding.

---

## Testing Checklist

- [ ] Database columns added successfully
- [ ] `email_functions.php` created
- [ ] `verify_order_otp.php` created
- [ ] `checkout.php` modified and shows OTP in fallback message
- [ ] `orders.php` displays verification status
- [ ] Place test order and verify functionality
- [ ] Test resend OTP feature
- [ ] Test expired OTP scenario
- [ ] Test with invalid OTP

---

## Support & Customization

For any issues or customizations needed:
1. Check the troubleshooting section above
2. Verify all files are in correct locations
3. Ensure database schema is updated properly
4. Check PHP error logs for detailed errors
5. Test with a simple order first

---

## Notes

- Email sending requires proper server configuration
- OTP is valid for 10 minutes from generation
- Each resend generates a new OTP
- Verification is optional but recommended for order processing
- All personal data is validated and sanitized

---

**Version**: 1.0  
**Last Updated**: 2026-03-10  
**Compatible with**: PHP 7.4+, MySQL 5.6+
