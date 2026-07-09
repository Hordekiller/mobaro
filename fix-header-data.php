<?php
/**
 * Fix header template data for Elementor
 */

$WP_DIR = "/home/solo/Local Sites/mobaro/app/public";
// Get menu ID
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
                            // COLUMN 1: Logo
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
                                        "widgetType" => "heading",
                                        "settings" => [
                                            "title" => "موبارو",
                                            "header_size" => "default",
                                            "align" => "right",
                                            "title_color" => "#e11d48",
                                            "typography_typography" => "custom",
                                            "typography_font_family" => "Playfair Display",
                                            "typography_font_size" => ["size" => 36, "unit" => "px"],
                                            "typography_font_weight" => "700",
                                            "typography_line_height" => ["size" => 1, "unit" => "em"],
                                            "typography_letter_spacing" => ["size" => -1.8, "unit" => "px"],
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
                            // COLUMN 3: Actions
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
                                        "id" => "cart_btn_widget",
                                        "elType" => "widget",
                                        "widgetType" => "button",
                                        "settings" => [
                                            "text" => "سبد خرید",
                                            "link" => ["url" => "/cart"],
                                            "button_background_color" => "#FFFFFF",
                                            "button_text_color" => "#27272A",
                                            "button_border_color" => "#E4E4E7",
                                            "button_border_width" => ["unit" => "px", "top" => "1", "right" => "1", "bottom" => "1", "left" => "1"],
                                            "border_border" => "solid",
                                            "border_radius" => ["unit" => "px", "top" => "24", "right" => "24", "bottom" => "24", "left" => "24"],
                                            "text_padding" => ["unit" => "px", "top" => "10", "right" => "20", "bottom" => "10", "left" => "20"],
                                            "typography_typography" => "custom",
                                            "typography_font_family" => "Vazirmatn",
                                            "typography_font_size" => ["size" => 14, "unit" => "px"],
                                            "typography_font_weight" => "500",
                                        ],
                                    ],
                                    [
                                        "id" => "login_btn_widget",
                                        "elType" => "widget",
                                        "widgetType" => "button",
                                        "settings" => [
                                            "text" => "ورود",
                                            "link" => ["url" => "/my-account"],
                                            "button_background_color" => "#e11d48",
                                            "button_text_color" => "#FFFFFF",
                                            "border_radius" => ["unit" => "px", "top" => "24", "right" => "24", "bottom" => "24", "left" => "24"],
                                            "text_padding" => ["unit" => "px", "top" => "12", "right" => "28", "bottom" => "12", "left" => "28"],
                                            "box_shadow_box_shadow_type" => "preset",
                                            "typography_typography" => "custom",
                                            "typography_font_family" => "Vazirmatn",
                                            "typography_font_size" => ["size" => 14, "unit" => "px"],
                                            "typography_font_weight" => "600",
                                            "hover_color" => "#FFFFFF",
                                            "hover_background_color" => "#be185d",
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

// Also fix the header.php to bypass theme builder since it has issues
$header_php = <<<'PHP'
<?php
/**
 * The template for displaying the header
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
$skip_link_url = apply_filters( 'hello_elementor_skip_link_url', '#content' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( $enable_skip_link ) { ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>"><?php echo esc_html__( 'Skip to content', 'hello-elementor' ); ?></a>
<?php } ?>
<?php
// Render custom Elementor Header (ID: 27)
$header_rendered = false;
if ( class_exists( '\Elementor\Plugin' ) ) {
    $document = \Elementor\Plugin::instance()->documents->get( 27 );
    if ( $document && $document->is_built_with_elementor() ) {
        $content = $document->get_content();
        if ( ! empty( $content ) ) {
            echo $content;
            $header_rendered = true;
        }
    }
}
if ( ! $header_rendered ) {
    if ( hello_elementor_display_header_footer() ) {
        if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
            get_template_part( 'template-parts/dynamic-header' );
        } else {
            get_template_part( 'template-parts/header' );
        }
    }
}
PHP;

file_put_contents("/home/solo/Local Sites/mobaro/app/public/wp-content/themes/hello-elementor/header.php", $header_php);
echo "header.php updated\n";

echo "All done!\n";
