<?php
/**
 * P2 Reloaded Theme
 * Functions File
 *
 * @package P2Reloaded
 * @author mok <imoz39@gmail.com>
 * @since 2.9
 */

/**
 * Load P2 Reloaded files
 */
require_once TEMPLATEPATH . '/includes/attachments.php';
require_once TEMPLATEPATH . '/includes/comments.php';
require_once TEMPLATEPATH . '/includes/posts.php';

/**
 * Register theme features.
 *
 */
if ( function_exists('register_nav_menu') )
{
	register_nav_menu('topmenu', __('Top Navigation Menu'));
}

if ( function_exists('register_sidebar') )
{
	register_sidebar(array(
		'id' => 'right-sidebar',
		'name' => __('Right Sidebar'),
		'description' => __('The Right Sidebar')
	));
}

// --------------------------------------------------------------------------

/**
 * Theme Activation Script
 * This block runs when P2 Reloaded gets activated.
 *
 */
if ( ! get_option('p2_reloaded') )
{
	
	add_option('p2_reloaded', '1');
	
	add_option('p2_show_titles', '1');
	add_option('p2_attachments', '1');
	add_option('p2_enable_tags', '1');
	add_option('p2_notifications', '1');
	add_option('p2_anonymous_posting', '0');
	add_option('p2_color_scheme', 'blue');
	add_option('p2_homepage', 'normal');
	add_option('p2_is_private', '0');
	add_option('p2_private_msg', '');
	add_option('p2_email', '');
	add_option('p2_email_name', '');
	
	// Add custom caps
	$allowed_roles = array('administrator', 'editor', 'author', 'contributor');
	foreach ( $allowed_roles as $role )
	{
		$the_role = get_role($role);
		$the_role->add_cap('p2_add_todo');
		$the_role->add_cap('p2_edit_todo');
	}
	
	// Store WP's native notification option and then disable it
	add_option('p2_old_comments_notify', get_option('comments_notify'));
	update_option('comments_notify', '0');

}

// --------------------------------------------------------------------------

/**
 * Theme Deactivation Function
 * Invoked when P2 Reloaded gets deactivated.
 *
 * @param string $new_theme new theme name
 * @return void
 */
function p2_deactivate($new_theme)
{
	
	delete_option('p2_reloaded', '1');
	
	// Remove our custom caps
	$roles = array_keys(get_editable_roles());
	foreach ( $roles as $role )
	{
		$the_role = get_role($role);
		$the_role->remove_cap('p2_add_todo');
		$the_role->remove_cap('p2_edit_todo');
	}
	
	// Restore WP's native notification option
	update_option('comments_notify', get_option('p2_old_comments_notify'));
	delete_option('p2_old_comments_notify');
	
}
add_action('switch_theme', 'p2_deactivate');

// --------------------------------------------------------------------------

/**
 * Add in WP's auto paragraph filter to comments.
 *
 */
add_filter('comment_text', 'wpautop');

// --------------------------------------------------------------------------

/**
 * Registers P2 Admin menus
 *
 * @return void
 */
function p2_add_admin_menus()
{
	
	add_theme_page('P2 Reloaded Settings', __('Settings'), 'edit_themes', 'p2-reloaded-settings', 'p2_admin_settings_page');
	
}
add_action('admin_menu', 'p2_add_admin_menus');

// --------------------------------------------------------------------------

/**
 * Loads the p2 settings page
 *
 * @return void
 */
function p2_admin_settings_page()
{
	
	require_once TEMPLATEPATH . '/includes/admin-settings.php';
	
}

// --------------------------------------------------------------------------

/**
 * Replaces smart quotes with their standard equivalents.
 *
 * @param string $str input string
 * @return string
 */
function p2_convert_smart_quotes($str)
{
	
	// utf-8 characters
	$str = str_replace(
	array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
 	array("'", "'", '"', '"', '-', '--', '...'),
 	$str);
 	
	// Next, replace their Windows-1252 equivalents.
	 $str = str_replace(
	 array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
	 array("'", "'", '"', '"', '-', '--', '...'),
	 $str);
	
	return $str;
	
}

// --------------------------------------------------------------------------

/**
 * Automatically adds <a> links to post and comment content. Does not auto-link
 * content on admin pages.
 *
 * @param string $content the content
 * @return string
 */
function p2_linkify($content)
{
	
	$pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
	$callback = create_function('$matches', '
		$url       = array_shift($matches);
		$url_parts = parse_url($url);

		$text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

		return sprintf(\'<a rel="nofollow" href="%s">%s</a>\', $url, $text);
	');

	$content = preg_replace_callback($pattern, $callback, $content);
	
	return $content;
	
}

// --------------------------------------------------------------------------

/**
 * Sends out notification emails to the email addresses in $emails. Prevents
 * multiple notifications internally so you can have multiple emails in $emails
 * and p2_notify() will just check if a user is already been notified/emailed.
 *
 * @param array $users an array of users that will be emailed. emails are keys while the values are the names.
 * @param string $subject the email subject
 * @param string $message the email message
 * @return void
 */
function p2_notify($users, $subject, $message)
{
	
	$headers = array(
		sprintf('From: %s <%s>', get_option('p2_email_name'), get_option('p2_email')),
		'Content-type: text/html'
	);
	$headers = implode("\r\n", $headers);
	
	$message = stripcslashes($message);
	
	$notified = array();
	
	if ( is_array($users) && count($users) > 0 )
	{
		foreach ( $users as $email => $user )
		{
			
			$msg = '';
			if ( ! in_array($email, $notified) )
			{
				
				$msg = sprintf($message, $user);
				wp_mail($email, $subject, $msg, $headers);
				 
				$notified[] = $email;
				 
			}
			
		}
	}
	
}

// --------------------------------------------------------------------------

/**
 * Callback function for the top menu. Just creates a menu of all the
 * pages.
 *
 * @since 2.9
 */
function p2_pages_menu()
{
	
	$pages = get_pages(array(
		'hierarchical' => false,
		'number' => 5
	));
	
?>

<?php if ( count($pages) > 0 ): ?>
<nav id="pages-nav">
	<ul>
<?php foreach ( $pages as $page ): ?>
		<li><a href="<?php echo get_page_link($page->ID) ?>"><?php echo $page->post_title ?></a></li>
<?php endforeach; ?>
	</ul>
</nav>
<?php endif; ?>

<?php
}

// --------------------------------------------------------------------------

/**
 * Runs on WP init and checks if the private blog option is turned on. If it
 * is then we show a message for non-logged-in users.
 *
 * @return void
 */
function p2_private_blog_check()
{
	
	global $blog_id;
	
	if ( get_option('p2_is_private') )
	{
		$uri = $_SERVER['REQUEST_URI'];
		if (  ! current_user_can_for_blog($blog_id, 'read') && ! is_user_logged_in() && strpos($uri, 'wp-login.php') === FALSE && strpos($uri, 'wp-register.php') === FALSE )
		{
			require_once TEMPLATEPATH . '/includes/private-blog.php';
			exit;
		}
	}
	
}
add_action('init', 'p2_private_blog_check');

// --------------------------------------------------------------------------

/**
 * Returns the correct avatar depending on $user_id or $user_email.
 *
 * @param integer $user_id user ID
 * @param string $user_email user email
 * @param integer $size avatar size
 * @return void
 */
function p2_the_avatar($user_id, $user_email, $size)
{
	
	if ( $user_id )
	{
		get_avatar($user_id, $size);
	}
	else
	{
		get_avatar($user_email, $size);
	}
	
}

// --------------------------------------------------------------------------

/**
 * Runs on theme setup. Just registers the languages supported by P2.
 *
 * @return void
 * @since 3.0a
 */
function p2_theme_setup()
{

	load_theme_textdomain('p2', get_template_directory() . '/languages');

}
add_action('after_setup_theme', 'p2_theme_setup');