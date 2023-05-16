<header>
    <div class="header">
        <a href="add_product.php" class="logo">LOGO</a>
        <nav class="navigation">
            <a href="add_product.php">ADD PRODUCT</a>
            <a href="all_products.php">ALL PRODUCTS</a>
            <a href="my_cart.php">MY CART</a>
            <a href="my_cart.php">MY ORDERS</a>
            <?php
            $count_cart_items = $connection->prepare("SELECT * FROM `cart`
            WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
            ?>
            <span>
                <?= $total_cart_items; ?>
            </span>
        </nav>
    </div>
</header>