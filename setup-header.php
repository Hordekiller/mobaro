<?php
/**
 * Mobaro Header Setup Script
 * Configures Elementor Kit (Global Styles) & Creates Header Template
 * Run: wp eval-file setup-header.php --path=/path/to/wp
 */

// ==================== GLOBAL COLORS (from home.html CSS) ====================
$system_colors = [
  ['_id' => 'primary',   'title' => 'Primary',   'color' => '#e11d48'],
  ['_id' => 'secondary', 'title' => 'Secondary', 'color' => '#be185d'],
  ['_id' => 'text',      'title' => 'Text',      'color' => '#27272A'],
  ['_id' => 'accent',    'title' => 'Accent',    'color' => '#fff1f2'],
];

// ==================== GLOBAL FONTS ====================
$system_typography = [
  [
    '_id' => 'body',
    'title' => 'بدنه',
    'typography_typography' => 'custom',
    'typography_font_family' => 'Vazirmatn',
    'typography_font_weight' => '400',
    'typography_font_size' => ['size' => 16, 'unit' => 'px'],
    'typography_line_height' => ['size' => 1.7, 'unit' => 'em'],
  ],
  [
    '_id' => 'heading',
    'title' => 'عنوان',
    'typography_typography' => 'custom',
    'typography_font_family' => 'Vazirmatn',
    'typography_font_weight' => '700',
    'typography_font_size' => ['size' => 32, 'unit' => 'px'],
  ],
];

// ==================== UPDATE ELEMENTOR KIT ====================
$kit_id = get_option('elementor_active_kit');
if (!$kit_id) {
  $kit_id = (new \Elementor\Core\Kits\Manager())->create_new_kit('Default Kit');
}

$kit_settings = get_post_meta($kit_id, '_elementor_page_settings', true);
if (!is_array($kit_settings)) $kit_settings = [];

$kit_settings['system_colors'] = $system_colors;
$kit_settings['system_typography'] = $system_typography;
$kit_settings['site_name'] = 'Mobaro';
$kit_settings['site_description'] = 'سالن زیبایی و آرایشگاه حرفه‌ای';

update_post_meta($kit_id, '_elementor_page_settings', $kit_settings);
echo "✅ Kit $kit_id updated with global colors & fonts\n";

// ==================== CUSTOM CSS FOR HEADER ====================
$header_custom_css = <<<CSS
/* Nav link underline animation */
selector .elementor-nav-menu a:after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    right: 0;
    background-color: var(--e-global-color-primary, #e11d48);
    transition: width 0.3s ease;
}
selector .elementor-nav-menu a:hover:after,
selector .elementor-nav-menu a.elementor-item-active:after {
    width: 100%;
    right: auto;
    left: 0;
}
selector .elementor-nav-menu a {
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
selector .elementor-nav-menu a:hover {
    color: var(--e-global-color-primary, #e11d48) !important;
}
/* Hamburger menu button styling */
selector .elementor-menu-toggle {
    border-radius: 12px !important;
}
/* Cart button styling */
selector .mobaro-cart-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: 1px solid #e4e4e7;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    background: white;
}
selector .mobaro-cart-btn:hover {
    background: #f4f4f5;
}
selector .mobaro-cart-count {
    background: var(--e-global-color-primary, #e11d48);
    color: white;
    width: 20px;
    height: 20px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
selector .mobaro-login-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--e-global-color-primary, #e11d48);
    color: white !important;
    padding: 12px 28px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(225, 29, 72, 0.3);
    transition: all 0.2s;
}
selector .mobaro-login-btn:hover {
    background: var(--e-global-color-secondary, #be185d) !important;
    box-shadow: 0 6px 20px rgba(225, 29, 72, 0.4);
}
/* Logo styling */
selector .mobaro-logo-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}
selector .mobaro-logo-icon {
    width: 44px;
    height: 44px;
    background: var(--e-global-color-primary, #e11d48);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}
selector .mobaro-logo-icon i {
    color: white;
    font-size: 22px;
}
selector .mobaro-logo-text {
    font-family: 'Playfair Display', serif;
    font-size: 36px;
    font-weight: 700;
    letter-spacing: -0.05em;
    color: var(--e-global-color-primary, #e11d48);
    line-height: 1;
}
/* Scroll progress bar */
.mobaro-scroll-bar {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(to right, #e11d48, #f43f5e);
    z-index: 99999;
    width: 0;
    transition: width 0.1s;
}
CSS;

// ==================== BUILD HEADER TEMPLATE DATA ====================
$header_data = [
  // === SECTION: Outer (sticky header) ===
  [
    'id' => 'header_section',
    'elType' => 'section',
    'settings' => [
      'background_background' => 'classic',
      'background_color' => '#FFFFFF',
      'border_border' => 'solid',
      'border_width' => ['unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 1, 'left' => 0],
      'border_color' => '#f4f4f5',
      'padding' => ['unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
      'margin' => ['unit' => 'px', 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
      'sticky' => 'top',
      'sticky_on' => ['desktop', 'tablet', 'mobile'],
      'sticky_offset' => 0,
      'z_index' => 50,
      'width' => 'full_width',
    ],
    'elements' => [
      // === INNER SECTION: Container ===
      [
        'id' => 'header_inner',
        'elType' => 'section',
        'settings' => [
          'content_width' => 'boxed',
          'boxed_width' => ['size' => 1400, 'unit' => 'px'],
          'padding' => ['unit' => 'px', 'top' => 20, 'right' => 30, 'bottom' => 20, 'left' => 30],
          'flex_direction' => 'row',
          'flex_wrap' => 'wrap',
          'align_items' => 'center',
          'justify_content' => 'space-between',
        ],
        'elements' => [
          // === COLUMN 1: Logo ===
          [
            'id' => 'logo_col',
            'elType' => 'column',
            'settings' => [
              '_column_size' => 25,
              '_inline_size' => 25,
              '_inline_size_tablet' => 33,
              '_inline_size_mobile' => 50,
              'content_position' => 'center',
            ],
            'elements' => [
              [
                'id' => 'logo_widget',
                'elType' => 'widget',
                'widgetType' => 'html',
                'settings' => [
                  'html' => '<div class="mobaro-logo-wrap">
                    <div class="mobaro-logo-icon"><i class="fas fa-spa"></i></div>
                    <span class="mobaro-logo-text">موبارو</span>
                  </div>',
                ],
              ],
            ],
          ],
          // === COLUMN 2: Nav Menu ===
          [
            'id' => 'menu_col',
            'elType' => 'column',
            'settings' => [
              '_column_size' => 50,
              '_inline_size' => 50,
              '_inline_size_tablet' => 33,
              '_inline_size_mobile' => 0,
              'content_position' => 'center',
              'hide_mobile' => 'hidden',
              'hide_tablet' => 'hidden',
            ],
            'elements' => [
              [
                'id' => 'nav_menu_widget',
                'elType' => 'widget',
                'widgetType' => 'nav-menu',
                'settings' => [
                  'menu' => 16, // Header Menu ID
                  'layout' => 'horizontal',
                  'align_items' => 'center',
                  'pointer' => 'text',
                  'animation_line' => 'underline',
                  'toggle' => 'burger',
                  'menu_type' => 'dropdown',
                  'dropdown_layout' => 'dropdown',
                  'gap' => ['size' => 32, 'unit' => 'px'],
                  'menu_item_typography_typography' => 'custom',
                  'menu_item_typography_font_family' => 'Vazirmatn',
                  'menu_item_typography_font_size' => ['size' => 14, 'unit' => 'px'],
                  'menu_item_typography_font_weight' => '500',
                  'menu_item_typography_text_transform' => 'none',
                  'padding_horizontal_menu_item' => 0,
                  'padding_vertical_menu_item' => 0,
                  'pointer_color' => '#e11d48',
                ],
              ],
            ],
          ],
          // === COLUMN 3: Actions (Cart + Login) ===
          [
            'id' => 'actions_col',
            'elType' => 'column',
            'settings' => [
              '_column_size' => 25,
              '_inline_size' => 25,
              '_inline_size_tablet' => 33,
              '_inline_size_mobile' => 50,
              'content_position' => 'center',
              'flex_direction' => 'row',
              'flex_wrap' => 'no-wrap',
              'align_items' => 'center',
              'justify_content' => 'flex-start',
              'gap' => ['size' => 12, 'unit' => 'px'],
            ],
            'elements' => [
              // Cart Button
              [
                'id' => 'cart_btn',
                'elType' => 'widget',
                'widgetType' => 'html',
                'settings' => [
                  'html' => '<a href="/cart" class="mobaro-cart-btn">
                    <i class="fas fa-shopping-cart" style="color:#e11d48"></i>
                    <span class="mobaro-cart-count">0</span>
                  </a>',
                ],
              ],
              // Login Button
              [
                'id' => 'login_btn',
                'elType' => 'widget',
                'widgetType' => 'html',
                'settings' => [
                  'html' => '<a href="/my-account" class="mobaro-login-btn">
                    <i class="fas fa-user"></i>
                    <span>ورود / ثبت‌نام</span>
                  </a>',
                ],
              ],
            ],
          ],
        ],
      ],
    ],
  ],
];

// ==================== CREATE HEADER TEMPLATE POST ====================
$header_post_id = wp_insert_post([
  'post_title'   => 'Mobaro Header',
  'post_content' => '',
  'post_status'  => 'publish',
  'post_type'    => 'elementor_library',
  'meta_input'   => [
    '_elementor_template_type' => 'header',
    '_elementor_edit_mode'     => 'builder',
    '_elementor_version'       => ELEMENTOR_VERSION,
    '_elementor_data'          => wp_slash(wp_json_encode($header_data)),
    '_elementor_css'           => '',
  ],
]);

if (is_wp_error($header_post_id)) {
  echo "❌ Error creating header: " . $header_post_id->get_error_message() . "\n";
  exit;
}
echo "✅ Header template created (ID: $header_post_id)\n";

// Add custom CSS
update_post_meta($header_post_id, '_elementor_custom_css', $header_custom_css);
echo "✅ Custom CSS added to header\n";

// ==================== SET HEADER AS DISPLAYED GLOBALLY ====================
// Elementor Pro stores conditions in post meta
$conditions = [
  [
    'type' => 'include',
    'name' => 'entire_site',
    'sub_name' => '',
    'sub_id' => '',
  ],
];
update_post_meta($header_post_id, '_elementor_conditions', $conditions);
echo "✅ Header set to display on entire site\n";

// ==================== UPLOAD FONTS TO ELEMENTOR ====================
// Add Vazirmatn + Playfair Display to Elementor's font list
$custom_fonts = get_option('elementor_font_manager_fonts', []);
$custom_fonts['Vazirmatn'] = 'system';
$custom_fonts['Playfair Display'] = 'system';
update_option('elementor_font_manager_fonts', $custom_fonts);
echo "✅ Fonts registered in Elementor\n";

echo "\n🎉 Header setup complete!\n";
echo "  - Global Colors: Primary {$system_colors[0]['color']}\n";
echo "  - Global Fonts: Vazirmatn + Playfair Display\n";
echo "  - Header Template ID: $header_post_id\n";
echo "  - Visit: http://mobaro.local/wp-admin/post.php?post=$header_post_id&action=elementor\n";
