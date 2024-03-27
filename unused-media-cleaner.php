<?php
/*
Plugin Name: Media Cleaner Plugin
Plugin URI: https://itspasindu.com/media-cleaner-plugin/
Description: A WordPress plugin for media compatibility, including retina and WebP versions.
Version: 1.0
Author: Pasindu Dewviman
Author URI: https://itspasindu.com/
License: GPL3
*/

// Define constants
define( 'MC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Enqueue scripts and styles
function mc_enqueue_scripts() {
    // Enqueue scripts
    wp_enqueue_script( 'mc-script', MC_PLUGIN_URL . 'js/script.js', array( 'jquery' ), '1.0', true );

    // Enqueue styles
    wp_enqueue_style( 'mc-style', MC_PLUGIN_URL . 'css/style.css', array(), '1.0' );
}
add_action( 'wp_enqueue_scripts', 'mc_enqueue_scripts' );

// Create settings page
function mc_settings_page() {
    ?>
    <div class="wrap">
        <h2>Media Compatibility Plugin Settings</h2>
        <div>
            <h3>Delete Unused Media Items</h3>
            <p>Click the button below to delete all unused media items.</p>
            <form method="post" action="">
                <input type="hidden" name="mc_delete_unused_media" value="1" />
                <?php submit_button( 'Delete Unused Media', 'delete', 'mc_delete_unused_media_button', false ); ?>
            </form>
        </div>
    </div>
    <?php
}

// Handle delete button action
function mc_handle_delete_action() {
    if ( isset( $_POST['mc_delete_unused_media'] ) && $_POST['mc_delete_unused_media'] == 1 ) {
        // Get all media items
        $media_query = new WP_Query(
            array(
                'post_type'      => 'attachment',
                'posts_per_page' => -1,
                'post_status'    => 'any',
            )
        );

        if ( $media_query->have_posts() ) {
            while ( $media_query->have_posts() ) {
                $media_query->the_post();
                $attachment_id = get_the_ID();

                // Check if media item is used in any post or page
                $is_used = false;
                if ( get_post( $attachment_id ) && count( get_posts( array( 'post_type' => 'any', 'meta_key' => '_thumbnail_id', 'meta_value' => $attachment_id ) ) ) ) {
                    $is_used = true;
                }

                // If media item is not used, delete it
                if ( ! $is_used ) {
                    wp_delete_attachment( $attachment_id, true );
                }
            }
            wp_reset_postdata();
        }
    }
}
add_action( 'admin_init', 'mc_handle_delete_action' );

// Add settings page to admin menu
function mc_add_settings_page() {
    add_options_page( 'Media Compatibility Plugin Settings', 'Media Compatibility', 'manage_options', 'mc-settings', 'mc_settings_page' );
}
add_action( 'admin_menu', 'mc_add_settings_page' );
