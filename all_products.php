<?php
include 'includes/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    setcookie('user_id', uniqid(), time() + 60 * 60 * 24 * 30);
}

if (isset($_POST['add_to_cart'])) {
    $id = uniqid();
    $product_id = $_POST['product_id'];
    $product_id = filter_var($product_id, FILTER_SANITIZE_STRING);
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);

    $verify_cart = $connection->prepare("SELECT * FROM `cart` 
    WHERE USER_ID = ? AND product_id = ?");
    $verify_cart->execute([$user_id, $product_id]);
    $max_cart_items = $connection->prepare("SELECT * FROM  `cart`
    WHERE user_id = ?");
    $max_cart_items->execute([$user_id]);

    if ($verify_cart->rowCount() > 0) {
        echo '<script type="text/javascript">';
        echo 'alert("Already added to cart!");';
        echo '</script>';
    } else {
        $select_price = $connection->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
        $select_price->execute([$product_id]);
        $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

        $insert_cart = $connection->prepare("INSERT INTO `cart`(id, user_id, product_id, price, qty)
        VALUES(?,?,?,?,?)");
        $insert_cart->execute([$id, $user_id, $product_id, $fetch_price['price'], $qty]);

        echo '<script type="text/javascript">';
        echo 'alert("Added to cart!");';
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
    <title>Products</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'includes/header.php';
    ?>

    <section class="products">
        <h1>All products</h1>
        <div class="box-container">
            <?php

            $select_products = $connection->prepare("SELECT * FROM `products`");
            $select_products->execute();
            if ($select_products->rowCount() > 0) {
                while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <form action="" method="POST" class="form_block" id="product_item">
                        <input type="hidden" name="product_id" value="<?= $fetch_products['id'] ?>">
                        <img src="uploaded_files/<?= $fetch_products['image']; ?>" class="image" alt="product">
                        <h3>
                            <?= $fetch_products['name'] ?>
                        </h3>
                        <div class="product_block">
                            <p class="price">
                                <?= $fetch_products['price'] . "$" ?>
                            </p>
                            <input type="number" name="qty" maxlength="2" min="1" value="1" max="99" required class="input_qty">
                        </div>
                        <a href="checkout.php?get_id=<?= $fetch_products['id']; ?> " class="btn buy_now "> Buy now </a>
                        <input type="submit" value="Add to cart" name="add_to_cart" class="btn">
                    </form>
                    <?php
                }
            } else {
                echo '<p class="empty">No products found</p>';
            }
            ?>
        </div>
    </section>

    <script src="script.js"></script>
</body>

</html>