<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_action("yeepdf_builder_block","superaddons_pdf_builder_block_rote",60);
function superaddons_pdf_builder_block_rote(){
    ?>
    <li>
        <div class="momongaDraggable pro_disable" data-type="rotate" title=" Pro Version">
            <i class="dashicons dashicons-image-rotate"></i>
            <div class="yeepdf-tool-text"><?php esc_html_e("Rotate Text",'pdf-for-woocommerce') ?></div>
        </div>
    </li>
    <?php
}
add_filter( 'yeepdf_builder_block_html', "superaddons_pdf_builder_block_rote_load" );
function superaddons_pdf_builder_block_rote_load($type){
   $type["block"]["rotate"]["builder"] = '
   <div class="builder-elements">
        <div class="builder-elements-content builder-elements-content-rotate" data-type="rotate">
             <div class="text-content-data hidden" >Rotate 90</div>
            <div class="text-content" text-rotate="90">Rotate 90</div>   
        </div>
    </div>';
   //Show editor
    $inner_style = array(
            ".builder__editor--item-background .builder__editor_color"=>"background-color",
            ".builder__editor--item-height .text_height"=>"height",
            ".builder__editor--item-background .image_url"=>"background-image",
        );
    $type["block"]["rotate"]["editor"]["container"]["show"]= ["rotate","width_height","padding","html","margin","condition"];
    $padding = Yeepdf_Global_Data::$padding;
    $margin = Yeepdf_Global_Data::$margin;
    $size    = Yeepdf_Global_Data::$width_height;
    $type["block"]["rotate"]["editor"]["container"]["style"]= array_merge($padding,$margin);
    $type["block"]["rotate"]["editor"]["inner"]["style"]= array(".text-content"=>$size);
    $type["block"]["rotate"]["editor"]["inner"]["attr"] = array(
        ".text-content"=>array(".builder__editor--html .builder__editor--js"=>"html",".builder__editor--item-rotate .text_rotate"=>"text-rotate"),
        ".text-content-data"=>array(".builder__editor--html .builder__editor--js"=>"html_hide"));
    return $type;
}