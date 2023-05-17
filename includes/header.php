<header>
    <div class="header">
        <a href="add_product.php" class="logo">LOGO</a>
        <nav class="navigation">
            <a href="add_product.php">ADD PRODUCT</a>
            <a href="all_products.php">ALL PRODUCTS</a>
            <a href="my_cart.php">MY ORDERS</a>
            <a href="my_cart.php">CART
                <?php
                $count_cart_items = $connection->prepare("SELECT * FROM `cart`
            WHERE user_id = ?");
                $count_cart_items->execute([$user_id]);
                $total_cart_items = $count_cart_items->rowCount();
                ?>
                <span class="cart_items_total">
                    <?= $total_cart_items; ?>
                </span>
            </a>
        </nav>
    </div>
</header>