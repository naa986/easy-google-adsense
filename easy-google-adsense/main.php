<?php
/*
Plugin Name: Easy Google AdSense
Version: 1.0.5
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

        var $plugin_version = '1.0.5';

        function __construct() {
            define('EASY_GOOGLE_ADSENSE_VERSION', $this->plugin_version);
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            }
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
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
                add_options_page(__('Easy Google AdSense', 'easy-google-adsense'), __('Easy Google AdSense', 'easy-google-adsense'), 'manage_options', 'easy-google-adsense-settings', array($this, 'display_options_page'));
            }
        }
        function display_options_page()
        {    
            $plugin_tabs = array(
                'easy-google-adsense-settings' => __('General', 'easy-google-adsense'),
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

            
            $this->general_settings();

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
                $options = array();
                $options['publisher_id'] = $publisher_id;
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
                        
                    </tbody>

                </table>

                <p class="submit"><input type="submit" name="easy_google_adsense_update_settings" id="easy_google_adsense_update_settings" class="button button-primary" value="<?php _e('Save Changes', 'easy-google-adsense');?>"></p>
            </form>

            <?php
        }
        function add_adsense_auto_ads_code() {
            $options = get_option('easy_google_adsense_settings');
            $publisher_id = $options['publisher_id'];
            if(!isset($publisher_id) || empty($publisher_id)){
                return;
            }
            if(current_user_can('manage_options')){
                return;
            }
            $ouput = <<<EOT
            <!-- auto ad code generated with Easy Google AdSense plugin v{$this->plugin_version} -->
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-{$publisher_id}" crossorigin="anonymous"></script>      
            <!-- / Easy Google AdSense plugin -->
EOT;

            echo $ouput;
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
    return $options;
}
