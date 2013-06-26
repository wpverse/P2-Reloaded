<?php
/**
 * P2 Reloaded Theme
 *
 * Comment Functions
 * Defines functions that handle comments and todo comments.
 *
 * @package P2Reloaded
 * @author mok <imoz39@gmail.com>
 * @since 2.9
 */

/**
 * Handles AJAX requests for fetching new comments.
 *
 * @return void
 */
function p2_comments()
{

	check_ajax_referer('p2-get-comments', '_wp_nonce');
	
	$last = $_POST['last_refresh'];
	$GLOBALS['p2_comments_after'] = $last;
	
	add_filter('comments_clauses', 'p2_comments_time_filter');
	$comments = get_comments(array(
		'status' => 'approve',
		'order' => 'ASC' // so the loop processes earlier comments first
	));
	
	$the_comments = array();
	if ( count($comments) > 0 )
	{
		foreach ( $comments as $comment )
		{
			ob_start();
			p2_display_comments($comment, array('avatar_size' => 36), 0);
			$the_comments[] = array(
				'post_id' => $comment->comment_post_ID,
				'id' => $comment->comment_ID,
				'parent' => $comment->comment_parent,
				'html' => ob_get_contents()
			);
			ob_end_clean();
		}
	}
	
	echo json_encode(array(
		'status' => 'ok',
		'last_refresh' => time(),
		'comments' => $the_comments
	));
	exit;
	
}
add_action('wp_ajax_p2_get_comments', 'p2_comments');
add_action('wp_ajax_nopriv_p2_get_comments', 'p2_comments');

// --------------------------------------------------------------------------

/**
 * Prints the comment's date in a HTML 5-friendly format.
 *
 * @return void
 */
function p2_comment_date()
{
?>

	<time datetime="<?php comment_date('c') ?>"><?php comment_date(get_option('date_format')) ?> <?php comment_date(get_option('time_format')) ?></time>

<?php
}

// --------------------------------------------------------------------------

/**
 * Convenience wrapper for comment_form().
 *
 * @return void
 */
function p2_comment_form()
{
	
	global $current_user;
	
	$logged_in_as = sprintf('<p class="comment-loggedin-as">(Logged in as %s. <a href="%s">Log out &rarr;</a>)</p>', $current_user->display_name, wp_logout_url());
	
	comment_form(array(
		'comment_notes_after' => '',
		'title_reply' => __('Reply', 'p2')
	));
	
}

// --------------------------------------------------------------------------

/**
 * Modifies the SQL clauses used in fetching comments and adds an additional
 * WHERE clause to return only comments that were added in a specified time.
 *
 * @param array $clauses the SQL clauses
 * @return array
 */
function p2_comments_time_filter($clauses = array())
{
	
	$date = date('Y-m-d H:i:s', $GLOBALS['p2_comments_after']);
	$clauses['where'] .= " AND comment_date > '{$date}'";
	return $clauses;
	
}

// --------------------------------------------------------------------------

/**
 * Callback function for displaying comments.
 *
 * @param object $comment the comment
 * @param array $args arguments passed to wp_list_comments()
 * @param integer $depth comment depth
 * @param object $post optional post object
 * @since 2.9
 */
function p2_display_comments($comment, $args, $depth, $post = null)
{
	
	// Set the current comment
	$GLOBALS['comment'] = $comment;
	
	// Grab a copy of the current post if $post isn't provided
	if ( ! $post )
	{
		global $wp_query;
		$post = $wp_query->post;
	}
	
	// Determine classes
	$classes = array();
	
	$todo = get_comment_meta(get_comment_ID(), 'p2_todo', true);
	if ( $todo )
	{
		$classes[] = 'todo-comment-'. $todo;
	}
	
	$marked = get_comment_meta(get_comment_ID(), 'p2_todo_marked_as', true);
	if ( $marked ) {
		$classes[] = 'todo-'. $marked;
	}
	
?>

	<li id="comment-<?php comment_ID() ?>" <?php comment_class($classes) ?>>
		
		<a name="comment-<?php comment_ID() ?>"></a>
		
<?php if ( $todo ): ?>

		<div class="comment-content" id="comment-content-<?php comment_ID() ?>">
			<p><label><input type="checkbox" class="comment-todo" value="<?php comment_ID() ?>"<?php if ( $todo == 'checked' ): ?> checked<?php endif; ?>> <?php echo get_comment_text() ?></label></p>
		</div>
		
		<div class="comment-info">
			<p class="comment-author vcard comment-date">&ndash; added by <cite><?php comment_author_link() ?></cite> on <?php p2_comment_date() ?></p>
			<p class="comment-meta">
				<a href="<?php comment_link() ?>">Permalink</a>
				<?php comment_reply_link(array_merge($args, array(
					'depth' => $depth,
					'max_depth' => $args['max_depth'],
					'before' => ' | '
				)), $comment, $post) ?>
				<?php if ( $args['max_depth'] != $depth && current_user_can('edit_comment') ): ?> | <?php endif ?>
				<?php edit_comment_link(__('Edit', 'p2')) ?>
			</p>
			<br class="clear">
		</div>

<?php elseif ( $marked ): ?>

		<div class="comment-content" id="comment-content-<?php comment_ID() ?>">
			<?php comment_text() ?>
		</div>
		
		<div class="comment-info">
			<p class="comment-author vcard comment-date">&mdash; <?php echo $marked ?> by <cite><?php comment_author_link() ?></cite> on <?php p2_comment_date() ?></p>
			<p class="comment-meta">
				<a href="<?php comment_link() ?>">Permalink</a>
				<?php comment_reply_link(array_merge($args, array(
					'depth' => $depth,
					'max_depth' => $args['max_depth'],
					'before' => ' | '
				))) ?>
				<?php if ( $args['max_depth'] != $depth && current_user_can('edit_comment') ): ?> | <?php endif ?>
				<?php edit_comment_link('Edit') ?>
			</p>
			<br class="clear">
		</div>

<?php else: ?>
		<div class="avatar">
			<?php echo get_avatar($comment, 36) ?>
		</div>
		
		<div class="comment-info">
			<p class="comment-author vcard"><cite><?php comment_author_link() ?></cite></p>
			<p class="comment-date"><?php p2_comment_date() ?></p>
			<p class="comment-meta">
				<a href="<?php comment_link() ?>">Permalink</a>
				<?php comment_reply_link(array_merge($args, array(
					'depth' => $depth,
					'max_depth' => $args['max_depth'],
					'before' => ' | '
				))) ?>
				<?php if ( $args['max_depth'] != $depth && current_user_can('edit_comment') ): ?> | <?php endif ?>
				<?php edit_comment_link('Edit') ?>
			</p>
			<br class="clear">
		</div>
		
		<br class="clear">
		
		<div class="comment-content" id="comment-content-<?php comment_ID() ?>">
			<?php comment_text() ?>
		</div>
<?php endif; ?>
	
		<br class="clear">

<?php
}

// --------------------------------------------------------------------------

/**
 * Handles comment content retrieval requests and comment save requests sent
 * via AJAX
 *
 * @return void
 */
function p2_edit_comment()
{
	
	check_ajax_referer('p2-edit-comment', '_wp_nonce');
	
	$comment_id = intval($_POST['comment_id']);
	
	// Make sure we can edit comments
	if ( ! current_user_can('edit_comment', $comment_id) )
	{
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('You are not allowed to edit comments.', 'p2')
		));
		exit;
	}
	
	if ( isset($_POST['comment_content']) )
	{
		
		// Grab the new comment content and save to the database
		$comment_content = trim($_POST['comment_content']);
		wp_update_comment(array(
			'comment_ID' => $comment_id,
			'comment_content' => $comment_content
		));
		
		$comment_content = apply_filters('comment_text', $comment_content);
		
		echo json_encode(array(
			'status' => 'ok',
			'comment_content' => stripcslashes($comment_content)
		));
		exit;
		
	}
	else
	{
		
		$comment = get_comment($comment_id);
		echo json_encode(array(
			'status' => 'ok',
			'comment_content' => $comment->comment_content
		));
		exit;
		
	}
	
}
add_action('wp_ajax_p2_edit_comment', 'p2_edit_comment');

// --------------------------------------------------------------------------

/**
 * Returns an array of To-Do comments under a specific post.
 *
 * @param integer $post_id Post ID
 * @return array
 */
function p2_get_todos($post_id)
{

	$comments = get_comments(array(
		'status' => 'approve',
		'post_id' => $post_id
	));
	
	$todos = array();
	foreach ( $comments as $comment )
	{
		$todo = get_comment_meta($comment->comment_ID, 'p2_todo', true);
		if ( $todo )
		{
		
			if ( $todo == 'checked' ) {
				$comment->checked = true;
			}
			else {
				$comment->checked = false;
			}
		
			$todos[] = $comment;
			
		}
	}
	
	return $todos;

}

// --------------------------------------------------------------------------

/**
 * Handles todo checking/unchecking
 *
 * @return void
 */
function p2_mark_comment()
{
	
	global $current_user;
	
	check_ajax_referer('p2-mark-comment', '_wp_nonce');
	
	$post_id = $_POST['post_id'];
	$id = $_POST['comment_id'];
	$message = trim($_POST['message']);
	$mark_as = $_POST['mark_as'];
	
	$name = $current_user->display_name;
	$email = $current_user->user_email;
	$url = $current_user->user_url;
	$ip = $_SERVER['REMOTE_ADDR'];
	$user_agent = $_SERVER['USER_AGENT'];
	
	if ( update_comment_meta($id, 'p2_todo', $mark_as) )
	{
	
		// Insert status comment
		$mark_comment_id = wp_new_comment(array(
			'comment_post_ID' => $post_id,
			'comment_parent' => $id,
			'comment_content' => $message,
			'comment_author' => $name,
			'comment_author_email' => $email,
			'comment_author_url' => $url,
			'comment_author_IP' => $ip,
			'comment_agent' => $user_agent
		));
		add_comment_meta($mark_comment_id, 'p2_todo_marked_as', $mark_as);
		
		$mark_as = ucwords($mark_as);
		
		// If we need to send notifications...
		if ( get_option('p2_notifications') )
		{
		
			$parent_comment = get_comment($id);
			$parent_excerpt = strlen($parent_comment->comment_content) > 50 ? substr($parent_comment->comment_content, 0, 50) . '...' : $parent_comment->comment_content;
			$post_permalink = get_permalink($post_id);
			$optional_msg = empty($message) ? '' : "<p>{$message}</p>";	
			
			// Create our email message
			$subject = "{$name} has {$mark_as} $parent_excerpt";
			$msg = "<p>Dear %s,</p>
					<p>{$name} has {$mark_as} {$parent_comment->comment_content}:</p>
					{$optional_msg}
					<p><a href=\"{$post_permalink}\">View Post</a></p>";
			
			// Get the subscriber list and then remove the commenter from it.
			$subscribers = get_post_meta($post_id, '_p2_subscribers', true);
			if ( isset($subscribers[$email]) )
			{
				unset($subscribers[$email]);
			}
			
			// Send the notifs.
			p2_notify($subscribers, $subject, $msg);
			
			// Subscribe the todo checker/unchecker then update post meta
			$subscribers[$email] = $name;
			update_post_meta($post_id, '_p2_subscribers', $subscribers);
		
		}
		
		// Capture a block of generated HTML for the comment
		// Capture an html chunk of the comment
		$the_comment = get_comment($mark_comment_id);
		ob_start();
		p2_display_comments($the_comment, array('avatar_size' => 36), 0);
		$html = ob_get_contents();
		ob_end_clean();
		
		echo json_encode(array(
			'status' => 'ok',
			'last_refresh' => time(),
			'id' => $mark_comment_id,
			'html' => $html
		));
		exit;
	}
	else
	{
		$action = str_replace('ed', '', $mark_as);
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('Failed to mark this item. Please try again.', 'p2')
		));
		exit;
	}
	
}
add_action('wp_ajax_p2_mark_comment', 'p2_mark_comment');

// --------------------------------------------------------------------------

/**
 * Handles new comments sent via AJAX
 *
 * @return void
 */
function p2_new_comment()
{
	
	global $current_user;
	
	// Check Nonce
	check_ajax_referer('p2-comment', '_wp_nonce');
	
	// Get the comment and make sure it isn't empty
	$comment = trim($_POST['comment']);
	if ( $comment == '' )
	{
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('Enter your comment.')
		));
		exit;
	}
	
	$post_id = intval($_POST['comment_post_id']);
	$parent = intval($_POST['comment_parent']);
	
	// Make sure we have an existing post
	$post = get_post($post_id);
	if ( ! $post )
	{
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('Invalid post ID.')
		));
		exit;
	}
	
	// Grab the author's details
	if ( is_user_logged_in() )
	{
		
		if ( $current_user->display_name )
		{
			$name = $current_user->display_name;
		}
		elseif ( $current_user->user_nicename)
		{
			$name = $current_user->user_nicename;
		}
		else
		{
			$name = $current_user->user_login;
		}
		
		$email = $current_user->user_email;
		$url = $current_user->user_url;
		
		if ( current_user_can('unfiltered_html') )
		{
			$unfiltered_html_nonce = wp_create_nonce('p2-unfiltered-html-comment');
			if ( $unfiltered_html_nonce != $_POST['_wp_nonce_unfiltered_html'] )
			{
				kses_remove_filters();
				kses_init_filters();
			}
		}
		
	}
	// Non-logged in users need more validation
	else
	{
		
		$name = trim($_POST['comment_author']);
		$email = trim($_POST['comment_author_email']);
		$url = trim($_POST['comment_author_url']);
		
		// Check name and email if the blog requires it
		if ( get_option('require_name_email') )
		{
		
			if ( $name == '' || strlen($email) < 6 )
			{
				echo json_encode(array(
					'status' => 'error',
					'msg' => __('Fill up the required fields (name and email).')
				));
				exit;
			}
		
			// Validate email
			if ( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
			{
				echo json_encode(array(
					'status' => 'error',
					'msg' => __('Invalid email address.')
				));
				exit;
			}
		
		}
		
		// Validate URL (if we have one)
		if ( ! empty($url) && ! filter_var($url, FILTER_VALIDATE_URL) )
		{
			echo json_encode(array(
				'status' => 'error',
				'msg' => __('Invalid Website URL.')
			));
			exit;
		}
		
	}
	
	$ip = $_SERVER['REMOTE_ADDR'];
	$user_agent = $_SERVER['USER_AGENT'];
	
	$approved = 1;
	if ( ! is_user_logged_in() )
	{
		$approved = (int) check_comment($name, $email, $url, $comment, $ip, $user_agent, 'comment');
	}
	
	// Automatically add paragraphs
	$comment = wpautop($comment);
	
	// Insert the comment
	$id = wp_new_comment(array(
		'comment_post_ID' => $post_id,
		'comment_parent' => $parent,
		'comment_content' => $comment,
		'comment_author' => $name,
		'comment_author_email' => $email,
		'comment_author_url' => $url,
		'comment_author_IP' => $ip,
		'comment_agent' => $user_agent,
		'comment_approved' => $approved
	));
	
	// If the comment has been added
	if ( $id )
	{
	
		// Store the credentials of not-logged-in users
		if ( ! is_user_logged_in() )
		{
			$comment_cookie_lifetime = apply_filters('comment_cookie_lifetime', 30000000);
			setcookie('comment_author_' . COOKIEHASH, $name, time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
			setcookie('comment_author_email_' . COOKIEHASH, $email, time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
			setcookie('comment_author_url_' . COOKIEHASH, esc_url($url), time() + $comment_cookie_lifetime, COOKIEPATH, COOKIE_DOMAIN);
		}
		
		// Check if a todo hashtag is present and the commenter can add To-Do's
		if ( preg_match('/#todo/', $comment) && current_user_can('p2_add_todo') )
		{
			$is_todo = true;
			add_comment_meta($id, 'p2_todo', 'unchecked');
			$comment = str_replace('#todo', '', $comment);
			wp_update_comment(array(
				'comment_ID' => $id,
				'comment_content' => $comment
			));
		}
	
		// If we need to send notifications...
		if ( get_option('p2_notifications') )
		{
			
			$post_title = $post->post_title;
			$post_permalink = get_permalink($post->ID);
			
			// ToDo's have a different subjects & message
			if ( $is_todo )
			{
				$subject = "New To-Do Item on {$post_title}";
				$msg = "<p>Dear %s,</p>
				<p>{$name} added a new To-Do item on &quot;{$post_title}&quot;:</p>
				<p>{$comment}</p>
				<p><a href=\"{$post_permalink}\">View Post</a></p>";
			}
			else
			{
				$subject = "New Comment on {$post_title}";
				$msg = "<p>Dear %s,</p>
				<p>{$name} commented on the post &quot;{$post_title}&quot;:</p>
				<p>{$comment}</p>
				<p><a href=\"{$post_permalink}\">View Post</a></p>";
			}
			
			$subscribers = get_post_meta($post->ID, '_p2_subscribers', true);
			
			// Remove the commenter's email from the list.
			if ( isset($subscribers[$email]) )
			{ 
				unset($subscribers[$email]);
			}
			
			// Send the notifications
			p2_notify($subscribers, $subject, $msg);
			
			// Add the commenter to the subscriber list then update the post meta
			$subscribers[$email] = $name;
			update_post_meta($post->ID, '_p2_subscribers', $subscribers);
		
		}
		
		// Capture an html chunk of the comment
		$the_comment = get_comment($id);
		ob_start();
		$args = array(
			'avatar_size' => 36,
			'max_depth' => get_option('thread_comments_depth')	
		);
		p2_display_comments($the_comment, $args, 1);
		$html = ob_get_contents();
		ob_end_clean();
		
		echo json_encode(array(
			'status' => 'ok',
			'last_refresh' => time(),
			'is_approved' => $approved,
			'id' => $id,
			'html' => $html
		));
		exit;
		
	}
	else
	{
		echo json_encode(array(
			'status' => 'error',
			'msg' => __('Failed to add comment.')
		));
		exit;
	}
	
}
add_action('wp_ajax_p2_new_comment', 'p2_new_comment');
add_action('wp_ajax_nopriv_p2_new_comment', 'p2_new_comment');

// --------------------------------------------------------------------------

/**
 * Custom template tag that will display the recent 5 comments in the blog.
 * 
 * @return void
 * @since 3.0d
 */
function p2_recent_comments()
{

	$comments = get_comments(array(
		'number' => 5,
		'status' => 'approve'
	));
	
	if ( count($comments) > 0 )
	{
?>
	<ul>
	
<?php foreach ( $comments as $comment ): ?>
		<li><?php echo $comment->comment_author ?> on <a href="<?php echo get_permalink($comment->comment_post_ID) ?>"><?php echo get_the_title($comment->comment_post_ID) ?></a></li>
<?php endforeach; ?>
	
	</ul>
<?php
	
	}
	else
	{
?>
	<p>No comments yet.</p>
<?php
	}

}