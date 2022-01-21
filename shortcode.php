<?php
defined('ABSPATH') or die('No script kiddies please!');

function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

add_action('init', 'save_seller_survey_data_to_db');
function save_seller_survey_data_to_db()
{
    if (isset($_POST['submit_admin_product_survey'])) {

        if (!empty($_POST['name']) && !empty($_POST['order_number']) && !empty($_POST['amount']) && !empty($_POST['email']) && !empty($_POST['phone'])) {
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && ($_POST['email'] === $_POST['confirm_email'])) {

                global $wpdb;
                $table = $wpdb->prefix . 'amazon_seller_products';
                $term_meta = $wpdb->prefix . 'termmeta';

                $term_id = clean_input($_POST['keyword']);
                $sql = "SELECT meta_value FROM ${term_meta}  
				WHERE meta_key='user_id' AND term_id=${term_id}";

                $client = $wpdb->get_results($sql, ARRAY_A);

                $data = [
                    'client_id' => !empty($client) ? $client[0]['meta_value'] : -1,
                    'keyword_id' => clean_input($_POST['keyword']),
                    'name' => clean_input($_POST['name']),
                    'order_number' => clean_input($_POST['order_number']),
                    'amount' => clean_input($_POST['amount']),
                    'email' => clean_input($_POST['email']),
                    'phone' => clean_input($_POST['phone']),
                ];
                $format = [
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%f',
                    '%s',
                    '%s',
                ];
                $wpdb->insert($table, $data, $format);

                if (!empty($wpdb->insert_id)) {
                    wp_redirect($_POST['redirect_url'] . '?status=success');
                    exit;
                } else {
                    wp_redirect($_POST['redirect_url'] . '?status=error');
                    exit;
                }
            }
        }
    }
}

add_shortcode('amazon-seller-survey-form', 'amazon_survey_seller_form');
function amazon_survey_seller_form()
{
    $status = !empty($_GET['status']) ? $_GET['status'] : '';

    global $wpdb;
    $term_taxonomy = $wpdb->prefix . 'term_taxonomy';
    $terms = $wpdb->prefix . 'terms';
    $posts_table = $wpdb->prefix . 'posts';
    $terms_relationship = $wpdb->prefix . 'term_relationships';
    $sql = "SELECT ${posts_table}.post_status, ${terms}.term_id, ${terms}.name FROM ${term_taxonomy} 
				INNER JOIN ${terms} ON ${term_taxonomy}.term_id=${terms}.term_id 
				INNER JOIN ${terms_relationship} ON ${terms_relationship}.term_taxonomy_id=${term_taxonomy}.term_taxonomy_id 
				INNER JOIN ${posts_table} ON ${posts_table}.ID=${terms_relationship}.object_Id 
				AND ${term_taxonomy}.taxonomy='keywords'";

    $keywords = $wpdb->get_results($sql, ARRAY_A);
?>
    <div class="amazon-seller-dashboard-admin">

        <?php if ($status == 'success') : ?>
            <div class="alert alert-success" role="alert">
                Thanks. Your information was saved successfully.
            </div>
        <?php exit;
        endif; ?>

        <?php if ($status == 'error') : ?>
            <div class="alert alert-danger" role="alert">
                Sorry. Your information was not saved. Please try again.
            </div>
        <?php exit;
        endif; ?>

        <form method="post" id="ProductSurveyForm">
            <div class="form-group">
                <label for="keyword">Choose the keyword <span class="required">*</span></label>
                <select class="form-control hybrid-select" id="keyword" name="keyword">
                    <option value="">Choose</option>
                    <?php foreach ($keywords as $keyword) : if ($keyword['post_status'] != 'publish') : continue;
                        endif; ?>
                        <option value="<?php echo $keyword['term_id']; ?>"> <?php echo $keyword['name']; ?> </option>
                    <?php endforeach; ?>
                </select>
                <div class="validation-box"></div>
            </div>
            <div class="form-group">
                <label for="name">Name <span class="required">*</span></label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Your answer">
                <div class="validation-box"></div>
            </div>
            <div class="form-group">
                <label for="order_number">Order number <span class="required">*</span></label>
                <input type="text" class="form-control" name="order_number" id="order_number" placeholder="Your answer">
                <div class="validation-box"></div>
            </div>
            <div class="form-group">
                <label for="amount">Amount of purchase <span class="required">*</span></label>
                <input type="text" class="form-control" name="amount" id="amount" placeholder="Your answer">
                <div class="validation-box"></div>
            </div>
            <div class="form-group">
                <label for="email">Email linked to your Paypal <span class="required">*</span></label>
                <br>
                <small>If you place any info here besides your PayPal email you will not be reimbursed.</small>
                <input type="text" class="form-control" name="email" id="email" placeholder="Your answer">
                <div class="validation-box"></div>
            </div>
            <div class="form-group">
                <label for="confirm_email">Confirm your email, double check it for any errors, and ensure there are no mistakes. (IMPORTANT) <span class="required">*</span></label>
                <br>
                <small>If you place any info here besides your PayPal email you will not be reimbursed.</small>
                <input type="text" class="form-control" name="confirm_email" id="confirm_email" placeholder="Your answer">
                <div class="validation-box"></div>
            </div>
            <div class="form-group">
                <label for="phone">Phone number <span class="required">*</span></label>
                <input type="text" class="form-control" name="phone" id="phone" placeholder="Your answer">
                <div class="validation-box"></div>
            </div>
            <input type="hidden" name="redirect_url" value="<?php echo get_the_permalink(); ?>">
            <input type="checkbox" id="tos-status-checkbox" />
            <a target="_blank" href="<?php echo get_settings('tos_uri_form'); ?>">Accept terms and condition</a>
            <br>
            <br>
            <button disabled type="submit" id="submit_admin_product_survey" name="submit_admin_product_survey" class="form-submit-btn btn btn-primary">Submit</button>
        </form>
    </div>

<?php
}
