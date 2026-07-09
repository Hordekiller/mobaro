<?php
$WP_DIR = "/home/solo/Local Sites/mobaro/app/public";
require_once $WP_DIR . '/wp-load.php';

$menus = wp_get_nav_menus();
$menu_id = 0;
foreach ($menus as $menu) {
    if ($menu->name === "Header Menu") {
        $menu_id = $menu->term_id;
        break;
    }
}
echo "Menu ID: $menu_id\n";

$header_data = [
    [
        "id" => "header_section",
        "elType" => "section",
        "settings" => [
            "background_background" => "classic",
            "background_color" => "#FFFFFF",
            "border_border" => "solid",
            "border_width" => ["unit" => "px", "top" => "0", "right" => "0", "bottom" => "1", "left" => "0"],
            "border_color" => "#F4F4F5",
            "padding" => ["unit" => "px", "top" => "0", "right" => "0", "bottom" => "0", "left" => "0"],
            "sticky" => "top",
            "sticky_on" => ["desktop", "tablet", "mobile"],
            "sticky_offset" => 0,
            "z_index" => 50,
            "width" => "full_width",
        ],
        "elements" => [
            [
                "id" => "header_container",
                "elType" => "column",
                "settings" => [
                    "_column_size" => 100,
                    "_inline_size" => null,
                    "padding" => ["unit" => "px", "top" => "16", "right" => "30", "bottom" => "16", "left" => "30"],
                ],
                "elements" => [
                    [
                        "id" => "header_inner_section",
                        "elType" => "section",
                        "settings" => [
                            "content_width" => "boxed",
                            "boxed_width" => ["size" => 1400, "unit" => "px"],
                        ],
                        "elements" => [
                            // COLUMN 1: Logo (fa-spa icon + Mobaro)
                            [
                                "id" => "logo_column",
                                "elType" => "column",
                                "settings" => [
                                    "_column_size" => 25,
                                    "_inline_size" => 25,
                                    "content_position" => "center",
                                ],
                                "elements" => [
                                    [
                                        "id" => "logo_widget",
                                        "elType" => "widget",
                                        "widgetType" => "html",
                                        "settings" => [
                                            "html" => '<div class="mobaro-logo-box"><span class="mobaro-logo-icon"><i class="fa-solid fa-spa"></i></span><span class="mobaro-logo-text">موبارو</span></div>',
                                        ],
                                    ],
                                ],
                            ],
                            // COLUMN 2: Nav Menu
                            [
                                "id" => "nav_column",
                                "elType" => "column",
                                "settings" => [
                                    "_column_size" => 50,
                                    "_inline_size" => 50,
                                    "content_position" => "center",
                                ],
                                "elements" => [
                                    [
                                        "id" => "nav_widget",
                                        "elType" => "widget",
                                        "widgetType" => "nav-menu",
                                        "settings" => [
                                            "menu" => $menu_id,
                                            "layout" => "horizontal",
                                            "align_items" => "center",
                                            "pointer" => "text",
                                            "animation_line" => "underline",
                                            "toggle" => "burger",
                                            "menu_type" => "dropdown",
                                            "gap" => ["size" => 32, "unit" => "px"],
                                            "menu_item_typography_typography" => "custom",
                                            "menu_item_typography_font_family" => "Vazirmatn",
                                            "menu_item_typography_font_size" => ["size" => 14, "unit" => "px"],
                                            "menu_item_typography_font_weight" => "500",
                                            "padding_horizontal_menu_item" => 0,
                                            "padding_vertical_menu_item" => 0,
                                        ],
                                    ],
                                ],
                            ],
                            // COLUMN 3: Action icons (Cart + Login)
                            [
                                "id" => "actions_column",
                                "elType" => "column",
                                "settings" => [
                                    "_column_size" => 25,
                                    "_inline_size" => 25,
                                    "content_position" => "center",
                                ],
                                "elements" => [
                                    [
                                        "id" => "actions_wrapper",
                                        "elType" => "widget",
                                        "widgetType" => "html",
                                        "settings" => [
                                            "html" => '<div style="display:flex;align-items:center;gap:8px;justify-content:flex-start;flex-wrap:nowrap">
<a href="/cart" class="mobaro-cart-btn"><i class="fa-solid fa-cart-shopping"></i><span class="mobaro-cart-badge">0</span></a>
<a href="/my-account" class="mobaro-login-btn"><i class="fa-solid fa-user"></i></a>
</div>',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

$post_id = 27;
$data = wp_slash(wp_json_encode($header_data));
update_post_meta($post_id, "_elementor_data", $data);

$document = Elementor\Plugin::instance()->documents->get($post_id);
if ($document) {
    $document->save([]);
    echo "Template $post_id saved\n";
}

Elementor\Plugin::instance()->files_manager->clear_cache();
echo "Cache cleared\n";
echo "All done!\n";
