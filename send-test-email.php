<?php
/**
 * Plugin Name: Send Test Email
 * Plugin URI: https://bitbucket.org/pbrocks/send-test-email
 * Description: Send a test email using AJAX, receive results in the current screen and record in debug log.
 * Version: 1.3
 * Author: pbrocks
 * Author URI: https://bitbucket.org/pbrocks/send-test-email
 * Text Domain: send-test-email
 * Contributors: pbrocks
 */

add_filter(
	'wp_mail_from_name', function( $name ) {
		return 'PMPro Email Master';
	}
);

add_filter(
	'wp_mail_from', function( $email ) {
		return 'pmrpo-master@' . $_SERVER['HTTP_HOST'];
	}
);
add_action( 'admin_menu', 'send_test_email_menu' );
function send_test_email_menu() {
	global $admin_screen_hook;
	$admin_screen_hook = add_dashboard_page( __( 'Send Test Email', 'send-test-email' ), __( 'Send Test Email', 'send-test-email' ), 'manage_options', 'send-test-email.php', 'send_test_email_page' );
}

function send_test_email_page() {
	?>
	<div class="wrap">
		============================
		<h2><?php esc_attr_e( 'Send Test Email', 'send-test-email' ); ?></h2>
		
		<form id="send-email-form" method="POST">

			<div>
	<?php
	wp_dropdown_users(
		array(
			// 'echo' => 0,
			'id' => 'userid',
		)
	);
	?>

				<input type="text" name="send_to_email" id="send_to_email" value="testing@umm.rocks" />
				<input type="hidden" name="send-emails" value="send_emails" />
				<input type="submit" name="send-email-submit" id="send_email_submit" class="button-primary" value="<?php esc_attr_e( 'Send Test Email', 'send-test-email' ); ?>"/>
				<img src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>" class="waiting" id="send_email_loading" style="display:none;"/>
			</div>
		</form>
		<div id="send_email_results"></div>
		============================
	</div>
	<?php
	echo '<pre>';
	print_r( $_SERVER['HTTP_HOST'] );
	echo '</pre>';
}

add_action( 'admin_enqueue_scripts', 'send_email_load_scripts' );
function send_email_load_scripts( $hook ) {
	global $admin_screen_hook;
	if ( $hook !== $admin_screen_hook ) {
		return;
	}

	wp_enqueue_script( 'send-email-ajax', plugin_dir_url( __FILE__ ) . 'js/send-email.js', array( 'jquery' ) );
	wp_localize_script(
		'send-email-ajax', 'send_email_object', array(
			'send_email_nonce' => wp_create_nonce( 'send-email-nonce' ),
		)
	);

}

add_action( 'wp_ajax_send_email_get_results', 'send_email_process_ajax' );
function send_email_process_ajax() {
	if ( ! isset( $_POST['send_email_nonce'] ) || ! wp_verify_nonce( $_POST['send_email_nonce'], 'send-email-nonce' ) ) {
		die( 'Permissions check failed' );
	}

	$stuff = $_POST;
	$user_id = $stuff['userid'];
	$stuff['user_email'] = get_userdata( $user_id )->user_email;

	$headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
	$stuff['sending_mail'] = wp_mail( $stuff['send_to_email'], 'Test from the client site', 'This is a simple test email.', $headers );
	if ( null === $stuff['sending_mail'] ) {
		$stuff['sent_mail'] = 'sending_mail did not go';
		add_to_log( $stuff );
	} else {
		$stuff['sent_mail'] = 'sending_mail = kaching';
		add_to_log( $stuff );
	}
	echo '<pre>';
	json_encode( $stuff );
	print_r( $stuff );
	echo '</pre>';
	die();
}

function add_to_log( $message ) {
	if ( WP_DEBUG === true ) {
		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( print_r( $message, true ) );
		} else {
			error_log( $message );
		}
	}
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pmpro_test_email_action_links' );
/**
 * Function to add links to the plugin action links
 *
 * @param array $links Array of links to be shown in plugin action links.
 */
function pmpro_test_email_action_links( $links ) {
	if ( current_user_can( 'manage_options' ) ) {
		$new_links = array(
			'<a href="' . get_admin_url( null, 'index.php?page=send-test-email.php' ) . '">' . __( 'Send Test', 'send-test-email' ) . '</a>',
		);
	}
	return array_merge( $new_links, $links );
}
