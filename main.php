<?php
/*
Plugin Name: MakerBar Public Presence Projector
Plugin URI: https://github.com/MakerBar/Presence-Wordpress-Plugin
Description: Shows whether or not the MakerBar space is open (based on whether anyone is there)
Version: 1.0
Author: Bert Hartmann (bert@makerbar.com)
Author URI: http://berthartm.com
License: GPLv2 or later
*/

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

register_activation_hook(__FILE__, 'MBPPP_install');

// INITIALIZATION

function MBPPP_install() {
}

// CONFIGURATION SCREEN

if ( is_admin() ) {
    add_action('admin_menu', 'MBPPP_options_menu');
    add_action('admin_init', 'register_MBPPP_settings');
}

function MBPPP_options_menu() {
    add_options_page('MakerBar Presence Settings', 'MakerBar Presence Settings', 'manage_options', 'MBPPP', 'MBPPP_options');
}

function MBPPP_options() {
    if (!current_user_can('manage_options')) {
        wp_die( __('You do not have sufficient permisions to access this page.') );
    }
    echo "<form method=\"post\" action=\"options.php\">";
    settings_fields('MBPPP_options');
    do_settings_sections('MBPPP');
    ?>
        <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
        </form>
    <?php
}

function register_MBPPP_settings() {
    register_setting('MBPPP_options', 'MBPPP_options' );
    add_settings_section('MBPPP_main', 'MBPPP', 'MBPPP_options_instruction', 'MBPPP');
    add_settings_field('MBPPP_url', 'Presence URL', 'MBPPP_setting_url', 'MBPPP', 'MBPPP_main');
}

function MBPPP_setting_url() {
    $options = get_option('MBPPP_options');
    echo "<input type='text' id='MBPPPurl', name='MBPPP_options[url]', value='$options[url]' /> ";
}

function MBPPP_options_instruction() {
    // no instructions, figure it out for now
}

// WIDGET

add_action("plugins_loaded", 'MBPPP_register_widgets');
function MBPPP_register_widgets() {
    register_sidebar_widget(__('Is MakerBar open?'), 'MBPPP_open_query');
}

function MBPPP_open_query($args) {
    extract($args);
    echo $before_widget;
    echo $before_title;
    echo "Is MakerBar open?";
    echo $after_title;
    print_makerbar_open_stuff();
    echo $after_widget;
}

function print_makerbar_open_stuff() {
    $options = get_option('MBPPP_options');
    $ch = curl_init($options['url']);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $ret_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($ret_code == 200) {
        echo "<p><span style='color: #468847; text-align: center; display: block; width: 95%; font-size: 30px; font-weight: bold;'>OPEN</span>Folks are hacking away, come on by!</p>";
    } elseif ($ret_code == 204) {
        echo "<p><span style='color: #B94A48; text-align: center; display: block; width: 95%; font-size: 30px; font-weight: bold;'>CLOSED</span>It doesn't seem like anyone is there right now.</p>";
    } else {
        echo "<p>Something seems to have broken. I can't tell if anyone is there</p>";
    }
}