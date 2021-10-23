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
        // dump out the POST data
        var_dump($_POST);

        if (!empty($_POST['name']) && !empty($_POST['order_number']) && !empty($_POST['amount']) && !empty($_POST['email']) && !empty($_POST['phone'])) {
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && ($_POST['email'] === $_POST['confirm_email'])) {

                global $wpdb;
                $table = $wpdb->prefix . 'amazon_seller_products';
                $data = [
                    'product_id' => clean_input($_POST['product']),
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
                    '%d',
                    '%s',
                    '%s',
                ];
                $wpdb->insert($table, $data, $format);
                print_r($wpdb->insert_id);
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
    $args = array(
        'post_type' => 'amazon_seller_prod',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'showposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $loop = new WP_Query($args);
?>
    <div class="amazon-seller-dashboard-admin">

        <?php if ($status == 'success') : ?>
            <div class="alert alert-success" role="alert">
                Thanks. Your information was saved successfully.
            </div>
        <?php exit; endif; ?>

        <?php if ($status == 'error') : ?>
            <div class="alert alert-danger" role="alert">
                Sorry. Your information was not saved. Please try again.
            </div>
        <?php exit; endif; ?>

        <form method="post" id="ProductSurveyForm">
            <div class="form-group">
                <label for="product">Choose the product <span class="required">*</span></label>
                <select class="form-control" id="product" name="product">
                    <option value="">Choose</option>
                    <?php while ($loop->have_posts()) : $loop->the_post(); ?>
                        <option value="<?php echo get_the_ID(); ?>"> <?php echo get_the_title(); ?> </option>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>
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
            <button type="submit" id="submit_admin_product_survey" name="submit_admin_product_survey" class="form-submit-btn btn btn-primary">Submit</button>
        </form>
    </div>

<?php
}
