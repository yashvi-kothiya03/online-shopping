<?php

@include 'config.php';

@include 'session_client.php';

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

$error_message = '';
$success_message = '';

if (isset($_POST['send'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $number = mysqli_real_escape_string($conn, trim($_POST['number']));
    $msg = mysqli_real_escape_string($conn, trim($_POST['message']));

    // Validation
    if (empty($name) || strlen($name) < 3) {
        $error_message = 'Please enter a valid name (at least 3 characters).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (!preg_match('/^\d{10}$/', $number)) {
        $error_message = 'Please enter a valid 10-digit phone number.';
    } elseif (empty($msg) || strlen($msg) < 10) {
        $error_message = 'Please enter a message (at least 10 characters).';
    } else {
        // Check if message already sent
        $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name = '$name' AND email = '$email' AND number = '$number' AND message = '$msg'") or die('query failed');

        if (mysqli_num_rows($select_message) > 0) {
            $error_message = 'Message already sent!';
        } else {
            // Insert new message
            if (mysqli_query($conn, "INSERT INTO `message` (user_id, name, email, number, message) VALUES ('$user_id', '$name', '$email', '$number', '$msg')")) {
                $success_message = 'Message sent successfully!';
            } else {
                $error_message = 'Failed to send message. Please try again.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact</title>

   <!-- Font Awesome CDN link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- Custom admin CSS file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
       .message-box {
           padding: 10px;
           border-radius: 5px;
           margin-top: 15px;
           display: inline-block;
           width: 100%;
       }
       .error {
            font-size: 18px;
           background-color: #f8d7da;
           color: #721c24;
       }
       .success {
            font-size: 18px;
           background-color: #d4edda;
           color: #155724;
       }
   </style>
</head>
<body>
   
<?php @include 'header.php'; ?>

<section class="heading">
    <h3>Contact Us</h3>
    <p><a href="home.php">Home</a> / Contact</p>
</section>

<section class="contact">

    <form action="" method="POST">
        <h3>Send Us a Message!</h3>

        <?php if ($error_message): ?>
            <div class="message-box error"><?php echo $error_message; ?></div>
        <?php elseif ($success_message): ?>
            <div class="message-box success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <input type="text" name="name" placeholder="Enter your name" class="box" required>
        <input type="email" name="email" placeholder="Enter your email" class="box" required>
        <input type="number" name="number" placeholder="Enter your number" class="box" required>
        <textarea name="message" class="box" placeholder="Enter your message" required cols="30" rows="10"></textarea>
        <input type="submit" value="Send Message" name="send" class="btn">
        
        
    </form>

</section>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
