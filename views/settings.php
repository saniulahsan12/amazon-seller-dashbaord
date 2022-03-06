<?php
defined('ABSPATH') or die('No script kiddies please!');

add_shortcode('amazon-seller-dashboard', 'amazon_seller_dashboard_settings_details');

function format_to_currency($amount)
{
	return number_format((float)$amount, 2, '.', '');
}

function seller_increment_calculation($amount, $percentage)
{
	$amount_calculated = $amount - ($amount * ($percentage / 100));
	return format_to_currency($amount_calculated);
}

function seller_grand_total_calculation($products)
{
	$grand_total = [];

	foreach ($products as $key => $product) {
		$grand_total[] = seller_increment_calculation($product['amount'], $product['percentage']);
	}

	return format_to_currency(array_sum($grand_total));
}

function amazon_seller_dashboard_settings_details()
{

	ob_start();
	global $wpdb;

	$table_name = $wpdb->prefix . 'amazon_seller_products';
	$page = clean_input($_GET['page_no'] ?? 1);
	$limit = clean_input($_GET['limit'] ?? 10);
	// $limit = 1;
	$offset = clean_input(($page - 1) * $limit ?? 0);
	$client = clean_input($_GET['client'] ?? -1);

	$search_params = '';
	$total = 0;
	$products = [];

	$args = array(
		'role__in'    => [AMAZON_SELLER_CLIENT_ROLE, 'administrator'],
		'orderby' => 'user_nicename',
		'order'   => 'ASC'
	);
	$users = get_users($args);


	if (!current_user_can('administrator')) {
		$where_post_author = "WHERE ${table_name}.client_id=" . get_current_user_id();
	} else {
		if (!empty($_GET['client']) && $_GET['client'] == 'all') {
			$where_post_author = '';
		} else {
			$where_post_author = "WHERE ${table_name}.client_id=" . clean_input($client);
		}
	}

	if (!empty($_GET['job_id'])) {
		$job_id = clean_input($_GET['job_id']);
		$search_params .= " AND ${table_name}.job_id=${job_id}";
	}

	if (!empty($_GET['keyword'])) {
		$keyword = clean_input($_GET['keyword']);
		$search_params .= " AND ${table_name}.keyword LIKE '%${keyword}%'";
	}

	if (!empty($_GET['order_number'])) {
		$order_number = clean_input($_GET['order_number']);
		$search_params .= " AND ${table_name}.order_number LIKE '%${order_number}%'";
	}

	if (current_user_can('administrator')) {

		if (!empty($_GET['phone'])) {
			$phone = clean_input($_GET['phone']);
			$search_params .= "AND ${table_name}.phone='${phone}'";
		}

		if (!empty($_GET['email'])) {
			$email = clean_input($_GET['email']);
			$search_params .= "AND ${table_name}.email='${email}'";
		}

		if (!empty($_GET['u_name'])) {
			$name = clean_input($_GET['u_name']);
			$search_params .= "AND ${table_name}.name LIKE '%${name}%'";
		}


		if (!empty($_GET['fromDate']) && !empty($_GET['toDate'])) {
			$fromDate = date('Y-m-d', strtotime(clean_input($_GET['fromDate'])));
			$toDate = date('Y-m-d', strtotime(clean_input($_GET['toDate'])));
			$search_params .= "AND ${table_name}.created >= '${fromDate}' AND ${table_name}.created <= '${toDate}'";
		}
	}

	if (sizeof($_GET) > 0) {
		$sql = "SELECT count(*) AS total FROM ${table_name} 
					${where_post_author} 
					${search_params} 
				";

		$total = $wpdb->get_results($sql, ARRAY_A);
		if (!empty($total)) {
			$total = $total[0]['total'];
		}

		$sql = "SELECT ${table_name}.name AS name, order_number, amount, email, phone, keyword, asin, percentage, created FROM ${table_name} 
					${where_post_author} 
					${search_params} 
					LIMIT ${limit} OFFSET ${offset}";

		$products = $wpdb->get_results($sql, ARRAY_A);
	}

	$data_set = [];

	$args = array(
		'posts_per_page'   => -1,
		'post_status'      => 'publish',
		'post_type'      => 'amazon_seller_prod',
		'order' => 'ASC'
	);

	if (!empty($_GET['job_id'])) {
		$args['include'] = array($_GET['job_id']);
	}

	if (!current_user_can('administrator')) {
		$args['author'] = get_current_user_id();
	} else if ($client > 0) {
		$args['author'] = clean_input($client);
	}

	$jobs = get_posts($args);

	if (!empty($jobs)) {
		foreach ($jobs as $job) {
			$asin_number = get_post_meta($job->ID, 'asin_number', true);
			$asin_category = get_post_meta($job->ID, 'asin_category', true);
			$asin_percentage = get_post_meta($job->ID, 'asin_percentage', true);

			if (!empty($asin_number)) {
				$asin_number = json_decode($asin_number);
			}
			if (!empty($asin_category)) {
				$asin_category = json_decode($asin_category);
			}
			if (!empty($asin_percentage)) {
				$asin_percentage = json_decode($asin_percentage);
			}

			foreach ($asin_number as $key => $v) {
				$data_set[] = [
					'client_id' => $job->post_author,
					'asin' => $v,
					'keywords' => explode(',', $asin_category[$key]),
					'percentage' => $asin_percentage[$key]
				];
			}
		}
	}

	$current_user = wp_get_current_user();

?>

	<?php if (is_user_logged_in()) : ?>
		<?php if (in_array('administrator', (array) $current_user->roles) || in_array('amazon_seller_client', (array) $current_user->roles)) : ?>
			<div class="amazon-seller-dashboard-admin">
				<form class="row" method="get">
					<div class="col-md-12">
						<!-- <input type="hidden" name="page" value="amazon-seller-dashboard-api-settings"> -->

						<h4><strong>Search</strong></h4>
						<hr>
						<div class="row">
							<div class="form-group col-md-4">
								<select class="form-control limits-dropdown" name="limit">
									<option <?php echo $limit == 10 ? 'selected' : ''; ?> value="10">10</option>
									<option <?php echo $limit == 50 ? 'selected' : ''; ?> value="50">50</option>
									<option <?php echo $limit == 100 ? 'selected' : ''; ?> value="100">100</option>
									<option <?php echo $limit == 500 ? 'selected' : ''; ?> value="500">500</option>
									<option <?php echo $limit == 1000 ? 'selected' : ''; ?> value="1000">1000</option>
								</select>
							</div>

							<?php if (current_user_can('administrator')) : ?>
								<div class="form-group col-md-4">
									<select class="form-control clients-dropdown" name="client">
										<option value="">Choose Client</option>
										<option <?php echo 'all' == $_GET['client'] ? 'selected' : ''; ?> value="all">All</option>
										<?php
										foreach ($users as $user) {
											$selected = $client == $user->ID ? 'selected' : '';
											echo '<option ' . $selected . ' value="' . $user->ID . '">' . esc_html($user->display_name) . ' [' . esc_html($user->user_email) . ']</option>';
										}
										?>
									</select>
								</div>
							<?php endif; ?>

							<div class="form-group col-md-4">
								<select class="form-control job-ids-dropdown" name="job_id">
									<option value="">Select</option>
									<?php
									if (!empty($jobs)) {
										foreach ($jobs as $job) {
											$selected = clean_input($_GET['job_id']) == $job->ID ? 'selected' : '';
											echo '<option ' . $selected . ' value="' . $job->ID . '">' . $job->post_title . '</option>';
										}
									}
									?>
								</select>
							</div>

							<div class="form-group col-md-4">
								<select class="form-control keywords-dropdown" name="keyword">
									<option value="">Select</option>
									<?php foreach ($data_set as $data) : ?>
										<?php foreach ($data['keywords'] as $keyword) : ?>
											<?php $selected = clean_input($_GET['keyword']) == $keyword ? 'selected' : ''; ?>
											<option <?php echo $selected; ?> value="<?php echo $keyword; ?>"> <?php echo $keyword; ?></option>
										<?php endforeach; ?>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="form-group col-md-4">
								<input type="text" class="form-control" placeholder="Order No." name="order_number" value="<?php echo clean_input($_GET['order_number']) ?? ''; ?>">
							</div>

							<?php if (current_user_can('administrator')) : ?>
								<div class="form-group col-md-4">
									<input type="text" class="form-control" placeholder="Phone" name="phone" value="<?php echo clean_input($_GET['phone']) ?? ''; ?>">
								</div>

								<div class="form-group col-md-4">
									<input type="text" class="form-control" placeholder="Email" name="email" value="<?php echo clean_input($_GET['email']) ?? ''; ?>">
								</div>

								<div class="form-group col-md-4">
									<input type="text" class="form-control" placeholder="Name" name="u_name" value="<?php echo clean_input($_GET['name']) ?? ''; ?>">
								</div>

								<div class="form-group col-md-4">
									<input type="date" class="form-control" placeholder="Name" name="fromDate" value="<?php echo clean_input($_GET['fromDate']) ?? ''; ?>">
								</div>

								<div class="form-group col-md-4">
									<input type="date" class="form-control" placeholder="Name" name="toDate" value="<?php echo clean_input($_GET['toDate']) ?? ''; ?>">
								</div>

							<?php endif; ?>
						</div>

						<div class="form-group">
							<p>
								<button class="btn btn-success" type="submit" name="submit">Apply</button>
								<a href="<?php echo get_the_permalink(); ?>">
									<button class="btn btn-warning" type="button" name="submit">Clear</button>
								</a>
							</p>
						</div>
					</div>
					<div class="col-md-12">
						<h4><strong>Results</strong></h4>
						<hr>
						<?php if (!empty($products)) : ?>
							<table class="table table-bordered table-hover">
								<thead>
									<tr class="text-center">
										<th scope="col" class="text-right">#</th>
										<th scope="col" class="text-center">Date</th>

										<?php if (current_user_can('administrator')) : ?>
											<th scope="col">Name</th>
											<th scope="col">Phone</th>
											<th scope="col">Email</th>
										<?php endif; ?>

										<th scope="col" class="text-left">Order No.</th>
										<th scope="col" class="text-center">Keywords</th>

										<?php if (current_user_can('administrator')) : ?>
											<th scope="col">ASIN</th>
										<?php endif; ?>

										<th scope="col" class="text-right">Amount</th>

										<?php if (current_user_can('administrator')) : ?>
											<th scope="col" class="text-right">Percentage</th>
											<th scope="col" class="text-right">Total after Calculation</th>
										<?php endif; ?>
									</tr>
								</thead>

								<tbody>
									<?php foreach ($products as $key => $product) : ?>
										<tr class="text-center">
											<th scope="row" class="text-right"><?php echo $key + 1; ?></th>
											<th scope="row" class="text-right"><?php echo calculate_local_date_time($product['created']); ?></th>

											<?php if (current_user_can('administrator')) : ?>
												<td><?php echo $product['name']; ?></td>
												<td><?php echo $product['phone']; ?></td>
												<td><?php echo $product['email']; ?></td>
											<?php endif; ?>

											<td><?php echo $product['order_number']; ?></td>
											<td class="text-center"><?php echo $product['keyword']; ?></td>

											<?php if (current_user_can('administrator')) : ?>
												<td class="text-center"><?php echo $product['asin']; ?></td>
											<?php endif; ?>

											<td class="text-right"><?php echo format_to_currency($product['amount']); ?></td>

											<?php if (current_user_can('administrator')) : ?>
												<td class="text-right"><?php echo $product['percentage']; ?></td>
												<td class="text-right"><?php echo seller_increment_calculation($product['amount'], $product['percentage']); ?></td>
											<?php endif; ?>
										<?php endforeach; ?>
								</tbody>

								<?php if (current_user_can('administrator')) : ?>
									<tfoot>
										<tr class="text-right">
											<td colspan="9">Total</td>
											<td>
												<label id="lblTotal">
													<?php echo seller_grand_total_calculation($products); ?>
												</label>
											</td>
										</tr>
									</tfoot>
								<?php endif; ?>

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
				</form>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<?php echo do_shortcode('[easy-login-form]'); ?>
	<?php endif; ?>
<?php

	return ob_get_clean();
}
