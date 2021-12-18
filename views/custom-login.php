<?php

// login form fields
function cn_custom_login_form_fields()
{

	ob_start(); ?>
	<div class="amazon-seller-dashboard-admin">
		<form class="form-signin cn_custom_form" id="cn_custom_login_form" action="" method="post">
			<?php if (!is_user_logged_in()) : ?>
				<div class="form-group">
					<input type="text" class="form-control" name="cn_custom_user_login" placeholder="Username" autofocus="" />
				</div>
				<br>
				<div class="form-group">
					<input type="password" class="form-control" name="cn_custom_user_pass" placeholder="Password" />
				</div>
				<br>
				<div class="form-group">
					<label class="checkbox">
						<input type="checkbox" checked value="remember-me" id="rememberMe" name="rememberMe"> Remember me
					</label>
				</div>
				<input type="hidden" name="cn_custom_login_nonce" value="<?php echo wp_create_nonce('cn_custom-login-nonce'); ?>" />
				<div class="form-group">
					<button id="cn_custom_login_submit" class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
				</div>
			<?php else : ?>
				<input type="hidden" name="cn_custom_logout_nonce" value="<?php echo wp_create_nonce('cn_custom-logout-nonce'); ?>" />
				<div class="form-group">
					<button name="cn_custom_logout_submit" class="btn btn-primary btn-block">Logout</button>
				</div>
			<?php endif; ?>
		</form>
		<?php cn_custom_show_error_messages(); ?>
	</div>

<?php
	return ob_get_clean();
}


// logs a member in after submitting a form
function cn_custom_login_member()
{

	if (isset($_POST['cn_custom_logout_submit']) && wp_verify_nonce($_POST['cn_custom_logout_nonce'], 'cn_custom-logout-nonce')) {

		wp_logout();
		wp_redirect(home_url());
		exit;
	}

	if (isset($_POST['cn_custom_user_login']) && wp_verify_nonce($_POST['cn_custom_login_nonce'], 'cn_custom-login-nonce')) {

		// this returns the user ID and other info from the user name
		$user = get_userdatabylogin($_POST['cn_custom_user_login']);

		if (!$user) {
			// if the user name doesn't exist
			cn_custom_errors()->add('empty_username', __('Invalid username'));
		}

		if (!isset($_POST['cn_custom_user_pass']) || $_POST['cn_custom_user_pass'] == '') {
			// if no password was entered
			cn_custom_errors()->add('empty_password', __('Please enter a password'));
		}

		// check the user's login with their password
		if (!wp_check_password($_POST['cn_custom_user_pass'], $user->user_pass, $user->ID)) {
			// if the password is incorrect for the specified user
			cn_custom_errors()->add('empty_password', __('Incorrect password'));
		}

		// retrieve all error messages
		$errors = cn_custom_errors()->get_error_messages();

		// only log the user in if there are no errors
		if (empty($errors)) {
			$creds = array();

			if ($_POST['rememberMe']) :
				$creds['remember'] = true;
				wp_setcookie($_POST['cn_custom_user_login'], $_POST['cn_custom_user_pass'], true);
			endif;

			$creds['user_login'] 	= 	$_POST['cn_custom_user_login'];
			$creds['user_password'] = 	$_POST['cn_custom_user_pass'];

			$user = wp_signon($creds, false);

			if (is_wp_error($user)) :
				cn_custom_errors()->add('login_error', $user->get_error_message());
			endif;

			header('Location: ' . $_SERVER['REQUEST_URI']);
		}
	}
}
add_action('init', 'cn_custom_login_member');


// used for tracking error messages
function cn_custom_errors()
{
	static $wp_error; // Will hold global variable safely
	return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}


// displays error messages from form submissions
function cn_custom_show_error_messages()
{
	if ($codes = cn_custom_errors()->get_error_codes()) {
		echo '<div class="alert alert-danger" style="margin-top:20px;">';
		// Loop error codes and display errors
		foreach ($codes as $code) {
			$message = cn_custom_errors()->get_error_message($code);
			echo __('Error') . '</strong>: ' . $message . '<br/>';
		}
		echo '</div>';
	}
}

add_shortcode('easy-login-form', 'cn_custom_login_form_fields');
