<?php
/**
 * Plugin Name: Universal Web Share
 * Description: Adds a Web Share API button below the post title. Performance friendly and simple.
 * Version: 1.0.0
 * Author: Reggio Digital
 * Contributors: vskylabv, reggiodigital
 * Donate link: https://reggiodigital.com
 * Tags: share, social media, social, web share api
 * Requires PHP: 8.1
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Add share button to post content
function universal_web_share_add_share_button_to_content($content) {
    if (is_single()) {
        $options = get_option('universal_web_share_platform_options');
        $share_button = '<div id="share-button" style="cursor:pointer; margin: 20px 0; text-align: right; display: none;">🔗 Share</div>';
        if ($options['button_position'] === 'before') {
            $content = $share_button . $content;
        } else {
            $content = $content . $share_button;
        }
    }
    return $content;
}
add_filter('the_content', 'universal_web_share_add_share_button_to_content');

// Add Web Share script to footer
function universal_web_share_add_web_share_script() {
    if (is_single()) {
        $options = get_option('universal_web_share_platform_options');
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const shareButton = document.getElementById('share-button');
                if (navigator.share) {
                    // Show the share button if navigator.share is available
                    shareButton.style.display = 'block';
                    shareButton.addEventListener('click', function() {
                        const postTitle = document.title;
                        const postUrl = window.location.href;
                        let metaDescriptionTag = document.querySelector('meta[name="description"]');
                        const postText = metaDescriptionTag ? metaDescriptionTag.getAttribute('content') : 'Check out this post!';
                        navigator.share({
                            title: postTitle,
                            text: postText,
                            url: postUrl,
                        }).then(() => {
                            console.log('Successfully shared');
                        }).catch((error) => {
                            console.log('Sharing failed', error);
                        });
                    });
                }
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'universal_web_share_add_web_share_script');

// Create options page
function universal_web_share_create_options_page() {
    add_management_page(
        'Universal Web Share Options',
        'Web Share Options',
        'manage_options',
        'universal-web-share-options',
        'universal_web_share_options_page_html'
    );
}
add_action('admin_menu', 'universal_web_share_create_options_page');

// Add settings section
function universal_web_share_settings_init() {
    add_settings_section(
        'universal_web_share_platform_section',
        'Platform Settings',
        'universal_web_share_platform_section_cb',
        'universal-web-share-options'
    );

    // Add settings field for the share button position
    add_settings_field(
        'universal_web_share_button_position',
        'Share Button Position',
        'universal_web_share_button_position_cb',
        'universal-web-share-options',
        'universal_web_share_platform_section'
    );

    // Register the settings
    register_setting('universal_web_share_platform_options', 'universal_web_share_platform_options');
}
add_action('admin_init', 'universal_web_share_settings_init');

// Callback function for the settings section
function universal_web_share_platform_section_cb() {
    echo esc_html('Choose your platform settings:');
}

// Options page HTML
function universal_web_share_options_page_html() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('universal_web_share_platform_options');
            do_settings_sections('universal-web-share-options');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Callback function for the share button position field
function universal_web_share_button_position_cb() {
    $options = get_option('universal_web_share_platform_options');
    $button_position = isset($options['button_position']) ? $options['button_position'] : 'before';
    ?>
    <select name="universal_web_share_platform_options[button_position]">
        <option value="before" <?php selected($button_position, 'before'); ?>>Before Content</option>
        <option value="after" <?php selected($button_position, 'after'); ?>>After Content</option>
    </select>
    <?php
}