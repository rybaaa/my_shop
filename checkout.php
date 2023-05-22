<?php
include 'includes/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    setcookie('user_id', uniqid(), time() + 60 * 60 * 24 * 30);
}

if (isset($_GET['get_id'])) {
    $get_id = $_GET['get_id'];
} else {
    $get_id = '';
}

if (isset($_POST['place_order'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $number = $_POST['number'];
    $number = filter_var($number, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $payment = $_POST['payment'];
    $payment = filter_var($payment, FILTER_SANITIZE_STRING);
    $address_type = $_POST['address_type'];
    $address_type = filter_var($address_type, FILTER_SANITIZE_STRING);
    $address = $_POST['street'] . ', ' . $_POST['flat'];
    $address = filter_var($address, FILTER_SANITIZE_STRING);

    $verify_cart = $connection->prepare("SELECT * FROM `cart`
    WHERE user_id = ?");
    $verify_cart->execute([$user_id]);


    if (isset($_GET['get_id'])) {
        $get_product = $connection->prepare("SELECT * FROM `products`
        WHERE id = ? LIMIT 1");
        $get_product->execute([$_GET['get_id']]);
        if ($get_product->rowCount() > 0) {
            while ($fetch_p = $get_product->fetch(PDO::FETCH_ASSOC)) {
                $insert_order = $connection->prepare("INSERT INTO `orders`(`id`, `user_id`, `name`, `email`, `number`,
                `address`, `address_type`, `method`, `product_id`, `price`, `qty`)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $insert_order->execute([
                    uniqid(),
                    $user_id,
                    $name,
                    $email,
                    $number,
                    $address,
                    $address_type,
                    $payment,
                    $fetch_p['id'],
                    $fetch_p['price'],
                    1
                ]);
                echo '<script type="text/javascript">';
                echo 'alert("Order placed!");';
                echo '</script>';
                header('location:orders.php');
            }

        } else {
            echo '<script type="text/javascript">';
            echo 'alert("Something went wrong!");';
            echo '</script>';
        }
    } elseif ($verify_cart->rowCount() > 0) {
        while ($f_cart = $verify_cart->fetch(PDO::FETCH_ASSOC)) {
            $insert_order = $connection->prepare("INSERT INTO `orders`(`id`, `user_id`, `name`, `email`, `number`,
             `address`, `address_type`, `method`, `product_id`, `price`, `qty`)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $insert_order->execute([
                uniqid(),
                $user_id,
                $name,
                $email,
                $number,
                $address,
                $address_type,
                $payment,
                $f_cart['product_id'],
                $f_cart['price'],
                $f_cart['qty'],
            ]);
        }
        if ($insert_order) {
            $empty_cart = $connection->prepare("DELETE FROM `cart`
            WHERE user_id = ?");
            $empty_cart->execute([$user_id]);
        }
    } else {
        echo '<script type="text/javascript">';
        echo 'alert("Your cart is empty!");';
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
    <section class="checkout">
        <h1>Checkout summary</h1>
        <div class="row">
            <form action="" method="POST" class="form">
                <h3>Billing details</h3>
                <div class="form_info">
                    <p>Your name</p>
                    <input type="text" name="name" required maxlength="50" placeholder="Enter your name" class="input">
                    <p>Your email</p>
                    <input type="email" name="email" required maxlength="50" placeholder="Enter your name"
                        class="input">
                    <p>Your number</p>
                    <input type="number" name="number" required minlength="9" placeholder="Enter your name"
                        class="input">
                    <p>Payment method</p>
                    <select name="payment" class="input" required>
                        <option value="cash on delivery">Cash on delivery</option>
                        <option value="net banking">Net banking</option>
                        <option value="credit or debit card">Credit or debit card</option>
                    </select>
                    <p>Address type</p>
                    <select name="address_type" class="input" required>
                        <option value="home">Home</option>
                        <option value="office">Office</option>
                    </select>
                    <p>Address line 1</p>
                    <input type="text" name="street" required maxlength="50" placeholder="E.g. city & street name"
                        class="input">
                    <p>Address line 2</p>
                    <input type="text" name="flat" required maxlength="50" placeholder="E.g. flat № and building №"
                        class="input">
                </div>
                <input type="submit" value="Place order" name="place_order" class="btn">
            </form>
            <div class="summary">
                <h3>Total items</h3>
                <?php
                $grand_total = 0;
                if ($get_id != '') {
                    $select_product = $connection->prepare("SELECT * FROM `products`
                WHERE id = ?");
                    $select_product->execute([$get_id]);
                    if ($select_product->rowCount() > 0) {
                        while ($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)) {
                            $grand_total = $fetch_product['price'];
                            ?>
                            <div class="summary_item">
                                <img src="uploaded_files/<?= $fetch_product['image'] ?>" alt="product" class="image_checkout">
                                <div class="summary_item_info">
                                    <h3>
                                        <?= $fetch_product['name'] ?>
                                    </h3>
                                    <p class="price">
                                        <?= $fetch_product['price'] . "$" ?>
                                    </p>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="empty">Product was not found</p>';
                    }
                } else {
                    $select_cart = $connection->prepare("SELECT * FROM `cart`
                    WHERE user_id = ?");
                    $select_cart->execute([$user_id]);
                    if ($select_cart->rowCount() > 0) {
                        while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {

                            $select_p = $connection->prepare("SELECT * FROM `products`
                            WHERE id = ?");
                            $select_p->execute([$fetch_cart['product_id']]);
                            if ($select_p->rowCount() > 0) {
                                while ($f_product = $select_p->fetch(PDO::FETCH_ASSOC)) {
                                    $sub_total = $f_product['price'] * $fetch_cart['qty'];
                                    $grand_total += $sub_total;
                                    ?>
                                    <div class="summary_item">
                                        <img src="uploaded_files/<?= $f_product['image'] ?>" alt="product" class="image_checkout">
                                        <div class="summary_item_info">
                                            <h3>
                                                <?= $f_product['name'] ?>
                                            </h3>
                                            <p class="price">
                                                <?= $f_product['price'] . "$" . "x" . $fetch_cart['qty'] ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<p class="empty">Product was not found</p>';
                            }
                        }
                    } else {
                        echo '<p class="empty">Your cart is empty</p>';
                    }
                }

                ?>
                <p class="total_sum">Total:
                    <?= $grand_total ?>
                </p>
            </div>
        </div>

    </section>
    <script src="script.js"></script>
</body>

</html>