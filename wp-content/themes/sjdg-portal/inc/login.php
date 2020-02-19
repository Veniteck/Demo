<?php

function dt_handle_login_endpoint()
{

    $errors = [];

    if (!isset($_POST['action'])) {
        $errors[] = 'Invalid Request';
    }

    if (!empty($errors)) {
        return new WP_REST_Response([
            'status' => 'failed',
            'message' => 'Your request could not be processed, please try again.',
            'invalid_field' => 'global',
        ]);
    }

    $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

    try {

        switch ($action) {

            case 'user-password-reset':

                $email = filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception(json_encode(['message' => 'Please enter a valid email address', 'field' => 'login-email']));
                }

                /**
                 * Check the users email exists
                 */
                $user_data = get_user_by('email', $email);

                if (empty($user_data)) {
                    throw new Exception(json_encode(['message' => 'No user exists with that email address', 'field' => 'login-email']));
                }

                $user_login = $user_data->user_login;
                $user_email = $user_data->user_email;
                $key = get_password_reset_key($user_data);

                if (is_wp_error($key)) {
                    throw new Exception(json_encode(['message' => 'Your request could not be completed. Please contact support. (ERR 1)', 'field' => 'global']));
                }

                $site_name = get_bloginfo('name');

                $subject = sprintf(__('[%s] Password Reset'), $site_name);
                $mailer = WC()->mailer();
                $content = wc_get_template_html('emails/email-password-reset.php', array(
                    'site_name' => $site_name,
                    'user_login' => $user_login,
                    'key' => $key,
                    'sent_to_admin' => false,
                    'plain_text' => false,
                    'email' => $mailer,
                ));
                $headers = "Content-Type: text/html\r\n";

                if (!$mailer->send($user_email, $subject, $content, $headers)) {
                    throw new Exception(json_encode(['message' => 'Your request could not be completed. Please contact support. (ERR 2)', 'field' => 'global']));
                }

                return new WP_REST_Response([
                    'status' => 'success',
                    'message' => 'Please check your email for a reset link.',
                ]);

                break;

            case 'user-login':

                /**
                 * Check the username / password have been submitted.
                 */

                $is_username = false;
                $username = false;

                if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    //throw new Exception( json_encode( [ 'message' => 'Please enter a valid email address', 'field' =>  'login-email' ] ) );
                    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                } else {

                    $is_username = true;
                    $username = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

                }

                $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

                if (empty($password)) {
                    throw new Exception(json_encode(['message' => 'Your password cannot be blank.', 'field' => 'login-password']));
                }

                if ($is_username) {
                    $user = get_user_by('login', $username);
                } else {
                    $user = get_user_by('email', $email);
                }

                if (empty($user)) {
                    throw new Exception(json_encode(['message' => 'Invalid username or password.', 'field' => 'global']));
                }

                $results = wp_signon(['user_login' => $user->user_login, 'user_password' => $password]);

                if (is_wp_error($results)) {
                    throw new Exception(json_encode(['message' => 'Invalid username or password.', 'field' => 'global']));
                }

                return new WP_REST_Response([
                    'status' => 'success',
                    'message' => 'You have successfully logged in.',
                ]);

                break;

            case 'final-password-reset':

                /**
                 * Check the users login / key match
                 */
                $key = filter_var($_POST['key'], FILTER_SANITIZE_STRING);
                $login = filter_var($_POST['login'], FILTER_SANITIZE_STRING);

                $user = check_password_reset_key($key, $login);

                if (is_wp_error($user)) {
                    throw new Exception(json_encode(['message' => 'Invalid password reset key. Please try again.', 'field' => 'global']));
                }

                /**
                 * Check the submitted password is valid (strength, length etc) and matches the confirmation
                 */
                $password_1 = $_POST['password_1'];
                $password_2 = $_POST['password_2'];

                if ($password_1 !== $password_2) {
                    throw new Exception(json_encode(['message' => 'Your password does not match the confirmation', 'field' => 'password-1']));
                }

                //password contains a digit
                $contains_digit = preg_match('/\d/', $password_1);

                //password contains a capital
                $contains_uppercase  = preg_match('/[A-Z]/', $password_1);

                //final password check including length
                if (strlen($password_1) < 8 || !$contains_digit || !$contains_uppercase) {
                    throw new Exception(json_encode(['message' => 'Your password must be at least 8 characters long and contain at least one capital and one number.', 'field' => 'password-1']));
                }

                /**
                 * Change the users password.
                 */
                reset_password($user, $password_1);

                return new WP_REST_Response([
                    'status' => 'success',
                    'message' => 'Your password has successfully been reset.',
                ]);

                break;

            default:

                throw new Exception('Your request could not be completed. Please contact support.', 'global');

                break;

        }

    } catch (\Exception $exception) {

        $data = json_decode($exception->getMessage());

        return new WP_REST_Response([
            'status' => 'failed',
            'message' => $data->message,
            'invalid_field' => $data->field,
        ]);

    }

}

/**
 * Register REST API Routes For Login functionality
 */
add_action('rest_api_init', function () {

    register_rest_route('dt', '/handle_login', array(
        'methods' => ['POST', 'OPTIONS'],
        'callback' => 'dt_handle_login_endpoint',
    ));

});

add_action('login_head', function () {

    ?>

    <style>

        .login h1 a{
            background-image: url( <?php echo get_field('site_main_logo', 'option'); ?> );
            width: 300px;
            background-repeat: no-repeat;
            background-position: 50% 50%;
            background-size: contain;
        }

        #backtoblog{
            display:none;
        }

    </style>

    <?php

});

//Public Login after password reset, uncheck the 'reset password required ACF'
add_action('after_password_reset', function ($user, $new_pass) {
    update_user_meta($user->ID, 'user_password_reset', 1);
}, 10, 2);