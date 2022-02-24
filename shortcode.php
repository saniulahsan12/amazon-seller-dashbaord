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

                $data = [
                    'client_id' => clean_input($_POST['client_id']),
                    'keyword' => clean_input($_POST['keyword']),
                    'asin' => clean_input($_POST['asin']),
                    'percentage' => clean_input($_POST['percentage']),
                    'name' => clean_input($_POST['name']),
                    'order_number' => clean_input($_POST['order_number']),
                    'amount' => clean_input($_POST['amount']),
                    'email' => clean_input($_POST['email']),
                    'phone' => clean_input($_POST['phone']),
                ];
                $format = [
                    '%d',
                    '%s',
                    '%s',
                    '%f',
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

    $data_set = [];

    $args = array(
        'posts_per_page'   => -1,
        'post_status'      => 'publish',
        'post_type'      => 'amazon_seller_prod',
    );

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
?>

    <script>
        function setMetaValueSellerProd(params) {
            var selection = jQuery(params).find(":selected");
            jQuery('#amazon_seller_prod_client_id').val(selection.data('client'));
            jQuery('#amazon_seller_prod_asin').val(selection.data('asin'));
            jQuery('#amazon_seller_prod_percentage').val(selection.data('percentage'));
        }
    </script>
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
                <select onchange="setMetaValueSellerProd(this)" class="form-control hybrid-select" id="keyword" name="keyword">
                    <option value="">Choose</option>
                    <?php foreach ($data_set as $data) : ?>
                        <?php foreach ($data['keywords'] as $keyword) : ?>
                            <option data-client="<?php echo $data['client_id']; ?>" data-asin="<?php echo $data['asin']; ?>" data-percentage="<?php echo $data['percentage']; ?>" value="<?php echo $keyword; ?>
                                "> <?php echo $keyword; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" id="amazon_seller_prod_client_id" name="client_id" value="">
                <input type="hidden" id="amazon_seller_prod_asin" name="asin" value="">
                <input type="hidden" id="amazon_seller_prod_percentage" name="percentage" value="">
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
