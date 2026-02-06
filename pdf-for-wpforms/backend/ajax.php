<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
class Yeepdf_Ajax
{
	function __construct()
	{
		add_action('wp_ajax_yeepdf_builder_text', array($this, 'yeepdf_builder_text'));
		add_action('wp_ajax_yeepdf_builder_export_html', array($this, 'yeepdf_builder_export_html'));
		add_action('wp_ajax_pdf_reset_template', array($this, 'pdf_reset_template'));
		add_action('wp_ajax_yeepdf_import_template', array($this, 'yeepdf_import_template'));
		add_action("admin_init", array($this, "pdf_reset_template_php"));
		add_action('add_meta_boxes', array($this, 'remove_wp_seo_meta_box'), 100);
	}
	function yeepdf_import_template()
	{
		check_ajax_referer('_yeepdf_check_nonce', '_nonce');

		if (empty($_POST['url'])) {
			wp_send_json_error(array('message' => 'Missing URL'));
		}

		$url = esc_url_raw(wp_unslash($_POST['url']));
		$upload_dir = wp_upload_dir();

		// Ensure URL is inside uploads directory
		if (strpos($url, trailingslashit($upload_dir['baseurl'])) !== 0) {
			wp_send_json_error(array('message' => 'Invalid URL path'));
		}

		// Only allow JSON files
		if (strtolower(substr($url, -5)) !== '.json') {
			wp_send_json_error(array('message' => 'Invalid file type. JSON only.'));
		}

		$path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);

		if (!file_exists($path) || !is_readable($path)) {
			wp_send_json_error(array('message' => 'File not found or unreadable'));
		}

		$json_content = file_get_contents($path);

		if (false === $json_content) {
			wp_send_json_error(array('message' => 'Failed to read file'));
		}

		$data = json_decode($json_content, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(array('message' => 'Invalid JSON format'));
		}

		wp_send_json_success($data);
		die();
	}
	function pdf_reset_template()
	{

		check_ajax_referer('_yeepdf_check_nonce', '_nonce');
		if (isset($_POST["id"])) {
			$post_id = sanitize_text_field(wp_unslash($_POST['id']));
			update_post_meta($post_id, 'data_email', '');
		}
		die();
	}
	function pdf_reset_template_php()
	{
		if (isset($_GET["pdf_reset"])) {
			if (wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'pdf_reset')) {
				$post_id = sanitize_text_field(wp_unslash($_GET['post']));
				update_post_meta($post_id, 'data_email', '');
			}
		}
	}
	function remove_wp_seo_meta_box()
	{
		remove_meta_box('wpseo_meta', "yeepdf", 'normal');
	}
	function yeepdf_builder_export_html()
	{
		check_ajax_referer('_yeepdf_check_nonce', '_nonce');
		if (isset($_POST["id"])) {
			$post_id = sanitize_text_field(wp_unslash($_POST['id']));
			$id = get_post_meta($post_id, 'data_email_email', true);
			include YEEPDF_CREATOR_BUILDER_PATH . "pdf-templates/header.php";
			echo do_shortcode($id);
			include YEEPDF_CREATOR_BUILDER_PATH . "pdf-templates/footer.php";
		}
		die();
	}
	function yeepdf_builder_text()
	{
		check_ajax_referer('_yeepdf_check_nonce', '_nonce');
		if (class_exists("Yeepdf_Addons_Woocommerce_Shortcodes")) {
			$shortcode = new Yeepdf_Addons_Woocommerce_Shortcodes;
			$order_id = sanitize_text_field($_POST["order_id"]);
			$shortcode->set_order_id($order_id);
		}
		$string_with_shortcodes = wp_filter_post_kses($_POST["text"]);
		$type = sanitize_text_field($_POST["type"]);
		if ($type == "barcode") {
			$string_with_shortcodes = '[wp_builder_pdf_barcode]' . $string_with_shortcodes . '[/wp_builder_pdf_barcode]';
		} elseif ($type == "qrcode") {
			$string_with_shortcodes = '[wp_builder_pdf_qrcode]' . $string_with_shortcodes . '[/wp_builder_pdf_qrcode]';
		}
		$string_with_shortcodes = str_replace('\\', "", $string_with_shortcodes);
		$string_with_shortcodes = do_shortcode($string_with_shortcodes);
		echo $string_with_shortcodes; // phpcs:ignore WordPress.Security.EscapeOutput
		die();
	}
}
new Yeepdf_Ajax;