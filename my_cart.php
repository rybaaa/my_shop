<?php
include 'includes/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    setcookie('user_id', uniqid(), time() + 60 * 60 * 24 * 30);
}

if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);

    $update_cart = $connection->prepare("UPDATE `cart` SET qty = ?
    WHERE  id = ?");
    $update_cart->execute([$qty, $cart_id]);
    echo '<script type="text/javascript">';
    echo 'alert("The item is updated!");';
    echo '</script>';
}

if (isset($_POST['delete_item'])) {
    $cart_id = $_POST['cart_id'];
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);

    $verify_delete_item = $connection->prepare("SELECT * FROM `cart` 
    WHERE id = ? ");
    $verify_delete_item->execute([$cart_id]);

    if ($verify_delete_item->rowCount() > 0) {
        $delete_item = $connection->prepare("DELETE FROM `cart` 
        WHERE id = ?");
        $delete_item->execute([$cart_id]);
        echo '<script type="text/javascript">';
        echo 'alert("The item removed!");';
        echo '</script>';
    } else {
        echo '<script type="text/javascript">';
        echo 'alert("The item is already removed!");';
        echo '</script>';
    }

}

if (isset($_POST['empty_cart'])) {
    $verify_delete_all = $connection->prepare("SELECT * FROM `cart`
    WHERE user_id = ?");
    $verify_delete_all->execute([$user_id]);

    if ($verify_delete_all->rowCount() > 0) {
        $delete_all = $connection->prepare("DELETE FROM `cart` 
        WHERE user_id = ?");
        $delete_all->execute([$user_id]);
        echo '<script type="text/javascript">';
        echo 'alert("The cart is empty!");';
        echo '</script>';
    } else {
        echo '<script type="text/javascript">';
        echo 'alert("The cart is already empty!");';
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
    <title>Shopping cart</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'includes/header.php';
    ?>

    <section class="products">
        <h1>Shopping cart</h1>
        <div class="box-container">
            <?php
            $grand_total = 0;
            $select_cart = $connection->prepare("SELECT * FROM `cart`
            WHERE user_id = ?");
            $select_cart->execute([$user_id]);

            if ($select_cart->rowCount() > 0) {
                while ($fetch_products_in_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                    $select_products = $connection->prepare("SELECT * FROM `products`
                    WHERE id = ?");
                    $select_products->execute([$fetch_products_in_cart['product_id']]);
                    if ($select_products->rowCount() > 0) {
                        while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <form action="" method="POST" class="form_block" id="product_item">
                                <input type="hidden" name="cart_id" value="<?= $fetch_products_in_cart['id'] ?>">
                                <img src="uploaded_files/<?= $fetch_product['image']; ?>" class="image" alt="product">
                                <h3>
                                    <?= $fetch_product['name'] ?>
                                </h3>
                                <div class="product_block">
                                    <p class="price">
                                        <?= $fetch_product['price'] . "$" ?>
                                    </p>
                                    <input type="number" name="qty" maxlength="2" min="1" value="<?= $fetch_products_in_cart['qty'] ?>"
                                        max="99" required class="input_qty">
                                    <button type="submit" class="btn edit" name="update_cart">Edit</button>
                                </div>
                                <p class="price">
                                    <?php
                                    $sub_total = ($fetch_product['price'] * $fetch_products_in_cart['qty']);
                                    echo "Total :$sub_total" . "$";
                                    ?>
                                </p>
                                <input type="submit" class="btn delete_btn" value="Delete from cart" name="delete_item"></button>

                            </form>
                            <?php
                            $grand_total += $sub_total;
                        }
                    } else {
                        echo '<p class="empty">No products found</p>';
                    }
                }
            } else {
                echo '<p class="empty">Cart is empty</p>';
            }
            ?>
        </div>
        <?php

        if ($grand_total != 0) {
            ?>
            <div class="additional_info">
                <p class="price">Total:
                    <?= $grand_total ?>
                </p>
                <a href="checkout.php" class="btn buy_now ">Proceed to checkout</a>
                <form accept="" method="POST">
                    <input type="submit" value="Empty your cart" class="btn delete_btn" name="empty_cart">
                </form>
            </div>
        <?php }
        ?>
    </section>
</body>

</html>