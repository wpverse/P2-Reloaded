<?php
/**
 * P2 Reloaded Theme
 *
 * Attachment Functions
 * Defines functions that handle attachments.
 *
 * @package P2Reloaded
 * @author mok <imoz39@gmail.com>
 * @since 2.9
 */

/**
 * Prints out a DIV element containing the attachments for a post.
 *
 * @return void
 */
function p2_attachments()
{
	
	$args = array(
		'post_type' => 'attachment',
		'post_parent' => get_the_ID()
	);
	$attachments = get_posts($args);
	
	if ( count($attachments) > 0 ):

?>
		<!-- attachments -->
			<div class="post-attachments">
				<p><strong>Attachments:</strong></p>
					<ul>
<?php foreach ( $attachments as $attachment ): ?>
						<li><?php echo wp_get_attachment_link($attachment->ID, '', true, false, basename($attachment->guid)) ?></li>
<?php endforeach; ?>
					</ul>
			</div>
		<!-- /attachments -->
<?php
		
	endif;
	
}

// --------------------------------------------------------------------------

/**
 * Handles AJAX requests for deleting attachments.
 *
 * @return void
 */
function p2_remove_attachment()
{

	check_ajax_referer('p2-remove-attachment', '_wp_nonce');
	
	$id = intval($_POST['attachment_id']);
	
	// Make sure we are deleting orphan attachments
	$attachment = get_post($id);
	if ( $attachment->post_parent == 0 )
	{
		wp_delete_attachment($id, true);
		echo json_encode(array(
			'status' => 'ok'
		));
		exit;
	}
	else
	{
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('Failed to delete attachment.', 'p2')
		));
		exit;
	}
	
}
add_action('wp_ajax_p2_remove_attachment', 'p2_remove_attachment');

// --------------------------------------------------------------------------