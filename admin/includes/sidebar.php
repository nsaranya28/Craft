<?php
$currentFile = basename($_SERVER['PHP_SELF']);
$adminPrefix = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false) ? '' : 'admin/';
$navItems = [
    ['icon' => 'fa-solid fa-chart-pie',        'label' => 'Dashboard',      'file' => $adminPrefix . 'dashboard.php',          'match' => ['dashboard.php']],
    ['icon' => 'fa-solid fa-truck',             'label' => 'Orders',         'file' => $adminPrefix . 'manage_orders.php',      'match' => ['manage_orders.php', 'create_order.php']],
    ['icon' => 'fa-solid fa-gift',              'label' => 'Products',       'file' => $adminPrefix . 'manage_products.php',    'match' => ['manage_products.php', 'create_product.php', 'edit_product.php']],
    ['icon' => 'fa-solid fa-tags',              'label' => 'Categories',     'file' => $adminPrefix . 'manage_categories.php',  'match' => ['manage_categories.php']],
    ['icon' => 'fa-solid fa-wand-magic-sparkles', 'label' => 'Custom Orders','file' => $adminPrefix . 'manage_custom_orders.php','match' => ['manage_custom_orders.php']],
    ['icon' => 'fa-solid fa-users',             'label' => 'Customers',      'file' => $adminPrefix . 'manage_customers.php',   'match' => ['manage_customers.php']],
    ['icon' => 'fa-solid fa-star',              'label' => 'Reviews',        'file' => $adminPrefix . 'manage_reviews.php',     'match' => ['manage_reviews.php']],
    ['icon' => 'fa-solid fa-envelope',          'label' => 'Messages',       'file' => $adminPrefix . 'manage_messages.php',    'match' => ['manage_messages.php']],
    ['icon' => 'fa-solid fa-chart-line',        'label' => 'Reports',        'file' => $adminPrefix . 'reports.php',            'match' => ['reports.php']],
    ['icon' => 'fa-solid fa-robot',             'label' => 'AI Dashboard',   'file' => $adminPrefix . 'ai-dashboard.php',       'match' => ['ai-dashboard.php']],
    ['icon' => 'fa-solid fa-gear',              'label' => 'Settings',       'file' => $adminPrefix . 'settings.php',           'match' => ['settings.php']],
];
?>
                        <li class="sidebar-label">♡ Main ♡</li>
                        <?php foreach ($navItems as $item): ?>
                            <li>
                                <a href="<?php echo $item['file']; ?>" class="<?php echo in_array($currentFile, $item['match']) ? 'active' : ''; ?>">
                                    <i class="<?php echo $item['icon']; ?>"></i>
                                    <?php echo $item['label']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li class="sidebar-divider"></li>
                        <li>
                            <a href="../auth/logout.php" style="color: var(--text-light);">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">

