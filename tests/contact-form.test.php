<?php
/** Isolated delivery tests. wp_mail is stubbed; no messages are sent. */
define( 'ABSPATH', __DIR__ );
class Contact_Response extends Exception {
	public $success;
	public $data;
	public $status;
	public function __construct( $success, $data, $status ) { $this->success = $success; $this->data = $data; $this->status = $status; }
}
function wp_send_json_error( $data, $status = 200 ) { throw new Contact_Response( false, $data, $status ); }
function wp_send_json_success( $data ) { throw new Contact_Response( true, $data, 200 ); }
function wp_verify_nonce( $nonce, $action ) { return 'valid' === $nonce && 'jednotka_contact' === $action; }
function wp_unslash( $s ) { return stripslashes( $s ); }
function sanitize_text_field( $s ) { return trim( preg_replace( '/[\r\n\t]+/', ' ', strip_tags( $s ) ) ); }
function sanitize_textarea_field( $s ) { return trim( strip_tags( $s ) ); }
function is_email( $s ) { return is_string( $s ) && filter_var( $s, FILTER_VALIDATE_EMAIL ); }
function wp_salt( $context ) { return 'test-secret'; }
function get_option( $key ) { return $GLOBALS['recipient']; }
function get_transient( $key ) { return $GLOBALS['rates'][ $key ] ?? false; }
function set_transient( $key, $value, $seconds ) { $GLOBALS['rates'][ $key ] = $value; }
function wp_mail( ...$args ) { $GLOBALS['mail'][] = $args; return $GLOBALS['mail_success']; }
function sanitize_file_name( $s ) { return basename( $s ); }
function wp_check_filetype_and_ext( $file, $name, $types ) { return array( 'ext' => str_ends_with( $name, '.pdf' ) ? 'pdf' : false ); }
eval( 'namespace Jednotka\\Elementor; function is_uploaded_file( $file ) { return "/tmp/upload" === $file; }' );
require __DIR__ . '/../includes/class-contact-form.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
function submit( $overrides = array(), $method = 'POST' ) {
	$_POST = array_replace( array( 'nonce' => 'valid', 'name' => '<b>Test Visitor</b>', 'email' => 'visitor@example.com', 'phone' => '+421 900 000 000', 'interest' => 'Web', 'message' => "Line one\nLine two", 'website' => '' ), $overrides );
	$_SERVER = array( 'REQUEST_METHOD' => $method, 'REMOTE_ADDR' => '192.0.2.42' );
	$_FILES = $GLOBALS['files'] ?? array();
	try { \Jednotka\Elementor\Contact_Form::submit(); } catch ( Contact_Response $response ) { return $response; }
	throw new RuntimeException( 'Expected a JSON response' );
}
$GLOBALS['recipient'] = 'admin@example.com'; $GLOBALS['rates'] = array(); $GLOBALS['mail'] = array(); $GLOBALS['mail_success'] = true;
foreach ( array(
	array( array( 'nonce' => 'expired' ), 403 ),
	array( array( 'nonce' => array( 'valid' ) ), 403 ),
	array( array( 'name' => '' ), 400 ),
	array( array( 'email' => 'invalid' ), 400 ),
	array( array( 'email' => "user@example.com\r\nBcc: evil@example.com" ), 400 ),
	array( array( 'website' => 'bot' ), 400 ),
	array( array( 'message' => array( 'bad' ) ), 400 ),
	array( array( 'message' => str_repeat( 'x', 20001 ) ), 400 ),
) as $case ) { check( submit( $case[0] )->status === $case[1], 'Reject malformed submission' ); }
check( submit( array(), 'GET' )->status === 405, 'Require POST' );
check( count( $GLOBALS['mail'] ) === 0, 'Invalid requests never send mail' );
$response = submit( array( 'recipient' => 'attacker@example.com' ) );
check( $response->success, 'Successful delivery' );
$delivered_mail = $GLOBALS['mail'][0];
check( 'admin@example.com' === $delivered_mail[0], 'Recipient cannot be supplied by visitor' );
check( str_contains( $delivered_mail[2], 'Test Visitor' ) && ! str_contains( $delivered_mail[2], '<b>' ), 'Sanitize name' );
check( str_contains( $delivered_mail[2], "Line one\nLine two" ), 'Preserve message line breaks' );
check( in_array( 'Reply-To: visitor@example.com', $delivered_mail[3], true ), 'Reply directly to visitor' );
check( submit()->status === 429 && count( $GLOBALS['mail'] ) === 1, 'Rate-limit repeated delivery' );
$GLOBALS['rates'] = array(); $GLOBALS['mail_success'] = false;
check( submit()->status === 500, 'Mail failure is never reported as success' );
$GLOBALS['rates'] = array(); $GLOBALS['recipient'] = 'invalid';
check( submit()->status === 503, 'Invalid site recipient is rejected' );
$GLOBALS['recipient'] = 'admin@example.com'; $GLOBALS['mail_success'] = true;
$upload = array( 'name' => 'brief.pdf', 'tmp_name' => '/tmp/upload', 'size' => 100, 'error' => UPLOAD_ERR_OK );
$GLOBALS['rates'] = array(); $GLOBALS['mail'] = array(); $GLOBALS['files'] = array( 'attachment' => $upload );
check( submit()->success && array( 'brief.pdf' => '/tmp/upload' ) === $GLOBALS['mail'][0][4], 'Attach a valid upload under its own name' );
foreach ( array( array( 'size' => 10485761 ), array( 'name' => 'virus.exe' ), array( 'tmp_name' => '/etc/passwd' ), array( 'error' => UPLOAD_ERR_PARTIAL ) ) as $bad ) {
	$GLOBALS['rates'] = array(); $GLOBALS['mail'] = array(); $GLOBALS['files'] = array( 'attachment' => array_replace( $upload, $bad ) );
	check( 400 === submit()->status && ! $GLOBALS['mail'], 'Reject an unsafe upload' );
}
$GLOBALS['rates'] = array(); $GLOBALS['mail'] = array(); $GLOBALS['files'] = array( 'attachment' => array( 'error' => UPLOAD_ERR_NO_FILE ) );
check( submit()->success && array() === $GLOBALS['mail'][0][4], 'A file is optional' );
echo "Contact form delivery checks passed (no email sent).\n";
