<?php
include 'includes/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    setcookie('user_id', uniqid(), time() + 60 * 60 * 24 * 30);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    include 'includes/header.php';
    ?>

    <section class="orders">
        <h1>My Orders</h1>
        <div class="all_orders">
            <?php
            $select_orders = $connection->prepare("SELECT * FROM `orders`
                WHERE user_id = ? ORDER BY date DESC");
            $select_orders->execute([$user_id]);
            if ($select_orders) {
                while ($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                    $select_products = $connection->prepare("SELECT * FROM `products`
                        WHERE id = ?");
                    $select_products->execute([$fetch_order['product_id']]);
                    if ($select_products->rowCount() > 0) {
                        while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
                            if ($fetch_order['status'] === "in progress") {
                                $order_status_class = "order_status_in_progress";
                            } elseif ($fetch_order['status'] === "in progress") {
                                $order_status_class = "order_status_completed";
                            } else {
                                $order_status_class = "order_status_cancelled";
                            }
                            ?>
                            <a href="view_order.php?get_id=<?= $fetch_order['id']; ?>" class="order_item">
                                <p class="order_date">
                                    <?= $fetch_order['date'] ?>
                                </p>
                                <img class="order_image" src="uploaded_files/<?= $fetch_product['image'] ?>" alt="product image">
                                <h3 class="order_title">
                                    <?= $fetch_product['name'] ?>
                                </h3>
                                <p class="order_price">
                                    <?php
                                    echo $fetch_order['price'] . "$ " . "x " . $fetch_order['qty'];
                                    ?>
                                </p>
                                <p class="<?= $order_status_class ?>">
                                    <?= $fetch_order['status'] ?>
                                </p>
                            </a>
                            <?php
                        }
                    }
                }
            } else {
                echo '<p class="empty">Orders not found</p>';
            }
            ?>
        </div>
    </section>
</body>

</html>