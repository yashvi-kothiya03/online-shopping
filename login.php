<?php

@include 'config.php';
@include 'email_functions.php';

if (!($conn instanceof mysqli)) {
    die('database connection is not available');
}

$message = [];
$active_tab = 'login';

$showOtp = false;
$prefillEmail = '';
if (isset($_GET['tab']) && in_array($_GET['tab'], ['login', 'otp', 'register'])) {
    $active_tab = $_GET['tab'];
}
if (isset($_GET['showOtp']) && $_GET['showOtp']) {
    $showOtp = true;
    $active_tab = 'otp';
}
if (isset($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    $prefillEmail = mysqli_real_escape_string($conn, $_GET['email']);
    $showOtp = true;
    $active_tab = 'otp';
}
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $message[] = 'registered successfully! please login now.';
}


// password login
if(isset($_POST['login_submit'])){

   $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $email = mysqli_real_escape_string($conn, $filter_email);
   $filter_pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);
   $pass = mysqli_real_escape_string($conn, ($filter_pass));

   $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' AND password = '$pass'") or die('query failed');


   if(mysqli_num_rows($select_users) > 0){
      
      $row = mysqli_fetch_assoc($select_users);
        $account_type = strtolower(trim((string)($row['user_type'] ?? 'user')));

        if($account_type == 'admin'){
         @include 'session_admin.php';

         $_SESSION['admin_name'] = $row['name'];
         $_SESSION['admin_email'] = $row['email'];
         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');
         exit;

    }elseif($account_type == 'user'){
         @include 'session_client.php';

         $_SESSION['user_name'] = $row['name'];
         $_SESSION['user_email'] = $row['email'];
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
         exit;

    }elseif($account_type == 'seller'){
         @include 'session_client.php';

         $_SESSION['seller_name'] = $row['name'];
         $_SESSION['seller_email'] = $row['email'];
         $_SESSION['seller_id'] = $row['id'];
         header('location:seller_dashboard.php');
         exit;

      }else{
         $message[] = 'no user found!';
      }

   }else{
      $message[] = 'incorrect email or password!';
        $active_tab = 'login';
   }

}

// register request
if(isset($_POST['register_submit'])){

    $filter_name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $name = mysqli_real_escape_string($conn, $filter_name);
    $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $email = mysqli_real_escape_string($conn, $filter_email);
    $filter_pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);
    $pass = mysqli_real_escape_string($conn, ($filter_pass));
    $filter_cpass = filter_var($_POST['cpass'], FILTER_SANITIZE_STRING);
    $cpass = mysqli_real_escape_string($conn, ($filter_cpass));
    $user_type = isset($_POST['user_type']) ? strtolower(trim(mysqli_real_escape_string($conn, $_POST['user_type']))) : 'user';
    if(!in_array($user_type, ['user', 'seller'], true)){
        $user_type = 'user';
    }

    $select_users = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'") or die('query failed');

    if(mysqli_num_rows($select_users) > 0){
        $message[] = 'user already exist!';
        $active_tab = 'register';
    }else{
        if($pass != $cpass){
            $message[] = 'confirm password not matched!';
            $active_tab = 'register';
        }else{
            mysqli_query($conn, "INSERT INTO `users`(name, email, password, user_type) VALUES('$name', '$email', '$pass', '$user_type')") or die('query failed');
            header('location:login.php?registered=1');
            exit;
        }
    }

}

// OTP send request
if(isset($_POST['send_otp'])){
    $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $email = mysqli_real_escape_string($conn, $filter_email);

    $check = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email'") or die('query failed');
    if(mysqli_num_rows($check) > 0){
        $otp = generateOTP();
        mysqli_query($conn, "UPDATE `users` SET login_otp = '$otp', otp_created_at = NOW() WHERE email = '$email'") or die('query failed');
        if(sendLoginOTPEmail($email, $otp)){
            $message[] = 'OTP sent to your email.';
            $showOtp = true;
            $active_tab = 'otp';
        } else {
            $message[] = 'OTP generated. ' . getOTPDisplayMessage($otp);
            $showOtp = true;
            $active_tab = 'otp';
        }
    } else {
        $message[] = 'Email not registered.';
        $showOtp = true;
        $active_tab = 'otp';
    }
}
// OTP verification
if(isset($_POST['verify_otp'])){
    $filter_email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $email = mysqli_real_escape_string($conn, $filter_email);
    $otp_input = mysqli_real_escape_string($conn, $_POST['otp']);

    $res = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '$email' AND login_otp = '$otp_input'") or die('query failed');
    if(mysqli_num_rows($res) > 0){
        $row = mysqli_fetch_assoc($res);
        $account_type = strtolower(trim((string)($row['user_type'] ?? 'user')));
        $diff = (time() - strtotime($row['otp_created_at']))/60;
        if($diff <= 10){
            // login successful
            mysqli_query($conn, "UPDATE `users` SET login_otp = NULL, otp_created_at = NULL WHERE id = '{$row['id']}'");
            if($account_type == 'admin'){
                @include 'session_admin.php';
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_email'] = $row['email'];
                $_SESSION['admin_id'] = $row['id'];
                header('location:admin_page.php');
                exit;
            } elseif($account_type == 'seller'){
                @include 'session_client.php';
                $_SESSION['seller_name'] = $row['name'];
                $_SESSION['seller_email'] = $row['email'];
                $_SESSION['seller_id'] = $row['id'];
                header('location:seller_dashboard.php');
                exit;
            } else {
                @include 'session_client.php';
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_id'] = $row['id'];
                header('location:home.php');
                exit;
            }
        } else {
            $message[] = 'OTP expired. Please request a new one.';
            $active_tab = 'otp';
        }
    } else {
        $message[] = 'Invalid OTP. Please try again.';
        $active_tab = 'otp';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <!-- <link rel="stylesheet" href="css/style.css"> -->
   <link rel="stylesheet" href="css/login.css">


</head>
<body>

<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section>
    <div class="ring">
        <i style="--clr:#00ff0a;"></i>
        <i style="--clr:#ff0057;"></i>
        <i style="--clr:#fffd44;"></i>
        <div class="login">
            <h2>Account Access</h2>
            <div class="auth-tabs">
                <a href="#" id="use-password" class="tab-btn">Login</a>
                <a href="#" id="use-otp" class="tab-btn">OTP</a>
                <a href="#" id="use-register" class="tab-btn">Register</a>
            </div>

            <!-- password form -->
            <form id="password-form" action="" method="post" style="display:<?php echo $active_tab === 'login' ? 'block' : 'none'; ?>;">
                <div class="inputBx">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="inputBx">
                    <input type="password" name="pass" placeholder="Password" required>
                </div>
                <div class="inputBx">
                    <input type="submit" name="login_submit" value="login now">
                </div>
                <div class="links">
                    <p>don't have an account? <a href="#" id="switch-register">register</a></p>
                </div>
            </form>

            <!-- OTP form -->
            <form id="otp-form" action="" method="post" style="display:<?php echo $active_tab === 'otp' ? 'block' : 'none'; ?>;">
                <div class="inputBx">
                    <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($prefillEmail); ?>">
                </div>
                <div class="inputBx">
                    <input type="submit" name="send_otp" value="Send OTP">
                </div>
                <div class="links">
                    <p>after receiving OTP, verify below</p>
                </div>
            </form>

            <!-- OTP verify subform -->
            <form id="otp-verify-form" action="" method="post" style="display:<?php echo $active_tab === 'otp' ? 'block' : 'none'; ?>;">
                <div class="inputBx">
                    <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($prefillEmail); ?>">
                </div>
                <div class="inputBx">
                    <input type="text" name="otp" placeholder="Enter OTP" required>
                </div>
                <div class="inputBx">
                    <input type="submit" name="verify_otp" value="Verify OTP">
                </div>
            </form>

            <!-- register form -->
            <form id="register-form" action="" method="post" style="display:<?php echo $active_tab === 'register' ? 'block' : 'none'; ?>;">
                <div class="inputBx">
                    <input type="text" name="name" placeholder="Username" required>
                </div>
                <div class="inputBx">
                    <select name="user_type" required style="width:100%; padding:14px 16px; border: 1px solid rgba(15, 23, 42, 0.15); border-radius: 12px; background: #f8fafc;">
                        <option value="user" selected>Customer</option>
                        <option value="seller">Seller</option>
                    </select>
                </div>
                <div class="inputBx">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="inputBx">
                    <input type="password" name="pass" placeholder="Password" required>
                </div>
                <div class="inputBx">
                    <input type="password" name="cpass" placeholder="Confirm Password" required>
                </div>
                <div class="inputBx">
                    <input type="submit" name="register_submit" value="register now">
                </div>
                <div class="links">
                    <p>already have an account? <a href="#" id="switch-login">login</a></p>
                </div>
            </form>
        </div>
    </div>
</section>
    <script>
        const usePassword = document.getElementById('use-password');
        const useOtp = document.getElementById('use-otp');
        const useRegister = document.getElementById('use-register');
        const switchRegister = document.getElementById('switch-register');
        const switchLogin = document.getElementById('switch-login');
        const pwdForm = document.getElementById('password-form');
        const otpForm = document.getElementById('otp-form');
        const otpVerify = document.getElementById('otp-verify-form');
        const registerForm = document.getElementById('register-form');

        function showTab(tab) {
            pwdForm.style.display = tab === 'login' ? '' : 'none';
            otpForm.style.display = tab === 'otp' ? '' : 'none';
            otpVerify.style.display = tab === 'otp' ? '' : 'none';
            registerForm.style.display = tab === 'register' ? '' : 'none';

            usePassword.classList.toggle('active', tab === 'login');
            useOtp.classList.toggle('active', tab === 'otp');
            useRegister.classList.toggle('active', tab === 'register');
        }

        usePassword.addEventListener('click', e => {
            e.preventDefault();
            showTab('login');
        });
        useOtp.addEventListener('click', e => {
            e.preventDefault();
            showTab('otp');
        });
        useRegister.addEventListener('click', e => {
            e.preventDefault();
            showTab('register');
        });
        if (switchRegister) {
            switchRegister.addEventListener('click', e => {
                e.preventDefault();
                showTab('register');
            });
        }
        if (switchLogin) {
            switchLogin.addEventListener('click', e => {
                e.preventDefault();
                showTab('login');
            });
        }

        showTab('<?php echo $active_tab; ?>');
    </script>


</body>
</html>