<?php
$currentFile = basename($_SERVER['PHP_SELF']);
?>
                        <li>
                            <a href="dashboard.php" class="<?php echo $currentFile === 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-chart-pie"></i>
                                Overview
                            </a>
                        </li>
                        <li>
                            <a href="manage_categories.php" class="<?php echo $currentFile === 'manage_categories.php' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-tags"></i>
                                Manage Categories
                            </a>
                        </li>
                        <li>
                            <a href="manage_products.php" class="<?php echo $currentFile === 'manage_products.php' || $currentFile === 'edit_product.php' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-boxes-stacked"></i>
                                Manage Products
                            </a>
                        </li>
                        <li>
                            <a href="create_product.php" class="<?php echo $currentFile === 'create_product.php' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-plus"></i>
                                Add Product
                            </a>
                        </li>
                        <li>
                            <a href="manage_orders.php" class="<?php echo $currentFile === 'manage_orders.php' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-truck-ramp-box"></i>
                                Manage Deliveries
                            </a>
                        </li>
                        <li>
                            <a href="create_order.php" class="<?php echo $currentFile === 'create_order.php' ? 'active' : ''; ?>">
                                <i class="fa-solid fa-circle-plus"></i>
                                Create Order
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">
