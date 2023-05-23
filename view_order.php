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

if (isset($_POST['cancel'])) {
    $update_order = $connection->prepare("UPDATE `orders` SET status = ?
    WHERE id = ?");
    $update_order->execute(['cancelled', $get_id]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'includes/header.php';
    ?>

    <section class="orders">
        <h1>Order details</h1>
        <?php
        $grand_total = 0;
        $select_orders = $connection->prepare("SELECT * FROM `orders`
                WHERE id = ? LIMIT 1");
        $select_orders->execute([$get_id]);
        if ($select_orders) {
            while ($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                $select_products = $connection->prepare("SELECT * FROM `products`
                        WHERE id = ?");
                $select_products->execute([$fetch_order['product_id']]);
                if ($select_products->rowCount() > 0) {
                    while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
                        $sub_total = ($fetch_order['price'] * $fetch_order['qty']);
                        $grand_total += $sub_total;
                        if ($fetch_order['status'] === "in progress") {
                            $order_status_class = "order_status_in_progress";
                        } elseif ($fetch_order['status'] === "in progress") {
                            $order_status_class = "order_status_completed";
                        } else {
                            $order_status_class = "order_status_cancelled";
                        }
                        ?>
                        <div class="view_order">
                            <div class="view_order_info">
                                <p class="order_date">
                                    <?= $fetch_order['date'] ?>
                                </p>
                                <img class="view_order_image" src="uploaded_files/<?= $fetch_product['image'] ?>" alt="order image">
                                <h3>
                                    <?= $fetch_product['name'] ?>
                                </h3>
                                <p class="order_price">
                                    <?php
                                    echo $fetch_order['price'] . "$ " . "x " . $fetch_order['qty'];
                                    ?>
                                </p>
                                <p class="order_price">
                                    <?= "Total: " . $grand_total . "$" ?>
                                </p>
                            </div>
                            <div class="view_order_address">
                                <p>Billing address</p>
                                <p>
                                    <?= "Name: " . $fetch_order['name'] ?>
                                </p>
                                <p>
                                    <?= "Phone: " . $fetch_order['number'] ?>
                                </p>
                                <p>
                                    <?= "Email: " . $fetch_order['email'] ?>
                                </p>
                                <p>
                                    <?= "Address: " . $fetch_order['address'] ?>
                                </p>
                                <p>Status:</p>
                                <p class="<?= $order_status_class ?>">
                                    <?= $fetch_order['status'] ?>
                                </p>
                                <?php
                                if ($fetch_order['status'] === 'cancelled') {
                                    ?>
                                    <a href="checkout.php?get_id=<?= $fetch_order['product_id'] ?>" class="btn buy_now">Order again</a>
                                    <?php
                                } else {
                                    ?>
                                    <form action="" method="POST">
                                        <input type="submit" value="cancel order" name="cancel" class="btn delete_btn">
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="empty">Product not found</p>';
                }
            }
        } else {
            echo '<p class="empty">Order was not found</p>';
        }
        ?>
    </section>

</body>

</html>