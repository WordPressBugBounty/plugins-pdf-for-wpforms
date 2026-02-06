<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class Yeepdf_Setup_Template {
    function __construct() { 
        add_action( 'admin_footer', array( $this, 'add_dialog_page' ) );
    }
    function add_dialog_page(){
        global $post;
        $screen = get_current_screen();
        if ( $screen && $screen->post_type === 'yeepdf' && in_array( $screen->base, array('post', 'post-new') ) ) {
            $value = apply_filters("yeepdf_setup_id","",$post->ID);
            $type = apply_filters("yeepdf_setup_type","");
            ?>
            <input type="hidden" id="yeepdf-setup-template_id" value="<?php echo esc_attr($value) ?>">
            <input type="hidden" id="yeepdf-setup-type" value="<?php echo esc_attr($type) ?>">
            <div id="yeepdf-setup-template" style="display:none">
                <div class="yeepdf-setup-container">
                    <div class='pdf-for-woocommerce'>
                        <div class="yeepdf-setup-title-text">
                            <?php esc_attr_e("Name Your Template",'pdf-for-woocommerce') ?>
                        </div>
                        <div class="yeepdf-setup-title-input">
                            <input id="yeepdf-setup-form-title" type="text" placeholder="Enter your template name here…" name="">
                        </div>
                    </div>
                    <div class='pdf-for-woocommerce'>
                        <div class="yeepdf-setup-title-text">
                            <?php esc_html_e("Select a Form",'pdf-for-woocommerce') ?>
                        </div>
                        <div class="yeepdf-setup-title-input">
                            <?php
                                $forms = apply_filters("yeepdf_setup_forms",array(),$post->ID);
                            ?>
                            <select id="yeepdf-setup-form-id">
                                <?php foreach($forms as $id => $name){
                                    ?>
                                    <option value="<?php echo esc_attr($id) ?>"><?php echo esc_html($name) ?></option>
                                    <?php
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="yeepdf-setup-desc">
                        <div class="yeepdf-setup-desc-title">
                            Select a Template
                        </div>
                        <div class="yeepdf-setup-desc-desc">
                            To speed up the process, you can select from one of our pre-made templates, start with a blank form or create your own.
                        </div>
                    </div>
                </div>
                <div class="list-view-templates">
                    <?php 
                          $args = array(
                                "json"=>"",
                                "img"=>YEEPDF_CREATOR_BUILDER_URL."backend/demo/template1/1.png",
                                "title"=>"Email templates",
                                "cat" => array(),
                                "id"=>0,
                            );
                          do_action( "builder_yeepdfs" );
                           ?>
                </div>
            </div>
            <?php
        }
    }
}
new Yeepdf_Setup_Template;