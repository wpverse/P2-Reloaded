<?php
/**
 * P2 Reloaded Theme
 *
 * Posting Functions
 * Defines functions responsible for handling posts.
 *
 * @package P2Reloaded
 * @author mok <imoz39@gmail.com>
 * @since 2.9
 */

/**
 * Returns TRUE if the current user can post. Depends if anonymous posting 
 * is enabled and if the user is logged on.
 *
 * @return boolean
 */
function p2_can_post()
{

	return ( get_option('p2_anonymous_posting') || is_user_logged_in() );
	
}

// --------------------------------------------------------------------------

/**
 * Handles post content retrieval requests and content save requests sent
 * via AJAX.
 *
 * @return void
 */
function p2_edit_post()
{
	
	check_ajax_referer('p2-edit-post', '_wp_nonce');
	
	// Make sure we can edit posts
	if ( ! current_user_can('edit_posts') )
	{
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('You are not allowed to edit posts.')
		));
		exit;
	}
	
	$post_id = intval($_POST['post_id']);
	
	// If we have a post content then we need to save it.
	if ( isset($_POST['post_content']) )
	{
	
		// Grab the new post content and update the database
		$post_content = trim($_POST['post_content']);
		wp_update_post(array(
			'ID' => $post_id,
			'post_content' => $post_content
		));
		
		$post_content = apply_filters('the_content', $post_content);
		
		// Echo out a success response
		echo json_encode(array(
			'status' => 'ok',
			'post_content' => $post_content
		));
		exit;
	
	}
	else
	{
		
		// Grab the post and echo out a JSON response
		$post = get_post($post_id);
		echo json_encode(array(
			'status' => 'ok',
			'post_content' => $post->post_content
		));
		exit;
		
	}
	
	
}
add_action('wp_ajax_p2_edit_post', 'p2_edit_post');

// --------------------------------------------------------------------------

/**
 * Handles new posts sent via AJAX.
 *
 * @return void
 * @todo add more error checking. (much like p2_new_comment())
 */
function p2_new_post()
{
	
	global $current_user;
	
	check_ajax_referer('p2-post', '_wp_nonce');
	
	if ( ! p2_can_post() )
	{
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('You are not allowed to post.')
		));
		exit;
	}
	
	$msg = $_POST['message'];
	$tags = $_POST['tags'];
	
	$msg = stripcslashes($msg);
	$msg = p2_convert_smart_quotes($msg);
	
	$users = array();
	list($title, $content, $users) = p2_parse_post_message($msg);
	
	if ( ! get_option('p2_enable_tags') )
	{
		$tags = '';
	}
	
	// For anonymous posting, mark the post as draft first and strip tags.
	$status = 'publish';
	if ( ! is_user_logged_in() )
	{
		$status = 'draft';
		$content = strip_tags($content);
		$content = p2_linkify($content);
	}
	
	// Automatically add paragraphs
	$content = wpautop($content);
	
	// Insert the post
	$id = wp_insert_post(array(
		'post_title' => $title,
		'post_content' => $content,
		'post_status' => $status,
		'post_author' => $current_user->ID,
		'tags_input' => $tags
	));
	
	if ( $id )
	{
	
		$permalink = get_permalink($id);
		$site_name = get_option('blogname');
		
		// If we have attachments, attach them to the post.
		if ( isset($_POST['attachments']) )
		{
			$attachments = array_filter(explode(',', $_POST['attachments']));
			foreach ( $attachments as $attach_id )
			{ 
				wp_update_post(array(
					'ID' => $attach_id,
					'post_parent' =>  $id
				));
			}
		}
		
		// If this is an anonymous post notify administrators of the new post
		if ( ! is_user_logged_in() )
		{
			
			$url = admin_url("post.php?post=221&action=edit");
			$admins = get_users(array('role' => 'administrator'));
			
			$message = "<p>Dear %s,</p>
			<p>An anonymous poster has posted a new post: &quot;{$title}&quot;</p>
			<p>{$content}</p>
			<p><a href=\"{$url}\">View Post in the Admin Panel</a></p>
			<p>{$site_name}</p>";
			
			$to_notify = array();
			foreach ( $admins as $a )
			{
				$to_notify[] = array(
					'username' => $a->display_name,
					'email' => $a->user_email
				);
			}
			
			p2_notify($to_notify, 'An anonymous poster has posted a new post', $message);
			
		}
	
		// Notify tagged users
		if ( $users && get_option('p2_notifications') )
		{
			
			$message = "<p>Dear %s,</p>
			<p>You have been tagged in the post &quot;{$title}&quot;</p>
			<p>{$content}</p>
			<p><a href=\"{$permalink}\">View Post</a></p>
			<p>{$site_name}</p>";
			
			$tagged = array();
			foreach ( $users as $user )
			{
				$tagged[$user->user_email] = $user->display_name;
			}
			
			p2_notify($tagged, "You have been tagged on the post {$title}", $message);
			
		}
		
		// Add the author to the array of subscribers and then add the post meta
		$tagged[$current_user->user_email] = $current_user->display_name;
		add_post_meta($id, '_p2_subscribers', $tagged);
		
		query_posts(array(
			'p' => $id
		));
		
		$GLOBALS['norespond'] = true;
		
		ob_start();
		get_template_part('theloop');
		$html = ob_get_contents();
		ob_end_clean();
		
		// Create output JSON array
		$out = array(
			'status' => 'ok',
			'id' => $id,
			'html' => $html
		);
		
		// For anonymous posters remove the HTML.
		if ( ! is_user_logged_in() )
		{
			unset($out['html']);
		}
		
		echo json_encode($out);
		exit;
	
	}
	else
	{
		echo json_encode(array(
			'status' => 'error',
			'last_refresh' => time(),
			'msg' => __('Failed to save new post.')
		));
		exit;
	}
	
}
add_action('wp_ajax_p2_new_post', 'p2_new_post');
add_action('wp_ajax_nopriv_p2_new_post', 'p2_new_post');

// --------------------------------------------------------------------------

/**
 * Parses posts messages that are sent via AJAX checking for user tags and
 * detects if a custom title is added in and removes it (if it exists).
 * It also sends out notifications to the tagged users.
 *
 * @param string $msg the message
 * @return array 3-element array: the post title, content and array of tagged users
 */
function p2_parse_post_message($msg)
{
	
	// Check for titles
	if ( preg_match('/\s+title\s*=\s*"([^"]+)"/', $msg, $matches)  )
	{
		$title = substr($matches[1], 0, 50);
		$content = preg_replace('/title\s*=\s*"([^"]+)"/', '', $msg);
	}
	else
	{
		
		$max_len = 50;
		if ( strlen($msg) > 50 )
		{
			$title = wp_html_excerpt($msg, $max_len) . '...';
		}
		else
		{
			$title = $msg;
		}
		
		$content = $msg;
		
	}
	
	$title = str_replace("\n", '', $title);

	// If we still have no title, check if we have a video or image post and
	// set the title accordingly
	if ( ! $title )
	{
		if ( preg_match('/<object|<embed/', $content) )
		{
			$title = 'Video Post';
		}
		elseif ( preg_match('/<img/', $content) )
		{
			$title = 'Image Post';
		}
	}

	// Check for tagged users and notify them.
	$tagged_users = array();
	preg_match_all('/(@[^\s]+\,?)/', $content, $user_matches);
	if ( count($user_matches[1]) > 0 )
	{
		
		foreach ( $user_matches[1] as $username )
		{
			
			$username = trim($username, ',');
			$username = substr($username, 1);
			
			if ( ! in_array($username, $tagged_users) )
			{
				$user = get_userdatabylogin($username);
				if ( $user )
				{
					$tagged_users[] = $user;
				}
			}
			
		}
		
	}
	
	return array($title, $content, $tagged_users);
	
}

// --------------------------------------------------------------------------

/**
 * Prints the post's date in a HTML 5-friendly format.
 *
 * @return void
 */
function p2_post_date()
{
?>

	<time datetime="<?php the_time('c') ?>"><?php the_time(get_option('date_format')) ?> <?php the_time(get_option('time_format')) ?></time>

<?php	
}

// --------------------------------------------------------------------------

/**
 * Helper function for displaying pretty post navigation links
 *
 * @return void
 */
function p2_post_nav()
{
	
	ob_start();
	
	previous_post_link('%link', '&laquo; %title');
	echo ' &mdash; ';
	next_post_link('%link', '%title &raquo;');
	
	$contents = ob_get_contents();
	
	ob_end_clean();
	
	$contents = trim($contents, ' &mdash; ');
	echo $contents;
	
}

// --------------------------------------------------------------------------

/**
 * Handles AJAX requests for fetching new posts.
 *
 * @return void
 */
function p2_posts()
{
	
	check_ajax_referer('p2-get-posts', '_wp_nonce');
	
	$last = $_POST['last_refresh'];
	$GLOBALS['p2_posts_after'] = $last;
	add_filter('posts_where', 'p2_posts_last_refresh_filter');
	
	global $wp_query;
	$wp_query = new WP_Query(array(
		'posts_per_page' => -1
	));
	
	$contents = '';
	$ids = array();
	if ( have_posts() )
	{
		
		// Grab a block of HTML for all new posts
		ob_start();
		require_once TEMPLATEPATH . '/theloop.php';
		$contents = ob_get_contents();
		ob_end_clean();
		
		// Grab all new post IDs
		foreach ( $wp_query as $post )
		{
			$ids[] = $post->ID;
		}
		
	}
	
	echo json_encode(array(
		'status' => 'ok',
		'last_refresh' => time(),
		'since' => date('Y-m-d H:i:s', $last),
		'html' => $contents,
		'ids' => $ids
	));
	exit;
	
}
add_action('wp_ajax_p2_get_posts', 'p2_posts');
add_action('wp_ajax_nopriv_p2_get_posts', 'p2_posts');

// --------------------------------------------------------------------------

/**
 * Modifies the WHERE clause of WP_Query to add in a condition which restricts
 * fetched posts to posts created after a specified time. Set a global variable
 * p2_posts_after containing the timestamp of the time constraint.
 *
 * @param string $where SQL WHERE clause
 * @return string modified SQL WHERE clause
 */
function p2_posts_last_refresh_filter($where = '')
{
	
	$date = date('Y-m-d H:i:s', $GLOBALS['p2_posts_after']);
	$where .= " AND post_date > '{$date}'";
	
	return $where;
	
}

// --------------------------------------------------------------------------
