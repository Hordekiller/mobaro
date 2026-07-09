<?php
$WP_DIR = "/home/solo/Local Sites/mobaro/app/public";
require_once $WP_DIR . '/wp-load.php';

// Create or update menu
$menu_name = "Header Menu";
$menus = wp_get_nav_menus();
$menu_id = 0;
foreach ($menus as $menu) {
    if ($menu->name === $menu_name) {
        $menu_id = $menu->term_id;
        break;
    }
}

if (!$menu_id) {
    $menu_id = wp_create_nav_menu($menu_name);
    echo "Created menu ID: $menu_id\n";
} else {
    echo "Found menu ID: $menu_id\n";
}

// Map page titles to slugs
$pages = [
    'خانه' => 'home',
    'خدمات' => 'services',
    'مدل‌ها' => 'models',
    'آموزش' => 'education',
    'فروشگاه' => 'shop',
    'درباره ما' => 'about',
];

// Remove existing items
$existing = wp_get_nav_menu_items($menu_id);
if ($existing) {
    foreach ($existing as $item) {
        wp_delete_post($item->ID, true);
    }
    echo "Removed old items\n";
}

// Add page links
foreach ($pages as $title => $slug) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page) {
        $item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $title,
            'menu-item-object-id' => $page->ID,
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
        echo "  + $title (page {$page->ID})\n";
    } else {
        echo "  ! Page not found: $slug\n";
    }
}

// Assign to header location
$locations = get_theme_mod('nav_menu_locations', []);
$locations['menu-1'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
echo "Menu assigned to menu-1 location\n";

echo "\nDone. Menu ID: $menu_id\n";
