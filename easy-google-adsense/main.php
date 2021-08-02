<?php
/*
Plugin Name: Easy Google AdSense
Version: 1.0.4
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

        var $plugin_version = '1.0.4';

        function __construct() {
            define('EASY_GOOGLE_ADSENSE_VERSION', $this->plugin_version);
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            }
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_action('admin_init', array($this, 'settings_api_init'));
            add_action('admin_menu', array($this, 'add_options_menu'));
            add_action('wp_head', array($this, 'add_adsense_auto_ads_code'));
        }
        
        function plugins_loaded_handler()
        {
            load_plugin_textdomain('easy-google-adsense', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
                $links[] = '<a href="options-general.php?page=easy-google-adsense-settings">'.__('Settings', 'easy-google-adsense').'</a>';
            }
            return $links;
        }
        function add_options_menu() {
            if (is_admin()) {
                add_options_page(__('Easy Google AdSense', 'easy-google-adsense'), __('Easy Google AdSense', 'easy-google-adsense'), 'manage_options', 'easy-google-adsense-settings', array($this, 'options_page'));
            }
        }
        function sanitize($input)
        {
            $sanitized_input = array();
            if(isset($input['publisher_id'])){
                $sanitized_input['publisher_id'] = sanitize_text_field($input['publisher_id']);
            }
            return $sanitized_input;
        }
        function settings_api_init(){
            $args = array( 
                'sanitize_callback' => array( $this, 'sanitize' ) // using a custom function to sanitize since the API doesn't allow array just yet
            );
            register_setting( 'easygoogleadsensepage', 'easy_google_adsense_settings', $args );
            //register_setting( 'easygoogleadsensepage', 'easy_google_adsense_settings' );

            add_settings_section(
                    'easy_google_adsense_section', 
                    __('General Settings', 'easy-google-adsense'), 
                    array($this, 'easy_google_adsense_settings_section_callback'), 
                    'easygoogleadsensepage'
            );

            add_settings_field( 
                    'publisher_id', 
                    __('Publisher ID', 'easy-google-adsense'), 
                    array($this, 'publisher_id_render'), 
                    'easygoogleadsensepage', 
                    'easy_google_adsense_section' 
            );
        }
        function publisher_id_render() { 
            $options = get_option('easy_google_adsense_settings');            
            ?>
            <input type='text' name='easy_google_adsense_settings[publisher_id]' value='<?php echo $options['publisher_id']; ?>'>
            <p class="description"><?php printf(__('Enter your Google AdSense Publisher ID (e.g %s).', 'easy-google-adsense'), 'pub-1234567890111213');?></p>
            <?php
        }
        function easy_google_adsense_settings_section_callback() { 
                //echo __( 'This section description', 'easygoogleadsense' );
        }

        function options_page() {
            $url = "https://noorsplugin.com/easy-google-adsense-plugin-wordpress/";
            $link_text = sprintf(wp_kses(__('Please visit the <a target="_blank" href="%s">Easy Google AdSense</a> documentation page for full setup instructions.', 'easy-google-adsense'), array('a' => array('href' => array(), 'target' => array()))), esc_url($url));
            ?>           
            <div class="wrap">               
            <h2>Easy Google AdSense - v<?php echo $this->plugin_version; ?></h2>
            <div class="update-nag"><?php echo $link_text;?></div>
            <form action='options.php' method='post'>
            <?php
            settings_fields('easygoogleadsensepage');
            do_settings_sections('easygoogleadsensepage');
            submit_button();
            ?>
            </form>
            </div>
            <?php
        }
        
        function add_adsense_auto_ads_code() {
            $options = get_option('easy_google_adsense_settings');
            $publisher_id = $options['publisher_id'];
            if(isset($publisher_id) && !empty($publisher_id)){
                $ouput = <<<EOT
                <!-- auto ad code generated with Easy Google AdSense plugin v{$this->plugin_version} -->
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-{$publisher_id}" crossorigin="anonymous"></script>      
                <!-- / Easy Google AdSense plugin -->
EOT;

                echo $ouput;
            }
        }

    }

    $GLOBALS['easy_google_adsense'] = new EASY_GOOGLE_ADSENSE();
}
