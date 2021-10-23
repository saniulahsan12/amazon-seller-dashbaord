<?php

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
		'label' => __('Seller Products', 'txtdomain'),
		'public' => true,
		'show_in_quick_edit' => false,
		'menu_position' => 99,
		'menu_icon' => BpaxAddFile::addFiles('assets/images', 'icon-small', 'png', true),
		'supports' => ['title'],
		'show_in_rest' => false,
		'rewrite' => ['slug' => 'amazon-seller-products'],
		'labels' => [
			'singular_name' => __('Seller Product', 'txtdomain'),
			'add_new_item' => __('Seller Product', 'txtdomain'),
			'new_item' => __('New Seller Product', 'txtdomain'),
			'edit_item' => __('Edit Seller Product'),
			'view_item' => __('View Seller Product', 'txtdomain'),
			'not_found' => __('No Seller Products Found', 'txtdomain'),
			'not_found_in_trash' => __('No Seller Products found in trash', 'txtdomain'),
			'all_items' => __('All Seller Products', 'txtdomain'),
			'insert_into_item' => __('Insert into Seller Product', 'txtdomain')
		],
	];

	register_post_type('amazon_seller_prod', $args);
});

add_action('init', 'product_keywords_hierarchical_taxonomy', 0);
function product_keywords_hierarchical_taxonomy()
{
	$labels = array(
		'name' => _x('Product Keywords', 'taxonomy general name'),
		'singular_name' => _x('Keyword', 'taxonomy singular name'),
		'search_items' =>  __('Search Keywords'),
		'all_items' => __('All Keywords'),
		'parent_item' => __('Parent Keyword'),
		'parent_item_colon' => __('Parent Keyword:'),
		'edit_item' => __('Edit Keyword'),
		'update_item' => __('Update Keyword'),
		'add_new_item' => __('Add New Keyword'),
		'new_item_name' => __('New Keyword Name'),
		'menu_name' => __('Keywords'),
	);

	register_taxonomy(
		'keywords',
		array('amazon_seller_prod'),
		array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_in_rest' => false,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'keywords'),
			'capabilities' => array(
				'manage_terms' => 'manage_keywords',
				'delete_terms' => 'delete_keywords',
				'edit_terms' => 'edit_keywords',
				'assign_terms' => 'assign_keywords',
			)
		)
	);
}

/**
 * Hide tags from quick edit if user does not have admin priviledges
 */
function hide_tags_from_quick_edit($show_in_quick_edit, $taxonomy_name, $post_type)
{
	if ('post_tag' === 'keywords' && !current_user_can('edit_others_posts')) {
		return false;
	} else {
		return $show_in_quick_edit;
	}
}
add_filter('quick_edit_show_taxonomy', 'hide_tags_from_quick_edit', 10, 3);

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

		// add a new capability
		$capabilities = array(
			'manage_keywords',
			'delete_keywords',
			'edit_keywords',
			'assign_keywords',
		);
		foreach ($capabilities as $cap) {
			$role->add_cap($cap);
		}
	}
}

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


function my_create($term_id, $tt_id, $taxonomy)
{
	return add_term_meta($term_id, 'user_id', get_current_user_id());
}
add_action('create_term', 'my_create', 10, 3);


add_filter('get_terms_args', 'user_self_created_terms_only', 10, 2);
function user_self_created_terms_only($args, $taxonomies)
{

	if (!current_user_can('edit_others_posts')) {

		global $wpdb;
		global $typenow;

		$results = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}termmeta WHERE meta_key='user_id' and meta_value = '" . get_current_user_id() . "'", OBJECT);

		$results_mapped = [];
		if (!empty($results)) {
			$results_mapped = array_map(function ($result) {
				return $result->term_id;
			}, $results);
		}
		if ($typenow == 'amazon_seller_prod') {
			// check whether we're currently filtering selected taxonomy
			if (implode('', $taxonomies) == 'keywords') {
				$cats = $results_mapped; // as an array

				if (empty($cats))
					$args['include'] = array(99999999); // no available categories
				else
					$args['include'] = $cats;
			}
		}
	}
	return $args;
}

// Remove pointless post meta boxes
function FRANK_TWEAKS_current_screen()
{
	if (function_exists('get_current_screen') && !current_user_can('edit_others_posts')) {

		$current_screen = get_current_screen();

		if ($current_screen->post_type === 'amazon_seller_prod' && $_GET['taxonomy'] === 'keywords' && !empty($_GET['tag_ID'])) {
			global $wpdb;
			$term_id = $_GET['tag_ID'];
			$results = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}termmeta WHERE meta_key='user_id' and term_id='" . $term_id . "' and meta_value = '" . get_current_user_id() . "'", OBJECT);

			if (empty($results)) {
				wp_redirect(admin_url('edit-tags.php?taxonomy=keywords&post_type=amazon_seller_prod'));
			}
		}

		if ($current_screen->base === 'post' && !empty($_GET['post']) && $_GET['action'] === 'edit') {
			$post_author_id = get_post_field('post_author', $_GET['post']);

			if ($post_author_id != get_current_user_id()) {
				wp_redirect(admin_url('edit.php?post_type=amazon_seller_prod'));
			}
		}
	}
}
add_action('current_screen', 'FRANK_TWEAKS_current_screen');

function remove_quick_edit($actions)
{
	if (!current_user_can('edit_others_posts')) {
		unset($actions['inline hide-if-no-js']);
	}
	return $actions;
}
add_filter('post_row_actions', 'remove_quick_edit', 10, 1);

// add custom field to custom post type
function choose_client_markup($post)
{
	wp_nonce_field(basename(__FILE__), "choose-client-nonce");

	$args = array(
		'role__in'    => [AMAZON_SELLER_CLIENT_ROLE, 'administrator'],
		'orderby' => 'user_nicename',
		'order'   => 'ASC'
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
