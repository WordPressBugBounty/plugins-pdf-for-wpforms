<?php
/**
 * Plugin Name: PDF for WPForms + Drag and Drop Template Builder
 * Description:  WPForms PDF Customizer is a helpful tool that helps you build and customize the PDF Templates for WPforms.
 * Plugin URI: https://add-ons.org/plugin/wpforms-pdf-generator-attachment/
 * Version: 6.2.1
 * Requires PHP: 5.6
 * Author: add-ons.org
 * Author URI: https://add-ons.org/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define( 'BUIDER_PDF_WPFORMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BUIDER_PDF_WPFORMS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
if(!class_exists('Yeepdf_Creator_Builder')) {
    require 'vendor/autoload.php';
    if(!defined('YEEPDF_CREATOR_BUILDER_PATH')) {
        define( 'YEEPDF_CREATOR_BUILDER_PATH', plugin_dir_path( __FILE__ ) );
    }
    if(!defined('YEEPDF_CREATOR_BUILDER_URL')) {
        define( 'YEEPDF_CREATOR_BUILDER_URL', plugin_dir_url( __FILE__ ) );
    }
    class Yeepdf_Creator_Builder {
        function __construct(){
            $dir = new RecursiveDirectoryIterator(YEEPDF_CREATOR_BUILDER_PATH."backend");
            $ite = new RecursiveIteratorIterator($dir);
            $files = new RegexIterator($ite, "/\.php/", RegexIterator::MATCH);
            foreach ($files as $file) {
                if (!$file->isDir()){
                    require_once $file->getPathname();
                }
            }
            if (!class_exists('QRcode')) {
                include_once YEEPDF_CREATOR_BUILDER_PATH."libs/phpqrcode.php";
            }
            include_once YEEPDF_CREATOR_BUILDER_PATH."frontend/index.php";
        }
    }
    new Yeepdf_Creator_Builder;
}
class Yeepdf_Creator_Wpforms_Builder { 
    function __construct(){
        register_activation_hook( __FILE__, array($this,'activation') );
        include BUIDER_PDF_WPFORMS_PLUGIN_PATH."wpforms/index.php";
        include_once BUIDER_PDF_WPFORMS_PLUGIN_PATH."yeekit/document.php"; 
    }
    function activation() {
        $check = get_option( "yeepdf_wpforms_setup" );
        if( !$check ){           
            $data = file_get_contents(BUIDER_PDF_WPFORMS_PLUGIN_PATH."wpforms/form-import.json");
            $my_template = array(
            'post_title'    => "WPForms Default PDF",
            'post_content'  => "",
            'post_status'   => 'publish',
            'post_type'     => 'yeepdf'
            );
            $id_template = wp_insert_post( $my_template );
            add_post_meta($id_template,"data_email",$data);      
            add_post_meta($id_template,"_builder_pdf_settings_font_family",'dejavu sans');
            update_option( "yeepdf_wpforms_setup",$id_template );     
        } 
    }
}
new Yeepdf_Creator_Wpforms_Builder;