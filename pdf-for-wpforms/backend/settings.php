<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly
global $yeepdf_settings_main;
class Yeepdf_Settings_Main
{
	private $notices = array();
	function __construct()
	{
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('wp_ajax_yeepdf_remove_font', array($this, "remove_font"));
		add_action('yeepdf_custom_sizes', array($this, "add_sizes"));
		add_action('admin_init', array($this, 'plugins_loaded'));
		add_action('wp_ajax_yeepdf_dropbox_client_id_validate', [$this, 'ajax_validate_api_token']);
		add_action("yeepdf_after_settings", array($this, "yeepdf_after_settings"), 10);
		add_filter('upload_mimes', array($this, 'custom_upload_mimes'));
	}
	function custom_upload_mimes($mimes)
	{
		$mimes['ttf'] = 'application/x-font-ttf';
		return $mimes;
	}
	function yeepdf_after_settings()
	{
?>
		<tr valign="top">
			<th scope="row"><?php esc_html_e("Save PDF to Dropbox", 'pdf-for-woocommerce') ?>
			</th>
			<td>
				<div class="pro_disable pro_disable_fff">
					<?php
					esc_html_e('Upgrade to pro version', 'pdf-for-woocommerce');
					?>
				</div>
			</td>
		</tr>
	<?php
	}
	function ajax_validate_api_token()
	{
		check_ajax_referer("yeepdf_dropbox", '_nonce');
		$clientId = sanitize_text_field(wp_unslash($_POST['clientId']));
		$clientSecret = sanitize_text_field(wp_unslash($_POST['clientSecret']));
		$authorizationCode = sanitize_text_field(wp_unslash($_POST['authorizationCode']));
		if (! isset($_POST['clientId'])) {
			wp_send_json_error();
		}
		if (! current_user_can('manage_options')) {
			wp_send_json_error('Permission denied');
		}
		try {
			$datas = Yeepdf_Dropbox_API::get_token($clientId, $clientSecret, $authorizationCode);
			if ($datas == "ok") {
				wp_send_json_success($datas);
			} else {
				wp_send_json_error($datas);
			}
		} catch (\Exception $exception) {
			wp_send_json_error();
		}
		wp_send_json_success();
	}
	public static function generateRandomString($length = 15)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz_';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[random_int(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	public static function maybe_get_random_dir()
	{
		$settings_folder = get_option("pdf_creator_save_folder", "pdfs/downloads");
		$uploads_folder = apply_filters("yeepdf_folder_download", $settings_folder);
		$uploads_folder = ltrim($uploads_folder, "/\\");
		if (strpos($uploads_folder, '..') !== false) {
			$uploads_folder = 'pdfs/downloads';
		}
		return $uploads_folder;
	}
	public static function maybe_add_random_dir()
	{
		$upload_dir = wp_upload_dir();
		$uploads_folder = self::maybe_get_random_dir();
		$dir = $upload_dir['basedir'] . '/' . $uploads_folder . '/';
		$url = $upload_dir['baseurl'] . '/' . $uploads_folder . '/';
		$settings_folder = get_option("pdf_creator_save_random", "");
		$disable_random = apply_filters("yeepdf_disable_random_folder", $settings_folder);
		if ($disable_random != "yes") {
			do {
				$rand_max = mt_getrandmax();
				$rand = self::generateRandomString();
				$dir_new = path_join($dir, $rand);
				$url_new = $url . $rand;
			} while (file_exists($dir_new));
			if (wp_mkdir_p($dir_new)) {
				return array("path" => $dir_new . "/", "url" => $url_new . "/");
			}
			return array("path" => $dir, "url" => $url);
		} else {
			if (wp_mkdir_p($dir)) {
				return array("path" => $dir, "url" => $url);
			}
			return array("path" => $dir, "url" => $url);
		}
	}
	public static function destroy_all_files($dirPath = null)
	{
		global $wp_filesystem;
		if (! $wp_filesystem) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$upload_dir = wp_upload_dir();
		$basedir    = wp_normalize_path($upload_dir['basedir']);
		//dirPath → set default
		if (empty($dirPath)) {
			$uploads_folder = self::maybe_get_random_dir();
			$dirPath = trailingslashit($upload_dir['basedir'] . '/' . $uploads_folder);
		}
		$dirPath = wp_normalize_path($dirPath);
		// CHECK
		if (strpos($dirPath, $basedir) !== 0) {
			return;
		}
		if (! is_dir($dirPath)) {
			return;
		}
		$items = scandir($dirPath);
		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}
			$path = $dirPath . '/' . $item;
			if (is_dir($path)) {
				self::destroy_all_files($path);
			} else {
				wp_delete_file($path);
			}
		}
		$wp_filesystem->rmdir($dirPath, true);
	}
	public function plugins_loaded()
	{
		$this->check_mb_string();
		$this->check_mb_string_regex();
		$this->check_gd();
		$this->check_dom();
		$this->check_ram(ini_get('memory_limit'));
		if (count($this->notices) > 0) {
			add_action('admin_notices', array($this, 'display_notices'));
		}
	}
	public function display_notices()
	{
	?>
		<div class="error">
			<p><strong><?php esc_html_e('PDF Installation Problem', 'pdf-for-woocommerce'); ?></strong></p>
			<p><?php esc_html_e('The minimum requirements for PDF have not been met. Please fix the issue(s) below to use the plugin:', 'pdf-for-woocommerce'); ?></p>
			<ul>
				<?php foreach ($this->notices as $notice): ?>
					<li style="padding-left: 15px;"><?php echo wp_kses_post($notice); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php
	}
	public function check_mb_string()
	{
		if (! extension_loaded('mbstring')) {
			$this->notices[] = sprintf(esc_html__('The PHP Extension MB String could not be detected. Contact your web hosting provider to fix. %1$sGet more info%2$s.', 'pdf-for-woocommerce'), '<a href="https://pdf.add-ons.org/wordpress-pdf-activation-errors-and-how-to-fix-them/">', '</a>');
		}
	}
	public function check_mb_string_regex()
	{
		if (extension_loaded('mbstring') && ! function_exists('mb_regex_encoding')) {
			$this->notices[] = sprintf(esc_html__('The PHP Extension MB String does not have MB Regex enabled. Contact your web hosting provider to fix. %1$sGet more info%2$s.', 'pdf-for-woocommerce'), '<a href="https://pdf.add-ons.org/wordpress-pdf-activation-errors-and-how-to-fix-them/">', '</a>');
		}
	}
	public function check_gd()
	{
		if (! extension_loaded('gd')) {
			$this->notices[] = sprintf(esc_html__('The PHP Extension GD Image Library could not be detected. Contact your web hosting provider to fix. %1$sGet more info%2$s.', 'pdf-for-woocommerce'), '<a href="https://pdf.add-ons.org/wordpress-pdf-activation-errors-and-how-to-fix-them/">', '</a>');
		}
	}
	public function check_dom()
	{
		if (! extension_loaded('dom') || ! class_exists('DOMDocument')) {
			$this->notices[] = sprintf(esc_html__('The PHP DOM Extension was not found. Contact your web hosting provider to fix. %1$sGet more info%2$s.', 'pdf-for-woocommerce'), '<a href="https://pdf.add-ons.org/wordpress-pdf-activation-errors-and-how-to-fix-them/">', '</a>');
		}
		if (! extension_loaded('libxml')) {
			$this->notices[] = sprintf(esc_html__('The PHP Extension libxml could not be detected. Contact your web hosting provider to fix. %1$sGet more info%2$s.', 'pdf-for-woocommerce'), '<a href="https://pdf.add-ons.org/wordpress-pdf-activation-errors-and-how-to-fix-them/">', '</a>');
		}
	}
	public function check_ram($ram)
	{
		$memory_limit = $this->convert_ini_memory($ram);
		$ram = ($memory_limit === '-1') ? -1 : floor($memory_limit / 1024 / 1024);
		if ($ram < 64 && $ram !== -1) {
			$this->notices[] = sprintf(esc_html__('You need %1$s128MB%2$s of WP Memory (RAM) but we only found %3$s available. %4$sTry these methods to increase your memory limit%5$s, otherwise contact your web hosting provider to fix.', 'pdf-for-woocommerce'), '<strong>', '</strong>', $ram . 'MB', '<a href="https://pdf.add-ons.org/how-to-increase-your-wordpress-memory-limit-for-pdf/">', '</a>');
		}
	}
	public function convert_ini_memory($memory)
	{
		$convert = array(
			'mb' => 'm',
			'kb' => 'k',
			'gb' => 'g',
		);
		foreach ($convert as $k => $v) {
			$memory = str_ireplace($k, $v, $memory);
		}
		switch (strtolower(substr($memory, -1))) {
			case 'm':
				return (int) $memory * 1048576;
			case 'k':
				return (int) $memory * 1024;
			case 'g':
				return (int) $memory * 1073741824;
		}
		return $memory;
	}
	public static function add_number_seletor($name, $value, $class = "", $attr = "")
	{
	?>
		<div class="pdf-marketing-merge-tags-container <?php echo esc_attr($class) ?>">
			<input value="<?php echo esc_attr($value) ?>" type="text" name="<?php echo esc_attr($name) ?>" class="regular-text code-selector" <?php echo esc_attr($attr) ?>>
			<span class="dashicons dashicons-shortcode pdf-merge-tags"></span>
		</div>
	<?php
	}
	public static function get_list_fonts()
	{
		return array(
			"dejavusans" => [
				'R' => "DejaVuSans.ttf",
				'B' => "DejaVuSans-Bold.ttf",
				'I' => "DejaVuSans-Oblique.ttf",
				'BI' => "DejaVuSans-BoldOblique.ttf",
				'useOTL' => 0xFF,
				'useKashida' => 75,
			],
			"dejavuserif" => [
				'R' => "DejaVuSerif.ttf",
				'B' => "DejaVuSerif-Bold.ttf",
				'I' => "DejaVuSerif-Italic.ttf",
				'BI' => "DejaVuSerif-BoldItalic.ttf",
			],
			"OpenSans" => [
				'R' => "OpenSans-Regular.ttf",
				'B' => "OpenSans-Bold.ttf",
				'I' => "OpenSans-Italic.ttf",
				'BI' => "OpenSans-BoldItalic.ttf",
			],
			"fontawesome" => [
				'R' => "fontawesome.ttf",
			],
		);
	}
	function add_sizes($sizes)
	{
		$settings = get_option("pdf_creator_papers", "201,297");
		$datas = explode("\n", $settings);
		if (is_array($datas)) {
			foreach ($datas as $data) {
				$data = trim($data);
				$pages = explode(",", $data);
				if (isset($pages[0]) && isset($pages[1])) {
					$sizes[$data] =	"(" . $pages[0] . " x " . $pages[1] . "mm)";
				}
			}
		}
		return $sizes;
	}
	function remove_font()
	{
		check_ajax_referer("_yeepdf_check_nonce", '_wpnonce');
		if (!current_user_can('manage_options')) {
			return;
		}
		$fontname = sanitize_text_field(wp_unslash($_POST["font_name"]));
		$fontname = preg_replace('/[^a-z]/', '', strtolower($fontname));
		if ($fontname === '') {
			die();
		}
		$type = sanitize_text_field(wp_unslash($_POST["type"]));
		$custom_fonts = get_option("pdf_custom_fonts", array());
		unset($custom_fonts[$fontname]);
		update_option("pdf_custom_fonts", $custom_fonts);
		die();
	}
	function add_plugin_page()
	{
		add_submenu_page('edit.php?post_type=yeepdf', 'Settings', 'Settings', 'manage_options', 'yeepdf-settings', array($this, 'settings_page'));
		add_action('admin_init', array($this, 'register_settings'));
	}
	function register_settings()
	{
		// Handle font uploads
		if (!current_user_can('manage_options')) {
			return;
		}
		$settings = [
			'pdf_creator_font',
			'pdf_creator_save_pdf',
			'pdf_creator_save_random',
			'pdf_creator_save_folder',
			'pdf_creator_dropbox_token',
			'pdf_creator_dropbox_token_secret',
			'pdf_creator_dropbox_access'
		];
		foreach ($settings as $setting) {
			register_setting('pdf_creator_font', $setting, ['sanitize_callback' => 'sanitize_text_field']);
		}
		if (isset($_POST['pdf_creator_papers'])) {
			register_setting('pdf_creator_font', 'pdf_creator_papers', ['sanitize_callback' => 'sanitize_textarea_field']);
		}
		$fonts = array("R" => null, "B" => null, "I" => null, "BI" => null);
		$upload_dir = wp_upload_dir();
		$fonts_dir  = trailingslashit($upload_dir['basedir']) . 'pdfs/fonts/';
		$font_fields = array(
			'pdf_creator_font_upload_regular'      => 'R',
			'pdf_creator_font_upload_bold'         => 'B',
			'pdf_creator_font_upload_italic'       => 'I',
			'pdf_creator_font_upload_bold_italic'  => 'BI',
		);
		$has_upload = false;
		foreach ($font_fields as $input_key => $style_key) {
			if (empty($_FILES[$input_key]['tmp_name']) || $_FILES[$input_key]['error'] !== UPLOAD_ERR_OK) {
				continue;
			}
			$file     = $_FILES[$input_key];
			$tmp_path = $file['tmp_name'];
			$raw_name = sanitize_file_name($file['name']);
			$file_info = wp_check_filetype_and_ext($tmp_path, $raw_name);
			$ext       = $file_info['ext'];
			$type      = $file_info['type'];
			$filename  = $file_info['proper_filename'] ?: $raw_name;
			$allowed_ext = ($ext === 'ttf');
			$allowed_mime = (strpos($type, 'font') !== false || $type === 'application/x-font-ttf');
			if ($allowed_ext && $allowed_mime) {
				if (!file_exists($fonts_dir)) {
					wp_mkdir_p($fonts_dir);
				}
				$dest = $fonts_dir . $filename;
				// if (move_uploaded_file($tmp_path, $dest)) {
				// 	$fonts[$style_key] = $filename;
				// 	$has_upload = true;
				// }
				// Ensure the fonts directory exists.
				// Use WordPress upload handling (WP.org: do not use move_uploaded_file()).
				require_once ABSPATH . 'wp-admin/includes/file.php';
				$overrides = array(
					'test_form' => false,
					'mimes'     => array(
						'ttf' => 'application/x-font-ttf',
					),
				);
				$uploaded = wp_handle_upload($file, $overrides);
				if (isset($uploaded['file']) && is_string($uploaded['file']) && '' !== $uploaded['file']) {
					// Move the uploaded file into our fonts directory using WP_Filesystem.
					global $wp_filesystem;
					if (! $wp_filesystem) {
						WP_Filesystem();
					}
					if ($wp_filesystem) {
						$fonts_dir_norm   = trailingslashit(wp_normalize_path($fonts_dir));
						$target_filename  = wp_unique_filename($fonts_dir_norm, $filename);
						$target_full_path = $fonts_dir_norm . $target_filename;
						$moved = $wp_filesystem->move($uploaded['file'], $target_full_path, true);
						if ($moved) {
							$fonts[$style_key] = $target_filename;
							$has_upload          = true;
						} else {
							// Clean up temp upload if move fails.
							wp_delete_file($uploaded['file']);
						}
					} else {
						// If filesystem is unavailable, clean up temp upload.
						wp_delete_file($uploaded['file']);
					}
				}
			}
		}
		$font_name_raw = isset($_POST['pdf_creator_font_name']) ? $_POST['pdf_creator_font_name'] : '';
		if ($fonts["R"] && $has_upload && !empty($font_name_raw)) {
			$name = strtolower(sanitize_text_field($font_name_raw));
			$name = preg_replace('/[^a-z]/', '', $name);
			$custom_fonts = get_option("pdf_custom_fonts", []);
			$custom_fonts[$name] = $fonts;
			update_option("pdf_custom_fonts", $custom_fonts);
		}
	}
	function settings_page()
	{
		$fonts = self::get_list_fonts();
		wp_nonce_field("_yeepdf_check_nonce", "_yeepdf_check_nonce");
	?>
		<div class="wrap">
			<h1><?php esc_html_e("PDF Creator Settings", 'pdf-for-woocommerce') ?></h1>
			<h3><?php esc_html_e("Font Manage", 'pdf-for-woocommerce') ?></h3>
			<div class="list-fonts">
				<div class="header-list-fonts">
					<div><?php esc_html_e("Installed Fonts", 'pdf-for-woocommerce') ?></div>
					<div><?php esc_html_e("Regular", 'pdf-for-woocommerce') ?></div>
					<div><?php esc_html_e("Italics", 'pdf-for-woocommerce') ?></div>
					<div><?php esc_html_e("Bold", 'pdf-for-woocommerce') ?></div>
					<div><?php esc_html_e("Bold Italics", 'pdf-for-woocommerce') ?></div>
					<div><?php esc_html_e("Remove", 'pdf-for-woocommerce') ?></div>
				</div>
				<?php
				foreach ($fonts as $key => $font) { ?>
					<div class="container-list-fonts">
						<div class="pdf-font-name" style="font-family: '<?php echo esc_attr($key) ?>'"><?php echo esc_html($key); ?> </div>
						<?php
						$array_type = array("R", "I", "B", "BI");
						foreach ($array_type as $type) {
							if (isset($font[$type])) {
								$class = "yes";
							} else {
								$class = "no";
							}
						?>
							<div><span class="dashicons dashicons-<?php echo esc_attr($class) ?>"></span></div>
						<?php
						}
						?>
					</div>
				<?php }
				$google_fonts = get_option("pdf_custom_fonts", array());
				foreach ($google_fonts as $key => $font) {
					$r_font = "no";
					$i_font = "no";
					$b_font = "no";
					$bi_font = "no";
					if (isset($font["R"]) && $font["R"] != "") {
						$r_font = "yes";
					}
					if (isset($font["I"]) && $font["I"] != "") {
						$i_font = "yes";
					}
					if (isset($font["B"]) && $font["B"] != "") {
						$b_font = "yes";
					}
					if (isset($font["BI"]) && $font["BI"] != "") {
						$bi_font = "yes";
					}
				?>
					<div class="container-list-fonts">
						<div class="pdf-font-name" style="font-family: '<?php echo esc_attr($key) ?>'"><?php echo esc_html($key); ?> </div>
						<div><span class="dashicons dashicons-<?php echo esc_attr($r_font) ?>"></span></div>
						<div><span class="dashicons dashicons-<?php echo esc_attr($i_font) ?>"></span></div>
						<div><span class="dashicons dashicons-<?php echo esc_attr($b_font) ?>"></span></div>
						<div><span class="dashicons dashicons-<?php echo esc_attr($bi_font) ?>"></span></div>
						<div><a href="#" class="pdf-remove-font" data-type="upload"><span class="dashicons dashicons-trash"></span></a></div>
					</div>
				<?php } ?>
			</div>
			<h3><?php esc_html_e("Add Font", 'pdf-for-woocommerce') ?></h3>
			<form method="post" action="options.php" enctype="multipart/form-data" class="tab_pdf_creator_font">
				<?php settings_fields('pdf_creator_font'); ?>
				<?php do_settings_sections('pdf_creator_font'); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Font Name *", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<input type="text" name="pdf_creator_font_name" class="pdf_creator_font_name regular-text">
							<p><?php esc_html_e("The font name must contain lowercase letters only", 'pdf-for-woocommerce') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Regular *", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<input class="pdf_creator_font_files" type="file" name="pdf_creator_font_upload_regular">
							<p><?php esc_html_e("The plugin supports the .ttf font file", 'pdf-for-woocommerce') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Italics", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<input class="pdf_creator_font_files" type="file" name="pdf_creator_font_upload_italic">
							<p><?php esc_html_e("The plugin supports the .ttf font file", 'pdf-for-woocommerce') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Bold", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<input class="pdf_creator_font_files" type="file" name="pdf_creator_font_upload_bold">
							<p><?php esc_html_e("The plugin supports the .ttf font file", 'pdf-for-woocommerce') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Bold Italics", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<input class="pdf_creator_font_files" type="file" name="pdf_creator_font_upload_bold_italic">
							<p><?php esc_html_e("The plugin supports the .ttf font file", 'pdf-for-woocommerce') ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button("Add Font"); ?>
			</form>
			<br>
			<hr>
			<form method="post" action="options.php">
				<?php settings_fields('pdf_creator_font'); ?>
				<?php do_settings_sections('pdf_creator_font'); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Custom PDF Paper (mm)", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<textarea class="large-text code" row="4" name="pdf_creator_papers"><?php echo esc_textarea(get_option("pdf_creator_papers", "201,297")) ?></textarea>
							<p><?php esc_html_e("One size per line. E.g 1 line: 210,297", 'pdf-for-woocommerce') ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Do not save PDFs on the server", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<?php $save = get_option("pdf_creator_save_pdf", "") ?>
							<input <?php checked($save, "yes") ?> type="checkbox" name="pdf_creator_save_pdf" value="yes">
							<?php esc_html_e("It will automatically delete the PDF after attaching the file to the email.", 'pdf-for-woocommerce') ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e("Disable random name folder", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<?php $random = get_option("pdf_creator_save_random", "") ?>
							<input <?php checked($random, "yes") ?> type="checkbox" name="pdf_creator_save_random" value="yes">
							/wp-content/uploads/pdfs/downloads/<strong>[random_name]</strong>/name.pdf to /wp-content/uploads/pdfs/downloads/name.pdf
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e("PDF storage folder", 'pdf-for-woocommerce') ?>
						</th>
						<td>
							<?php $folder = get_option("pdf_creator_save_folder", "pdfs/downloads");
							$folder = rtrim($folder, "/\\");
							?>
							<input class="regular-text" type="text" name="pdf_creator_save_folder" value="<?php echo esc_attr($folder) ?>">
							<p>/wp-content/uploads/<strong><?php echo esc_attr($folder) ?></strong>/name.pdf</p>
						</td>
					</tr>
					<?php
					do_action("yeepdf_after_settings");
					?>
				</table>
				<?php
				do_action("yeepdf_after_table_settings");
				?>
				<?php submit_button(); ?>
			</form>
		</div>
	<?php
	}
	public static function get_conditional_logic($conditional = null, $class_logic_container = "hidden")
	{
	?>
		<div class="gform-settings-description gform-kitchen-sink">
			<?php
			if ($conditional == "") {
				$conditional = array(
					"type" => "show",
					"logic" => "all",
					"conditional" => array()
				);
			}
			?>
			<div class="yeepdf-popup-content <?php echo esc_attr($class_logic_container) ?>">
				<select name="yeepdf_logic[type]" id="yeepdf-logic-type">
					<option <?php selected($conditional["type"], 'show') ?> value="show"><?php esc_html_e("Enable", 'pdf-for-woocommerce') ?></option>
					<option <?php selected($conditional["type"], 'hide') ?> value="hide"><?php esc_html_e("Disable", 'pdf-for-woocommerce') ?></option>
				</select>
				<?php esc_html_e("this PDF if", 'pdf-for-woocommerce') ?>
				<select name="yeepdf_logic[logic]" id="yeepdf-logic-logic">
					<option <?php selected($conditional["logic"], 'all') ?> value="all"><?php esc_html_e("All", 'pdf-for-woocommerce') ?></option>
					<option <?php selected($conditional["logic"], 'any') ?> value="any"><?php esc_html_e("Any", 'pdf-for-woocommerce') ?></option>
				</select>
				<?php esc_html_e("of the following match", 'pdf-for-woocommerce') ?>:
				<div class="text-center yeepdf-logic-logic-bnt-container">
					<a href="#" class="yeepdf_condition_add button"><?php esc_html_e("Add Condition", 'pdf-for-woocommerce') ?></a>
				</div>
				<div class="yeepdf-popup-layout">
					<?php
					if (isset($conditional["conditional"]) && is_array($conditional["conditional"]) && count($conditional["conditional"]) > 0) {
						$i = 1;
						$shortcodes = Yeepdf_Builder_PDF_Shortcode::list_shortcodes();
						foreach ($conditional["conditional"] as $data) {
					?>
							<div class="yeepdf-logic-item">
								<div class="yeepdf-logic-item-name">
									<select class="yeepdf-logic-name" name="yeepdf_logic[conditional][<?php echo esc_attr($i) ?>][name]">
										<?php
										self::get_all_shortcodes_select_option($data["name"]);
										?>
									</select>
								</div>
								<div class="yeepdf-logic-item-rule">
									<select class="yeepdf-logic-rule" name="yeepdf_logic[conditional][<?php echo esc_attr($i) ?>][rule]">
										<option value="is">is</option>
										<option <?php selected($data["rule"], 'isnot') ?> value="isnot">is not</option>
										<option <?php selected($data["rule"], 'greater_than') ?> value="greater_than">greater than</option>
										<option <?php selected($data["rule"], 'less_than') ?> value="less_than">less than</option>
										<option <?php selected($data["rule"], 'contains') ?> value="contains">contains</option>
										<option <?php selected($data["rule"], 'starts_with') ?> value="starts_with">starts with</option>
										<option <?php selected($data["rule"], 'ends_with') ?> value="ends_with">ends with</option>
									</select>
								</div>
								<div class="yeepdf-logic-item-value">
									<input type="text" class="yeepdf-logic-value" name="yeepdf_logic[conditional][<?php echo esc_attr($i) ?>][value]" value="<?php echo esc_attr($data["value"]) ?>">
								</div>
								<div class="yeepdf-popup-layout-settings">
									<a class="yeepdf-popup-minus" href="#"><span class="dashicons dashicons-trash"></span></a>
								</div>
							</div>
					<?php
							$i++;
						}
					}
					?>
				</div>
			</div>
			<?php
		}
		public static function get_all_shortcodes_select_option($value = "")
		{
			$shortcodes = Yeepdf_Builder_PDF_Shortcode::list_shortcodes();
			foreach ($shortcodes as $shortcode_k => $shortcode_v) {
			?>
				<optgroup label="<?php echo esc_html($shortcode_k) ?>">
					<?php
					foreach ($shortcode_v as $k => $v) {
						if (is_array($v)) {
							foreach ($v as $k_i => $v_i) {
								if (strpos($k_i, "{") === false) {
									$k_i = "[" . $k_i . "]";
								}
					?>
								<option <?php selected($value, $k_i) ?> value="<?php echo esc_attr($k_i) ?>"><?php echo esc_attr($v_i) ?></option>
							<?php
							}
						} else {
							if (strpos($k, "{") === false) {
								$k = "[" . $k . "]";
							}
							?>
							<option <?php selected($value, $k) ?> value="<?php echo esc_attr($k) ?>"><?php echo esc_attr($v) ?></option>
					<?php
						}
					} ?>
				</optgroup>
	<?php
			}
		}
	}
	$yeepdf_settings_main = new Yeepdf_Settings_Main;
