<?php

function wpdocs_remove_menus()
{
	if (current_user_can('administrator')) {
		return;
	}
	// remove_menu_page('index.php');                  //Dashboard
	remove_menu_page('jetpack');                    //Jetpack* 
	remove_menu_page('edit.php');                   //Posts
	remove_menu_page('upload.php');                 //Media
	remove_menu_page('edit.php?post_type=page');    //Pages
	remove_menu_page('edit-comments.php');          //Comments
	remove_menu_page('themes.php');                 //Appearance
	remove_menu_page('plugins.php');                //Plugins
	// remove_menu_page('users.php');                  //Users
	remove_menu_page('tools.php');                  //Tools
	remove_menu_page('options-general.php');        //Settings

}
add_action('admin_menu', 'wpdocs_remove_menus');

function shapeSpace_remove_toolbar_nodes($wp_admin_bar)
{
	if (current_user_can('administrator')) {
		return;
	}

	$wp_admin_bar->remove_node('wp-logo');
	$wp_admin_bar->remove_node('comments');
	$wp_admin_bar->remove_node('customize');
	$wp_admin_bar->remove_node('customize-background');
	$wp_admin_bar->remove_node('customize-header');
	$wp_admin_bar->remove_node('new-content');
}
add_action('admin_bar_menu', 'shapeSpace_remove_toolbar_nodes', 999);

add_action('current_screen', 'wpdocs_this_screen');

/**
 * Run code on the admin widgets page
 */
function wpdocs_this_screen()
{
	if (current_user_can('administrator')) {
		return;
	}

	$currentScreen = get_current_screen();
	$banned_pages = [
		'upload',
		'edit-comments',
		'edit-post_tag',
		'plugins',
		'link-manager',
		'edit-post',
		'edit-page',
		'site-themes-network',
		'themes-network',
		'sites-network',
		'post',
		'page',
		'customize',
		'themes',
		'widgets',
		'nav-menus',
		'theme-editor',
		'plugin-editor',
		'plugin-install',
		'tools',
		'export',
		'import',
		'site-health',
		'export-personal-data',
		'erase-personal-data',
		'options-general',
		'options-writing',
		'options-reading',
		'options-discussion',
		'options-media',
		'options-permalink',
		'options-privacy',
		'options-discussion',
	];

	if (in_array($currentScreen->id, $banned_pages)) {
		wp_redirect(get_admin_url());
		exit;
	}
}

function amaz0n_seller_dashboard_scripts()
{
	wp_enqueue_style('amazon-seller-bootstrap-styles', BpaxAddFile::addFiles('assets/css', 'bootstrap.min', 'css', true));
	wp_enqueue_style('amazon-seller-select2-styles', BpaxAddFile::addFiles('assets/css', 'select2.min', 'css', true));
	wp_enqueue_style('amazon-seller-styles', BpaxAddFile::addFiles('assets/css', 'plugin', 'css', true));
	wp_enqueue_script('jquery-validation-scripts', BpaxAddFile::addFiles('assets/js', 'plugin', 'js', true), array('jquery'), '1.0.0', true);
	wp_enqueue_script('amazon-seller-scripts', BpaxAddFile::addFiles('assets/js', 'jquery.validate.min', 'js', true), array('jquery'), '1.0.0', true);
	wp_enqueue_script('amazon-seller-select2-scripts', BpaxAddFile::addFiles('assets/js', 'select2.min', 'js', true), array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'amaz0n_seller_dashboard_scripts');
add_action('admin_enqueue_scripts', 'amaz0n_seller_dashboard_scripts');


function tm_save_profile_fields($user_id)
{
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	if (!empty($_POST['phone'])) {
		update_usermeta($user_id, 'phone', $_POST['phone']);
	}

	if (!empty($_POST['company'])) {
		update_usermeta($user_id, 'company', $_POST['company']);
	}
}

add_action('personal_options_update', 'tm_save_profile_fields');
add_action('edit_user_profile_update', 'tm_save_profile_fields');
add_action('user_register', 'tm_save_profile_fields');

function tm_additional_profile_fields($user)
{
	$phone = get_the_author_meta('phone', $user->ID);
	$company = get_the_author_meta('company', $user->ID);

?>
	<h3>Extra profile information</h3>

	<table class="form-table">
		<tr>
			<th><label for="birth-date-day">Phone</label></th>
			<td>
				<input name="phone" type="text" value="<?php echo $phone; ?>">
			</td>
		</tr>
		<tr>
			<th><label for="birth-date-day">Company</label></th>
			<td>
				<input name="company" type="text" value="<?php echo $company; ?>">
			</td>
		</tr>
	</table>
<?php
}

add_action('show_user_profile', 'tm_additional_profile_fields');
add_action('edit_user_profile', 'tm_additional_profile_fields');
add_action('user_new_form', 'tm_additional_profile_fields');

add_role(AMAZON_SELLER_CLIENT_ROLE, 'Amazon Seller Client', [
	'read' => true
]);


add_action('init', function () {

	$args = [
		'capability_type'     => array('amazon_seller_prod', 'amazon_seller_prods'),
		'map_meta_cap'        => true,
		'label' => __('Seller Jobs', 'txtdomain'),
		'public' => true,
		'show_in_quick_edit' => false,
		'menu_position' => 99,
		'menu_icon' => BpaxAddFile::addFiles('assets/images', 'icon-small', 'png', true),
		'supports' => ['title'],
		'show_in_rest' => false,
		'rewrite' => ['slug' => 'amazon-seller-products'],
		'labels' => [
			'singular_name' => __('Seller Job', 'txtdomain'),
			'add_new_item' => __('Add New Job Id', 'txtdomain'),
			'new_item' => __('New Job Id', 'txtdomain'),
			'edit_item' => __('Edit Job Id'),
			'view_item' => __('View Job Id', 'txtdomain'),
			'not_found' => __('No Job Ids Found', 'txtdomain'),
			'not_found_in_trash' => __('No Job Ids found in trash', 'txtdomain'),
			'all_items' => __('All Job Ids', 'txtdomain'),
			'insert_into_item' => __('Insert into Job Id', 'txtdomain')
		],
	];
	
	register_post_type('amazon_seller_prod', $args);
});


add_action('admin_init', 'amazon_seller_add_role_caps', 999);
function amazon_seller_add_role_caps()
{
	$roles = array(AMAZON_SELLER_CLIENT_ROLE, 'administrator');
	foreach ($roles as $the_role) {

		$role = get_role($the_role);

		$role->add_cap('read');
		$role->add_cap('read_amazon_seller_prod');
		$role->add_cap('read_private_amazon_seller_prods');
		$role->add_cap('edit_amazon_seller_prod');
		$role->add_cap('edit_amazon_seller_prods');
		$role->add_cap('edit_others_amazon_seller_prods');
		$role->add_cap('edit_published_amazon_seller_prods');
		$role->add_cap('publish_amazon_seller_prods');
		$role->add_cap('delete_others_amazon_seller_prods');
		$role->add_cap('delete_private_amazon_seller_prods');
		$role->add_cap('delete_published_amazon_seller_prods');
	}
}

function asin_number_meta_box()
{

	add_meta_box(
		'asin-number',
		__('ASIN Number', 'sitepoint'),
		'asin_number_meta_box_callback',
		'amazon_seller_prod'
	);
}

function asin_number_meta_box_callback($post)
{

	// Add a nonce field so we can check for it later.
	wp_nonce_field('asin_number_nonce', 'asin_number_nonce');

	$asin_number = get_post_meta($post->ID, 'asin_number', true);
	$asin_category = get_post_meta($post->ID, 'asin_category', true);
	$asin_percentage = get_post_meta($post->ID, 'asin_percentage', true);

	if (!empty($asin_number)) {
		$asin_number = json_decode($asin_number);
	}
	if (!empty($asin_category)) {
		$asin_category = json_decode($asin_category);
	}
	if (!empty($asin_percentage)) {
		$asin_percentage = json_decode($asin_percentage);
	}

	echo '<h2>
			<button type="button" class="button button-primary button-large" id="addScnt">
				<span style="margin-top: 7px;" class="dashicons dashicons-plus">
			</button>
		</h2>';

	echo '<div id="p_scents">';
	foreach($asin_number as $key => $v) {
		echo '<p>
				<label for="p_scnts">
					<input placeholder="ASIN" style="width: 15%;" type="text" id="asin_number" name="asin_number[]" value="' . $v . '" />
					<input placeholder="Category" style="width: 50%;" type="text" id="asin_category" name="asin_category[]" value="' . $asin_category[$key] . '" />
					<select style="width: 15%;" id="asin_percentage" name="asin_percentage[]">
						<option ' . ($asin_percentage[$key] == 5 ? 'selected' : '') . ' value="5">5%</option>
						<option ' . ($asin_percentage[$key] == 10 ? 'selected' : '') . ' value="10">10%</option>
						<option ' . ($asin_percentage[$key] == 15 ? 'selected' : '') . ' value="15">15%</option>
						<option ' . ($asin_percentage[$key] == 20 ? 'selected' : '') . ' value="20">20%</option>
					</select>
				</label>
				<button type="button" class="remScnt button button-primary button-large">
					<span style="margin-top: 6px;" class="dashicons dashicons-no-alt"></span>
				</button>
			</p>
			';
		}
	echo '</div>';

}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function save_asin_number_meta_box_data($post_id)
{

	// Check if our nonce is set.
	if (!isset($_POST['asin_number_nonce'])) {
		return;
	}

	// Verify that the nonce is valid.
	if (!wp_verify_nonce($_POST['asin_number_nonce'], 'asin_number_nonce')) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	/* OK, it's safe for us to save the data now. */


	// Sanitize user input.
	$asin_number = $_POST['asin_number'];
	$asin_category = $_POST['asin_category'];
	$asin_percentage = $_POST['asin_percentage'];
	
	// Make sure that it is set.
	if (!isset($_POST['asin_number'])) {
		$asin_number = null;
	}
	if (!isset($_POST['asin_category'])) {
		$asin_category = null;
	}
	if (!isset($_POST['asin_percentage'])) {
		$asin_percentage = null;
	}
	// Update the meta field in the database.
	update_post_meta($post_id, 'asin_number', json_encode($asin_number));
	update_post_meta($post_id, 'asin_category', json_encode($asin_category));
	update_post_meta($post_id, 'asin_percentage', json_encode($asin_percentage));
}

add_action('save_post', 'save_asin_number_meta_box_data');

add_action('add_meta_boxes', 'asin_number_meta_box');

function posts_for_current_author($query)
{
	global $pagenow;

	if ('edit.php' != $pagenow || !$query->is_admin) {
		return $query;
	}

	if (!current_user_can('edit_others_posts')) {
		global $user_ID;
		$query->set('author', $user_ID);
	}
	return $query;
}
add_filter('pre_get_posts', 'posts_for_current_author');

// prohibit unauthorized user from accessing others posts edit.
function amazon_seller_prod_TWEAKS_current_screen()
{
	if (function_exists('get_current_screen') && !current_user_can('edit_others_posts')) {

		$current_screen = get_current_screen();

		if ($current_screen->base === 'post' && !empty($_GET['post']) && $_GET['action'] === 'edit') {
			$post_author_id = get_post_field('post_author', $_GET['post']);

			if ($post_author_id != get_current_user_id()) {
				wp_redirect(admin_url('edit.php?post_type=amazon_seller_prod'));
			}
		}
	}
}
add_action('current_screen', 'amazon_seller_prod_TWEAKS_current_screen');


// add custom field to custom post type
function choose_client_markup($post)
{
	wp_nonce_field(basename(__FILE__), "choose-client-nonce");

	$args = array(
		'role__in'    => [AMAZON_SELLER_CLIENT_ROLE],
		'role__not_in' => ['administrator'],
		'orderby' => 'user_nicename',
		'order'   => 'ASC',
	);
	$users = get_users($args);

	$post_author_id = get_post_field('post_author', $post->ID);

?>
	<select name="assigned_client">
		<option value="">Choose Client</option>
		<?php
		foreach ($users as $user) {
			$selected = $post_author_id == $user->ID ? 'selected' : '';
			echo '<option ' . $selected . ' value="' . $user->ID . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . ']</option>';
		}
		?>
	</select>
<?php
}

function save_assign_client_meta_box($post_id, $post, $update)
{
	if (!isset($_POST["choose-client-nonce"]) || !wp_verify_nonce($_POST["choose-client-nonce"], basename(__FILE__)))
		return $post_id;

	if (!current_user_can("edit_post", $post_id))
		return $post_id;

	if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
		return $post_id;

	$slug = "amazon_seller_prod";
	if ($slug != $post->post_type)
		return $post_id;

	if (isset($_POST["assigned_client"])) {
		$meta_box_dropdown_value = $_POST["assigned_client"];

		$arg = array(
			'ID' => $post_id,
			'post_author' => $meta_box_dropdown_value,
		);

		// unhook this function so it doesn't loop infinitely
		remove_action('save_post', 'save_assign_client_meta_box');

		// update the post, which calls save_post again
		wp_update_post($arg);
		
		// re-hook this function
		add_action('save_post', 'save_assign_client_meta_box');
	}
	return;
}

add_action("save_post", "save_assign_client_meta_box", 10, 3);

function add_custom_meta_box()
{
	add_meta_box("choose-client-for-product", "Assign Client to Product", "choose_client_markup", "amazon_seller_prod", "side", "high", null);
}
add_action("add_meta_boxes", "add_custom_meta_box");
// ends




add_filter('admin_init', 'register_my_general_settings_fields');
function register_my_general_settings_fields()
{
	register_setting('general', 'tos_uri_form', 'esc_attr');
	add_settings_field('tos_uri_form', '<label for="tos_uri_form">' . __('Rebate Form TOS Link', 'tos_uri_form') . '</label>', 'general_settings_custom_fields_html', 'general');
}

function general_settings_custom_fields_html()
{
	$value = get_option('tos_uri_form', '');
	echo '<input type="text" class"widefat" id="tos_uri_form" name="tos_uri_form" value="' . $value . '" />';
}