<?php
/*
Plugin Name: Easy Google AdSense
Version: 1.0.11
Plugin URI: https://noorsplugin.com/easy-google-adsense-plugin-wordpress/
Author: naa986
Author URI: https://noorsplugin.com/
Description: Easily add Google AdSense to your WordPress site
Text Domain: easy-google-adsense
Domain Path: /languages 
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('EASY_GOOGLE_ADSENSE')) {

    class EASY_GOOGLE_ADSENSE {

        var $plugin_version = '1.0.11';
        var $plugin_url;
        var $plugin_path;
        function __construct() {
            define('EASY_GOOGLE_ADSENSE_VERSION', $this->plugin_version);
            define('EASY_GOOGLE_ADSENSE_SITE_URL', site_url());
            define('EASY_GOOGLE_ADSENSE_URL', $this->plugin_url());
            define('EASY_GOOGLE_ADSENSE_PATH', $this->plugin_path());
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                include_once('addons/easy-google-adsense-addons.php');
            }
            add_action('init', array($this, 'init_handler'));
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_action('admin_menu', array($this, 'add_options_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('wp_head', array($this, 'add_adsense_auto_ads_code'));
            add_action('wp_body_open', array($this, 'wp_body_open'));
        }
        function enqueue_admin_scripts($hook) {
            if('settings_page_easy-google-adsense-settings' != $hook) {
                return;
            }
            wp_register_style('easy-google-adsense-addons-menu', EASY_GOOGLE_ADSENSE_URL.'/addons/easy-google-adsense-addons.css');
            wp_enqueue_style('easy-google-adsense-addons-menu');
        }
        function plugins_loaded_handler()
        {
            if(is_admin() && current_user_can('manage_options')){
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            }
            load_plugin_textdomain('easy-google-adsense', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }
        
        function plugin_path(){ 	
            if ( $this->plugin_path ) return $this->plugin_path;		
            return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
        }
    
        function plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
                $links[] = '<a href="options-general.php?page=easy-google-adsense-settings">'.__('Settings', 'easy-google-adsense').'</a>';
            }
            return $links;
        }
        function add_options_menu() {
            if (is_admin()) {
                add_options_page(__('Easy Google AdSense', 'easy-google-adsense'), __('Easy Google AdSense', 'easy-google-adsense'), 'manage_options', 'easy-google-adsense-settings', array($this, 'display_options_page'));
            }
        }
        function display_options_page()
        {    
            $plugin_tabs = array(
                'easy-google-adsense-settings' => __('General', 'easy-google-adsense'),
                'easy-google-adsense-settings&action=addons' => __('Add-ons', 'easy-google-adsense')
            );
            $url = "https://noorsplugin.com/easy-google-adsense-plugin-wordpress/";
            $link_text = sprintf(__('Please visit the <a target="_blank" href="%s">Easy Google AdSense</a> documentation page for setup instructions.', 'easy-google-adsense'), esc_url($url));          
            $allowed_html_tags = array(
                'a' => array(
                    'href' => array(),
                    'target' => array()
                )
            );
            echo '<div class="wrap"><h2>Easy Google AdSense - v'.EASY_GOOGLE_ADSENSE_VERSION.'</h2>';               
            echo '<div class="update-nag">'.wp_kses($link_text, $allowed_html_tags).'</div>';
            $current = '';
            $action = '';
            if (isset($_GET['page'])) {
                $current = sanitize_text_field($_GET['page']);
                if (isset($_GET['action'])) {
                    $action = sanitize_text_field($_GET['action']);
                    $current .= "&action=" . $action;
                }
            }
            $content = '';
            $content .= '<h2 class="nav-tab-wrapper">';
            foreach ($plugin_tabs as $location => $tabname) {
                if ($current == $location) {
                    $class = ' nav-tab-active';
                } else {
                    $class = '';
                }
                $content .= '<a class="nav-tab' . $class . '" href="?page=' . $location . '">' . $tabname . '</a>';
            }
            $content .= '</h2>';
            $allowed_html_tags = array(
                'a' => array(
                    'href' => array(),
                    'class' => array()
                ),
                'h2' => array(
                    'href' => array(),
                    'class' => array()
                )
            );
            echo wp_kses($content, $allowed_html_tags);

            if(!empty($action))
            { 
                switch($action)
                {
                    case 'addons':
                        easy_google_adsense_display_addons();
                        break;
                }
            }
            else
            {
                $this->general_settings();
            }

            echo '</div>'; 
        }
        function general_settings() {
            
            if (isset($_POST['easy_google_adsense_update_settings'])) {
                $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
                if (!wp_verify_nonce($nonce, 'easy_google_adsense_general_settings')) {
                    wp_die(__('Error! Nonce Security Check Failed! please save the general settings again.', 'easy-google-adsense'));
                }
                $publisher_id = '';
                if(isset($_POST['ega_publisher_id']) && !empty($_POST['ega_publisher_id'])){
                    $publisher_id = sanitize_text_field($_POST['ega_publisher_id']);
                }
                $generate_ads_txt = (isset($_POST['ega_generate_ads_txt']) && $_POST['ega_generate_ads_txt'] == '1') ? '1' : '';
                $options = array();
                $options['publisher_id'] = $publisher_id;
                $options['generate_ads_txt'] = $generate_ads_txt;
                easy_google_adsense_update_option($options);
                echo '<div id="message" class="updated fade"><p><strong>';
                echo __('Settings Saved', 'easy-google-adsense').'!';
                echo '</strong></p></div>';
            }
            $options = easy_google_adsense_get_option();

            ?>

            <form method="post" action="">
                <?php wp_nonce_field('easy_google_adsense_general_settings'); ?>

                <table class="form-table">

                    <tbody>
                        
                        <tr valign="top">
                            <th scope="row"><label for="ega_publisher_id"><?php _e('Publisher ID', 'easy-google-adsense');?></label></th>
                            <td><input name="ega_publisher_id" type="text" id="ega_publisher_id" value="<?php echo esc_attr($options['publisher_id']); ?>" class="regular-text">
                                <p class="description"><?php printf(__('Enter your Google AdSense Publisher ID (e.g %s).', 'easy-google-adsense'), 'pub-1234567890111213');?></p></td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row"><?php _e('Generate Ads.txt', 'easy-google-adsense');?></th>
                            <td> <fieldset><legend class="screen-reader-text"><span>Generate Ads.txt</span></legend><label for="ega_generate_ads_txt">
                                        <input name="ega_generate_ads_txt" type="checkbox" id="ega_generate_ads_txt" <?php if(isset($options['generate_ads_txt']) && $options['generate_ads_txt'] == '1') echo ' checked="checked"'; ?> value="1">
                                        <?php _e('Check this option if you want to automatically generate an ads.txt file', 'easy-google-adsense');?></label>
                                </fieldset></td>
                        </tr>
                        
                    </tbody>

                </table>

                <p class="submit"><input type="submit" name="easy_google_adsense_update_settings" id="easy_google_adsense_update_settings" class="button button-primary" value="<?php _e('Save Changes', 'easy-google-adsense');?>"></p>
            </form>

            <?php
        }
        function init_handler() {
            $request = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : false;
            if('/ads.txt' === $request || '/ads.txt?' === substr($request, 0, 9)){
                $options = get_option('easy_google_adsense_settings');
                if(!isset($options['publisher_id']) || empty($options['publisher_id'])){
                    return;
                }
                if(!isset($options['generate_ads_txt']) || empty($options['generate_ads_txt'])){
                    return;
                }
                header('Content-Type: text/plain');
                $ads_txt_content = '#ads.txt generated by Easy Google AdSense plugin'.PHP_EOL;
                $ads_txt_content .= 'google.com, '.$options['publisher_id'].', DIRECT, f08c47fec0942fa0';
                echo $ads_txt_content;
                die();
            }
        }
        function add_adsense_auto_ads_code() {
            if(function_exists('amp_is_request') && amp_is_request()){
                return;
            }
            $options = get_option('easy_google_adsense_settings');
            if(!isset($options['publisher_id']) || empty($options['publisher_id'])){
                return;
            }
            $publisher_id = $options['publisher_id'];
            $show_auto_ads = true;
            $show_auto_ads = apply_filters('easy_google_adsense_show_auto_ads', $show_auto_ads);
            if(!$show_auto_ads){
                return;
            }
            $custom_atts = '';
            $bottom_anchor_ads = '';
            $bottom_anchor_ads = apply_filters('easy_google_adsense_bottom_anchor_ads', $bottom_anchor_ads);
            if(!empty($bottom_anchor_ads)){
                $custom_atts .= ' '.$bottom_anchor_ads;
            }
            $output = '<!-- auto ad code generated by Easy Google AdSense plugin v'.$this->plugin_version.' -->';
            $output .= '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-'.esc_attr($publisher_id).'"'.$custom_atts.' crossorigin="anonymous"></script>';      
            $output .= '<!-- Easy Google AdSense plugin -->';
            
            echo $output;
        }
        function wp_body_open() {
            if(!function_exists('amp_is_request') || !amp_is_request()){
                return;
            }
            $options = get_option('easy_google_adsense_settings');
            if(!isset($options['publisher_id']) || empty($options['publisher_id'])){
                return;
            }
            $publisher_id = $options['publisher_id'];
            $show_auto_ads = true;
            $show_auto_ads = apply_filters('easy_google_adsense_show_auto_ads', $show_auto_ads);
            if(!$show_auto_ads){
                return;
            }
            $custom_atts = '';
            $bottom_anchor_ads = '';
            $bottom_anchor_ads = apply_filters('easy_google_adsense_bottom_anchor_ads', $bottom_anchor_ads);
            if(!empty($bottom_anchor_ads)){
                $custom_atts .= ' '.$bottom_anchor_ads;
            }
            $output = '<!-- auto ad code generated by Easy Google AdSense plugin v'.$this->plugin_version.' -->';
            $output .= '<amp-auto-ads type="adsense" data-ad-client="ca-'.esc_attr($publisher_id).'"'.$custom_atts.'></amp-auto-ads>';      
            $output .= '<!-- Easy Google AdSense plugin -->';
            
            echo $output;
        }

    }

    $GLOBALS['easy_google_adsense'] = new EASY_GOOGLE_ADSENSE();
}

function easy_google_adsense_get_option(){
    $options = get_option('easy_google_adsense_settings');
    if(!is_array($options)){
        $options = easy_google_adsense_get_empty_options_array();
    }
    return $options;
}

function easy_google_adsense_update_option($new_options){
    $empty_options = easy_google_adsense_get_empty_options_array();
    $options = easy_google_adsense_get_option();
    if(is_array($options)){
        $current_options = array_merge($empty_options, $options);
        $updated_options = array_merge($current_options, $new_options);
        update_option('easy_google_adsense_settings', $updated_options);
    }
    else{
        $updated_options = array_merge($empty_options, $new_options);
        update_option('easy_google_adsense_settings', $updated_options);
    }
}

function easy_google_adsense_get_empty_options_array(){
    $options = array();
    $options['publisher_id'] = '';
    $options['generate_ads_txt'] = '';
    return $options;
}
