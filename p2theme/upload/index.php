<?php
/**
 * P2 Reloaded Theme
 * Handles attachment uploads.
 *
 * @package P2Reloaded
 * @author mok <imoz39@gmail.com>
 * @since 2.9
 */

// Load Wordpress environment
require_once '../../../../wp-load.php';

// Verify nonce
if ( ! isset($_POST['_wp_nonce']) || ! wp_verify_nonce($_POST['_wp_nonce'], 'p2-upload-attachment') )
{
	echo json_encode(array(
		'status' => 'error',
		'msg' => 'Unauthorized access.'
	));
	exit;
}

// Make sure we can upload files
if ( ! get_option('p2_attachments') || ! is_user_logged_in() )
{
	echo json_encode(array(
		'status' => 'error',
		'msg' => 'You do not have permission to upload files.'
	));
	exit;
}

// Now we need to rearrange the $_FILES array to a flat FILE array
$files = array();
foreach ( $_FILES['attachment'] as $key => $block )
{
	
	foreach ( $block as $index => $val )
	{
		$files["attachment-{$index}"][$key] = $val;
	}
	
}

// Load required WP files
require_once ABSPATH . '/wp-admin/includes/file.php';
require_once ABSPATH . '/wp-admin/includes/image.php';

$hits = $misses = array();

// Process the files
$_FILES = $files;
$keys = array_keys($_FILES);
foreach ( $keys as $key )
{
	
	$file = wp_handle_upload($_FILES[$key], array('test_form' => false));
	
	if ( ! $file )
	{
		$misses[] = $_FILES[$key]['name'];
		continue;
	}
	
	$name = $_FILES[$key]['name'];
	$name_parts = pathinfo($name);
	$name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
	
	$url = $file['url'];
	$type = $file['type'];
	$file = $file['file'];
	$title = $name;
	$content = '';
	
	// Construct attachments array
	$attachment = array(
		'post_mime_type' => $type,
		'guid' => $url,
		'post_parent' => 0,
		'post_title' => $title,
		'post_content' => $content	
	);
	
	// Save
	$id = wp_insert_attachment($attachment, $file, 0);
	if ( is_wp_error($id) )
	{
		@unlink($file);
		$misses[] = $_FILES[$key]['name'];
	}
	else
	{
		$hits[$id] = basename($file);
	}
	
}

header('Content-type: text/plain', true);
echo json_encode(array(
	'status' => 'ok',
	'misses' => $misses,
	'hits' => $hits
));
exit;

?>