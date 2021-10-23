<?php
defined('ABSPATH') or die('No script kiddies please!');

function amazon_seller_dashboard_settings_details()
{


	global $wpdb;

	$table_name = $wpdb->prefix . 'amazon_seller_products';
	$post_table_name = $wpdb->prefix . 'posts';
	$term_relationships = $wpdb->prefix . 'term_relationships';
	$term_taxonomy = $wpdb->prefix . 'term_taxonomy';
	$terms = $wpdb->prefix . 'terms';
	$page = clean_input($_GET['page_no'] ?? 1);
	$limit = clean_input($_GET['limit'] ?? 10);
	$offset = clean_input(($page - 1) * $limit ?? 0);
	$client = clean_input($_GET['client'] ?? -1);

	$search_params = '';

	$args = array(
		'role__in'    => [AMAZON_SELLER_CLIENT_ROLE, 'administrator'],
		'orderby' => 'user_nicename',
		'order'   => 'ASC'
	);
	$users = get_users($args);


	if (!current_user_can('administrator')) {
		$where_post_author = "WHERE ${table_name}.client_id=" . get_current_user_id();
	} else {
		$where_post_author = "WHERE ${table_name}.client_id=" . clean_input($client);
	}

	if (!empty($_GET['order_number'])) {
		$order_number = clean_input($_GET['order_number']);
		$search_params .= "AND ${table_name}.order_number='${order_number}'";
	}

	if (!empty($_GET['phone'])) {
		$phone = clean_input($_GET['phone']);
		$search_params .= "AND ${table_name}.phone='${phone}'";
	}

	if (!empty($_GET['email'])) {
		$email = clean_input($_GET['email']);
		$search_params .= "AND ${table_name}.email='${email}'";
	}

	if (!empty($_GET['name'])) {
		$name = clean_input($_GET['name']);
		$search_params .= "AND ${table_name}.name LIKE '%${name}%'";
	}

	if (!empty($_GET['keyword_id'])) {
		$keyword_id = clean_input($_GET['keyword_id']);
		$search_params .= "AND ${table_name}.keyword_id=${keyword_id}";
	}

	$sql = "SELECT ${terms}.name AS keyword, ${table_name}.name AS name, order_number, amount, email, phone, keyword_id FROM ${table_name} 
				INNER JOIN ${terms} ON ${table_name}.keyword_id=${terms}.term_id 
				${where_post_author} 
				${search_params} 
			";

	$total = $wpdb->get_results($sql, ARRAY_A);
	if (!empty($total)) {
		$total = $total[0]['total'];
	}

	$sql = "SELECT ${terms}.name AS keyword, ${table_name}.name AS name, order_number, amount, email, phone, keyword_id FROM ${table_name} 
				INNER JOIN ${terms} ON ${table_name}.keyword_id=${terms}.term_id 
				${where_post_author} 
				${search_params} 
				LIMIT ${limit} OFFSET ${offset}";

	$products = $wpdb->get_results($sql, ARRAY_A);

	$sql = "SELECT ${terms}.term_id, ${terms}.name FROM ${term_taxonomy} 
				INNER JOIN ${terms} ON ${term_taxonomy}.term_id=${terms}.term_id 
				AND ${term_taxonomy}.taxonomy='keywords'";

	$keywords = $wpdb->get_results($sql, ARRAY_A);
?>
	<div class="amazon-seller-dashboard-admin">
		<form class="col-md-12" style="margin-top: 2%" method="get">
			<div class="row">
				<div class="col-md-12">
					<h1><strong>Seller Dashboard</strong></h1>
					<p></p>
					<p></p>
				</div>
				<div class="col-md-2">
					<input type="hidden" name="page" value="amazon-seller-dashboard-api-settings">

					<h3><strong>Filter</strong></h3>
					<hr>
					<div class="form-group">
						<label for="limit">Limit</label>
						<select class="form-control" name="limit">
							<option <?php echo $limit == 10 ? 'selected' : ''; ?> value="10">10</option>
							<option <?php echo $limit == 50 ? 'selected' : ''; ?> value="50">50</option>
							<option <?php echo $limit == 100 ? 'selected' : ''; ?> value="100">100</option>
							<option <?php echo $limit == 500 ? 'selected' : ''; ?> value="500">500</option>
							<option <?php echo $limit == 1000 ? 'selected' : ''; ?> value="1000">1000</option>
						</select>
					</div>

					<?php if (current_user_can('administrator')) : ?>
						<div class="form-group">
							<label for="client">Client</label>
							<select class="form-control hybrid-select" name="client">
								<option value="">Choose Client</option>
								<?php
								foreach ($users as $user) {
									$selected = $client == $user->ID ? 'selected' : '';
									echo '<option ' . $selected . ' value="' . $user->ID . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . ']</option>';
								}
								?>
							</select>
						</div>
					<?php endif; ?>

					<div class="form-group">
						<label for="limit">Keywords</label>
						<select class="form-control hybrid-select" name="keyword_id">
							<option value="">Select</option>
							<?php
							if (!empty($keywords)) {
								foreach ($keywords as $keyword) {
									$selected = clean_input($_GET['keyword_id']) == $keyword['term_id'] ? 'selected' : '';
									echo '<option ' . $selected . ' value="' . $keyword['term_id'] . '">' . $keyword['name'] . '</option>';
								}
							}
							?>
						</select>
					</div>

					<h3><strong>Search</strong></h3>
					<hr>
					<div class="form-group">
						<label for="order_number">Order Number</label>
						<input type="text" class="form-control" name="order_number" value="<?php echo clean_input($_GET['order_number']) ?? ''; ?>">
					</div>

					<div class="form-group">
						<label for="order_number">Phone</label>
						<input type="text" class="form-control" name="phone" value="<?php echo clean_input($_GET['phone']) ?? ''; ?>">
					</div>

					<div class="form-group">
						<label for="order_number">Email</label>
						<input type="text" class="form-control" name="email" value="<?php echo clean_input($_GET['email']) ?? ''; ?>">
					</div>

					<div class="form-group">
						<label for="order_number">Name</label>
						<input type="text" class="form-control" name="name" value="<?php echo clean_input($_GET['name']) ?? ''; ?>">
					</div>

					<div class="form-group">
						<p>
							<button class="btn btn-success" type="submit" name="submit">Apply</button>
							<a href="<?php echo admin_url('?page=amazon-seller-dashboard-api-settings'); ?>">
								<button class="btn btn-warning" type="button" name="submit">Clear</button>
							</a>
						</p>
					</div>
				</div>
				<div class="col-md-10">
					<h3><strong>Results</strong></h3>
					<hr>
					<?php if (!empty($products)) : ?>
						<table class="table table-bordered table-hover">
							<thead>
								<tr class="text-center">
									<th scope="col" class="text-right">#</th>

									<?php if (current_user_can('administrator')) : ?>
										<th scope="col">Name</th>
										<th scope="col">Phone</th>
										<th scope="col">Email</th>
									<?php endif; ?>

									<th scope="col">Order Id.</th>
									<th scope="col" class="text-right">Amount</th>
									<th scope="col">Keywords</th>
								</tr>
							</thead>

							<?php foreach ($products as $key => $product) : ?>
								<tbody>
									<tr class="text-center">
										<th scope="row" class="text-right"><?php echo $key + 1; ?></th>

										<?php if (current_user_can('administrator')) : ?>
											<td><?php echo $product['name']; ?></td>
											<td><?php echo $product['phone']; ?></td>
											<td><?php echo $product['email']; ?></td>
										<?php endif; ?>

										<td><?php echo $product['order_number']; ?></td>
										<td class="text-right"><?php echo $product['amount']; ?></td>
										<td><?php echo $product['keyword']; ?></td>
								</tbody>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>

					<?php if (empty($products)) : ?>
						<div class="alert alert-danger">
							No Results found.
						</div>
					<?php endif; ?>

					<?php if (!empty($products) && ($total > 0) && ($total > $limit)) : ?>
						<ul class="pagination">
							<?php for ($i = 1; $i <= $total; $i++) : ?>
								<li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
									<button type="submit" name="page_no" value="<?php echo $i; ?>" class="page-link"><?php echo $i; ?></button>
								</li>
							<?php endfor; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</form>
	</div>
<?php
}
