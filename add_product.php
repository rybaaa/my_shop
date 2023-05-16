<?php
include 'connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    setcookie('user_id', uniqid(), time() + 60 * 60 * 24 * 30);
}

if (isset($_POST['add_product'])) {
    $id = uniqid();
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $price = $_POST['price'];
    $price = filter_var($name, FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    $rename = uniqid() . ".$ext";
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $image_folder = 'uploaded_files/' . $rename;

    if ($image_size < 2000000) {
        $add_product = $connection->prepare("INSERT INTO products(id, name, price, image)
        VALUES(?,?,?,?)");
        $add_product->execute([$id, $name, $price, $image]);
        move_uploaded_file($image_tmp, $image_folder);
        echo '<script type="text/javascript">';
        echo 'alert("Success!");';
        echo '</script>';
    } else {
        echo '<script type="text/javascript">';
        echo 'alert("Too large image!");';
        echo '</script>';
    }


}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'includes/header.php';
    ?>

    <section class="add_product">
        <form action="" method="POST" enctype="multipart/form-data">
            <h3>Product details</h3>
            <p>Product name</p>
            <input type="text" name="name" required maxlength="50" placeholder="Enter product name" class="input">
            <p>Product price</p>
            <input type="number" name="price" required maxlength="10" min="0" max="99999"
                placeholder="Enter product name" class="input">
            <p>Product image</p>
            <input type="file" name="image" required accept="image/*" class="input_image">
            <input type="submit" value="Add product" name="add_product" class="btn">
        </form>
    </section>





    <script src="script.js"></script>
</body>

</html>