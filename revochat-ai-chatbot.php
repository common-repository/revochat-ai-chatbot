<?php

/**
 * Plugin Name: RevoChat AI Chatbot
 * Plugin URI: https://www.revo-chat.com/
 * Description: Embed RevoChat AI Chatbot on your wordpress site.
 * Version: 1.2
 * Author: Revolab.ai
 * Author URI: https://www.revolab.ai/
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_action('admin_menu', 'revochat_add_menu', 99);

function revochat_add_menu()
{
    $revochat_hook = add_menu_page('RevoChat Chatbot Configuration', 'RevoChat Setting', 'manage_options', 'revochat_bot_setting', 'revochat_build_setting_page', 'dashicons-admin-generic', 66);

    add_action('admin_enqueue_scripts', function ($hook) use ($revochat_hook) {
        if ($hook === $revochat_hook) {
            wp_enqueue_style('revochat-style', plugins_url('style/style.css', __FILE__));
        }
    });
}

function revochat_build_setting_page()
{
?>
    <div class="wrap revochat-setting">
        <style></style>
        <script>
            function revochatApplyChanges() {
                alert("Your changes have been saved.");
                return false;
            }
        </script>
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('revochat_settings'); ?>
            <?php do_settings_sections('revochat_settings'); ?>
            <label for="revochat_bot_id" class="label-text"><?php echo esc_html_e('RevoChat Chatbot ID', 'revochat_bot_id'); ?></label>
            <input name="revochat_bot_id" id="revochat_bot_id" type="text" class="form-control" placeholder="Enter your bot id" maxlength="750px" value="<?php echo esc_html(get_option('revochat_bot_id')); ?>"></input>
            <button type="submit" class="button button-primary" onclick="revochatApplyChanges()">Submit</button>
        </form>
    </div>
<?php
}

add_action('admin_init', 'revochat_register_settings');

function revochat_register_settings()
{
    register_setting('revochat_settings', 'revochat_bot_id');
}

add_action('wp_footer', 'revochat_add_embed_script');

function revochat_add_embed_script()
{
    $bot_id = get_option('revochat_bot_id');

    $nonce = wp_create_nonce('wc_store_api');
    $items = WC()->cart->get_cart();
    $jsonData = json_encode($items);

    if ($bot_id) {
        // Ensure dynamic values are safely escaped
        $safe_bot_id = esc_attr($bot_id); // Use esc_attr for attributes
        $safe_nonce = esc_attr($nonce);
        // Assuming $jsonData is a JSON string, ensure it's properly escaped for JS context
        $safe_jsonData = esc_js($jsonData);

        // Output the script tags directly, without using esc_html
        echo "<script> window.revoChatConfig = {chatbotId: \"{$safe_bot_id}\", platform: \"wordpress\", nonce: \"{$safe_nonce}\"};</script>\n";
        echo "<script src=\"https://revochat.revolab.ai/embed.js\" id=\"{$safe_bot_id}\" class=\"revo-chat-embeded-script-7887556\" defer></script>";
    }
}

add_action( 'woocommerce_new_order', 'add_custom_note_to_order', 10, 2 );

function add_custom_note_to_order( $order_id ) {   
    if ( isset($_COOKIE['revochat_assist_order']) && $_COOKIE['revochat_assist_order'] == 'true' && ! empty( $order_id ) ) {
        // Create an instance of the WC_Order object
        $order = wc_get_order( $order_id );
        
        // Define the note you want to add. This could be static or dynamically generated.
        $note = 'RevoChat Assisted Order';
        
        // Add the note to the order
        $order->add_order_note( $note );

        $order->update_meta_data( '_revochat_assist_order', 'true' );

        $order->save();
    }
}