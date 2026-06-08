<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly

add_action("yeepdf_builder_block", "superaddons_pdf_builder_block_pdf", 200);
function superaddons_pdf_builder_block_pdf()
{
?>
    <li>
        <div class="momongaDraggable pro_disable" title=" Pro Version" data-type="pdf">
            <i class="dashicons dashicons-pdf" style="font-size: 20px; vertical-align: middle; line-height: 1;"></i>
            <div class="yeepdf-tool-text"><?php esc_html_e("PDF", 'pdf-for-woocommerce'); //phpcs:ignore WordPress.WP.I18n.TextDomainMismatch 
                                            ?></div>
        </div>
    </li>
<?php
}

add_action('yeepdf_builder_block_html', "superaddons_pdf_builder_block_pdf_load");
function superaddons_pdf_builder_block_pdf_load($type)
{
    $type["block"]["pdf"]["builder"] = '
    <div class="builder-elements" >
        <div class="builder-elements-content" data-type="pdf">
            <img data-type="0" data-field="0" style="width:100%;height:auto;" src="' . YEEPDF_CREATOR_BUILDER_URL . 'images/your-image.png" alt="">
        </div>
    </div>';
    
    //Show editor
    $type["block"]["pdf"]["editor"]["container"]["show"] = ["padding", "margin", "image", "text-align", "width_height", "condition"];
    
    $text_align = Yeepdf_Global_Data::$text_align;
    $padding = Yeepdf_Global_Data::$padding;
    $margin  = Yeepdf_Global_Data::$margin;
    $size    = Yeepdf_Global_Data::$width_height;
    $pd_mg   = array_merge($padding, $margin);
    $pd_mg_bd_size =  array_merge($pd_mg, $size);
    
    $type["block"]["pdf"]["editor"]["container"]["style"] = $text_align;
    $type["block"]["pdf"]["editor"]["inner"]["style"] = ["img" => $pd_mg_bd_size];
    $type["block"]["pdf"]["editor"]["inner"]["attr"] = ["img" => [
        ".builder__editor--item-image .image_url" => "src",
        ".builder__editor--item-image .yeepdf-image-type-editor" => "data-type",
        ".builder__editor--item-image .yeepdf-image-type-editor-field" => "data-field"
    ]];
    return $type;
}
