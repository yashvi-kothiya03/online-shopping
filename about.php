<?php

@include 'config.php';

@include 'session_client.php';

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php @include 'header.php'; ?>

<section class="heading">
    <h3>About Us</h3>
    <p> <a href="home.php">Home</a> / about </p>
</section>

<section class="about">

    <div class="flex">

        <div class="image">
<img src="images/about us k.jpg"width="100%"></img>     
        </div>

        <div class="content">
            <h3>Why choose us?</h3>
            <p>At our website, we take pride in offering only the highest quality products to our customers. 
                Each item is carefully selected and sourced from reputable suppliers, ensuring that our customers receive products that meet their expectations in terms of durability, reliability, and overall quality.</p>
            <a href="shop.php" class="btn">shop now</a>
        </div>
    </div>
<br><br><br><br>

    <div class="flex">
        <div class="content">
            <h3>what we provide?</h3>
            <p>Welcome to ceramic, where tradition and innovation blend to create exquisite ceramic pieces. From functional dinnerware to stunning art pieces,kitcehn-ware,bathroom-ware, 
                our handcrafted ceramics bring beauty and utility into everyday life.</p>
            <a href="contact.php" class="btn">contact us</a>
        </div>

        <div class="image">
            <img src="images/about us B.jpg"width="100%"></img>     
        </div>
    </div>
<br><br><br><br>

    <div class="flex">
        <div class="image">
<img src="images/about us T.jpg"width="100%"></img>        
        </div>

        <div class="content">
            <h3>who we are?</h3>
            <p>Behind every piece of ceramics is a team of dedicated artists and designers who pour their hearts into their work. We’re a family of creators,
                 united by our love for the art of ceramics and a shared mission to bring beauty into the world, one handcrafted piece at a time.</p>         
   <a href="#reviews" class="btn">clients reviews</a>
        </div>
    </div><br>
</section>

<section class="reviews" id="reviews">
    <h1 class="title">client's reviews</h1>
    <div class="box-container">
        <div class="box">
            <img src="images/KIT BUTLER.jpeg" alt="">
            <p></p>
            <div class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>KIT BUTLER</h3>
        </div>
																																																																
        <div class="box">
            <img src="images/George Clooney.jpeg" alt="">
            <p></p>
            <div class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>George Clooney</h3>
        </div>

        <div class="box">
            <img src="images/Mark Zuckerberg.jpeg" alt="">
            <p></p>
            <div class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Mark Zuckerberg</h3>
        </div>

        <div class="box">
            <img src="images/Anne Marie.jpeg" alt="">
            <p></p>
            <div class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Anne Marie</h3>
        </div>

        <div class="box">
            <img src="images/actor.jpeg" alt="">
            <p></p>
            <div class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Actor</h3>
        </div>


        </div>

    </div>

</section>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>