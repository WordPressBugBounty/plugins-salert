<?php 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
  Plugin Name: Salert
  Plugin URI:  https://wpoperation.com/plugins/salert/
  Description: Boost trust and conversions with beautiful sales notification popups. Show recent purchases as social proof, with or without WooCommerce.
  Version:     1.3.2
  Tested up to: 7.1.1
  Author:      WPoperation
  Author URI:  https://wpoperation.com/
  License:     GPL2 or later
  License URI: http://www.gnu.org/licenses/gpl-2.0.txt
  Domain Path: /languages/
  Text Domain: salert

 */

defined('SALERT_FRONT_CSS_DIR') or define('SALERT_FRONT_CSS_DIR',plugin_dir_url(__FILE__).'assets/frontend/css');
defined('SALERT_FRONT_JS_DIR') or define('SALERT_FRONT_JS_DIR',plugin_dir_url(__FILE__).'assets/frontend/js');
defined('SALERT_BACK_CSS_DIR') or define('SALERT_BACK_CSS_DIR',plugin_dir_url(__FILE__).'assets/backend/css');
defined('SALERT_BACK_JS_DIR') or define('SALERT_BACK_JS_DIR',plugin_dir_url(__FILE__).'assets/backend/js');
defined('SALERT_PATH') or define('SALERT_PATH',plugin_dir_path(__FILE__));
defined('SALERT_DIR') or define('SALERT_DIR',plugin_dir_url(__FILE__));
defined('SALERT_VERSION') or define('SALERT_VERSION','1.3.2');
/*
**Require File Directories
*/
require_once SALERT_PATH.'inc/settings.php';


if(!class_exists('Salert')){
    class Salert{
       
       // Construtor to load all hooks
	    public function __construct(){
	        add_action( 'init', array($this,'salert_init') );
	        add_action( 'admin_enqueue_scripts',array($this,'register_backend_assets') );
            $salert_settings = get_option('salert_save_settings');
            $show_popup = isset($salert_settings['popup-enable']) ? $salert_settings['popup-enable'] : 0;
            if($show_popup){
                add_action( 'wp_enqueue_scripts', array($this, 'register_frontend_assets') );
                add_action( 'wp_footer', array($this,'salert_display_wrapper' ) );
                add_action( 'wp_ajax_salert_get_content', array($this,'salert_get_content' ) );
                add_action( 'wp_ajax_nopriv_salert_get_content', array($this,'salert_get_content' ) );
            }
            add_action( 'admin_notices', array($this,'salert_pro_notice') );
            add_action( 'wp_ajax_salert_dismiss_pro_notice', array($this,'salert_dismiss_pro_notice') );
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'salert_pro_plugin_action_links') ); 
	    }

  		// Register Text Domain
  		public function salert_init(){
  		   load_plugin_textdomain( 'salert', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
  		}

       //Registering of backend js and css
        public function register_backend_assets() {
            if( isset( $_GET['page'] ) && $_GET['page'] == 'salert-settings' ) {

              wp_enqueue_style( 'salert-admin-css', SALERT_BACK_CSS_DIR . '/salert-admin.css', array(), SALERT_VERSION );
              wp_enqueue_style( 'animate-css', SALERT_BACK_CSS_DIR . '/animate.css', array(), SALERT_VERSION );
              wp_enqueue_style( 'sweetalert-css', SALERT_BACK_CSS_DIR . '/sweetalert2.min.css', array(), SALERT_VERSION );
              wp_enqueue_style( 'wp-color-picker' );
              if ( function_exists( 'wp_enqueue_media' ) ) {
                 wp_enqueue_media();
              } 
              wp_enqueue_script( 'sweetalert-core-js', SALERT_BACK_JS_DIR.'/core.js', array( 'jquery' ), SALERT_VERSION, true );
              wp_enqueue_script( 'sweetalert2-js', SALERT_BACK_JS_DIR.'/sweetalert2.min.js', array( 'jquery', 'sweetalert-core-js' ), SALERT_VERSION, true );
              wp_enqueue_script( 'salert-custom-js', SALERT_BACK_JS_DIR.'/custom.js', array( 'jquery', 'wp-color-picker' ), SALERT_VERSION, true );
              $js_info = array(
      			    'ajax_url'      => admin_url( 'admin-ajax.php' ),
                    'ajax_nonce'    => wp_create_nonce('salert_ajax_nonce_wpop')
      		    );
              wp_localize_script( 'salert-custom-js', 'admin_settings', $js_info );
            }   
        }

        //Registering Frontend Assets
        public function register_frontend_assets(){
            //styles
            wp_enqueue_style( 'animate-css', SALERT_BACK_CSS_DIR . '/animate.css', array(), SALERT_VERSION );
            wp_enqueue_style( 'salert-main-css', SALERT_FRONT_CSS_DIR.'/style.css' );
            
            //scripts
            wp_enqueue_script('salert-main-js',SALERT_FRONT_JS_DIR . '/main.js', array('jquery'), SALERT_VERSION);

            include(SALERT_PATH.'inc/dynamic-styles.php') ;
       }

        //Add display wrapper
        public function salert_display_wrapper(){
            $salert_settings = get_option('salert_save_settings');
            ?>
            <div id="salertWrapper" class="sale_alert_wrapper">
                <div class="popup_position <?php echo $salert_settings['popup-position'];?>">
                    <div class="popup_box">
                        <div class="popup_template animated clearfix" id="popup_template" style="display: none;">
                            <!-- Content will be loaded dynamically through ajax -->
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        //Get salert contents
        public function salert_get_content(){
            $path = SALERT_PATH. '/inc/display.php';
            require_once($path);
            exit();
        }

        // Dismissible "upgrade to Pro" admin notice (dismissal is stored per user)
        public function salert_pro_notice(){
            if ( ! current_user_can( 'manage_options' ) || get_user_meta( get_current_user_id(), 'salert_pro_notice_dismissed', true ) ) {
                return;
            }
            $url = 'https://wpoperation.com/plugins/sale-alert/';
            ?>
            <div class="notice notice-info is-dismissible salert-pro-notice">
                <p>
                    <strong><?php esc_html_e( 'Get more from Salert!', 'salert' ); ?></strong>
                    <?php esc_html_e( 'Upgrade to Pro for real-time WooCommerce order notifications, sound alerts, analytics, multiple templates and priority support.', 'salert' ); ?>
                </p>
                <p><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'salert' ); ?></a></p>
            </div>
            <script>
            jQuery(function($){
                $(document).on('click', '.salert-pro-notice .notice-dismiss', function(){
                    $.post(ajaxurl, { action: 'salert_dismiss_pro_notice', security: '<?php echo esc_js( wp_create_nonce( 'salert_dismiss_pro_notice' ) ); ?>' });
                });
            });
            </script>
            <?php
        }

        public function salert_dismiss_pro_notice(){
            check_ajax_referer( 'salert_dismiss_pro_notice', 'security' );
            if ( current_user_can( 'manage_options' ) ) {
                update_user_meta( get_current_user_id(), 'salert_pro_notice_dismissed', 1 );
            }
            wp_die();
        }

        public function salert_pro_plugin_action_links( $links ) {
         
            $links[] = '<a href="https://wpoperation.com/plugins/sale-alert/" target="_blank" style="color:#05c305; font-weight:bold;">'.esc_html__('Go Pro','salert').'</a>';
            return $links;
        }
	}

	$salert_object = new Salert(); //initialization of plugin
}	    