<?php
/**
 * P2 Reloaded Theme
 * Admin Settings Page
 *
 * @package P2Reloaded
 * @author imoz32 <imoz39@gmail.com>
 * @since 2.9
 */

$updated = false;

// Get all the roles
$roles = get_editable_roles();

if ( isset($_POST['submit']) )
{
	
	// Titles
	if ( isset($_POST['titles']) )
	{
		update_option('p2_show_titles', '1');
	}
	else
	{
		update_option('p2_show_titles', '0');
	}
	
	// Attachments
	if ( isset($_POST['attachments']) )
	{
		update_option('p2_attachments', '1');
	}
	else
	{
		update_option('p2_attachments', '0');
	}
	
	// Tags
	if ( isset($_POST['tags']) )
	{
		update_option('p2_enable_tags', '1');
	}
	else
	{
		update_option('p2_enable_tags', '0');
	}
	
	// Notifications
	if ( isset($_POST['notifications']) )
	{
		update_option('p2_notifications', '1');
	}
	else
	{
		update_option('p2_notifications', '0');
	}
	
	// Anonymous Posting
	if ( isset($_POST['anonymous_posting']) )
	{
		update_option('p2_anonymous_posting', '1');
	}
	else
	{
		update_option('p2_anonymous_posting', '0');
	}
	
	// Color Scheme
	update_option('p2_color_scheme', strtolower($_POST['color_scheme']));
	
	// Homepage
	update_option('p2_homepage', $_POST['homepage']);
	
	// Todo items
	$roles2 = array_keys($roles);
	foreach ( $roles2 as $role )
	{
		$the_role = get_role($role);
		
		if ( in_array($role, $_POST['add_todos']) )
		{
			$the_role->add_cap('p2_add_todo');
		}
		else
		{
			$the_role->remove_cap('p2_add_todo');
		}
		
		if ( in_array($role, $_POST['edit_todos']) )
		{
			$the_role->add_cap('p2_edit_todo');
		}
		else
		{
			$the_role->remove_cap('p2_edit_todo');
		}
		
	}
	
	// Refresh roles
	$roles = get_editable_roles();
	
	// Email settings
	update_option('p2_email', trim($_POST['email_from']));
	update_option('p2_email_name', trim($_POST['email_from_name']));
	
	// Private blog
	if ( isset($_POST['private']) )
	{
		update_option('p2_is_private', '1');
	}
	else
	{
		update_option('p2_is_private', '0');
	}
	
	// Private msg
	update_option('p2_private_msg', trim($_POST['private_msg']));
	
	$updated = true;
	
}

?>

<div class="wrap">
	
		<div id="icon-themes" class="icon32"></div>
		
		<h2>P2 Reloaded <?php _e('Settings', 'p2') ?></h2>
		
		<?php if ( $updated ): ?><div id="message" class="updated"><p><?php _e('Settings saved', 'p2') ?>.</p></div><?php endif; ?>
		
		<form action="<?php echo admin_url('themes.php?page=p2-reloaded-settings') ?>" method="post">
		
			<h3><?php _e('General Settings', 'p2') ?></h3>
		
			<p>
				<label><input type="checkbox" name="titles" value="1"<?php if ( get_option('p2_show_titles') ): ?> checked="checked"<?php endif; ?> />
				<?php _e('Display post titles', 'p2') ?>.</label>
			<p>
		
			<p>
				<label><input type="checkbox" name="attachments" value="1"<?php if ( get_option('p2_attachments') ): ?> checked="checked"<?php endif; ?> /> 
				<?php _e('Allow logged in users to attach files to their posts', 'p2') ?>.</label>
			</p>
			
			<p>
				<label><input type="checkbox" name="tags" value="1"<?php if ( get_option('p2_enable_tags') ): ?> checked="checked"<?php endif; ?> /> 
				<?php _e('Allow users to add tags to their posts', 'p2') ?>.</label>
			</p>
			
			<p>
				<label><input type="checkbox" name="notifications" value="1"<?php if ( get_option('p2_notifications') ): ?> checked="checked"<?php endif; ?> /> 
				<?php _e('Notify users of new comments and whenever they are tagged in a post', 'p2') ?>.</label>
			</p>
			
			<p>
				<label><input type="checkbox" name="anonymous_posting" value="1"<?php if ( get_option('p2_anonymous_posting') ): ?> checked="checked"<?php endif; ?> />
				<?php _e('Allow anonymous users to post &ndash; Creates a draft post and then notifies administrators', 'p2') ?>.
			</p>
			
			<h3><?php _e('Appearance', 'p2') ?></h3>
			
			<p>
				<label><?php _e('Color Scheme:', 'p2') ?> 
				<select name="color_scheme">
					<option value="blue"<?php if ( get_option('p2_color_scheme') == 'blue' ): ?> selected<?php endif; ?>><?php _e('Blue', 'p2') ?></option>
					<option value="gray"<?php if ( get_option('p2_color_scheme') == 'gray' ): ?> selected<?php endif; ?>><?php _e('Gray', 'p2') ?></option>
					<option value="green"<?php if ( get_option('p2_color_scheme') == 'green' ): ?> selected<?php endif; ?>><?php _e('Green', 'p2') ?></option>
					<option value="pink"<?php if ( get_option('p2_color_scheme') == 'pink' ): ?> selected<?php endif; ?>><?php _e('Pink', 'p2') ?></option>
				</select></label>
			</p>
			
			<p>
				<label><?php _e('Homepage:', 'p2') ?>
				<select name="homepage">
					<option value="normal"<?php if ( get_option('p2_homepage') == 'normal' ): ?> selected<?php endif; ?>><?php _e('Normal', 'p2') ?></option>
					<option value="fullscreentodolist"<?php if ( get_option('p2_homepage') == 'fullscreentodolist' ): ?> selected<?php endif; ?>><?php _e('Fullscreen To-Do List', 'p2') ?></option>
				</select></label>
			</p>
			
			<h3><?php _e('ToDo\'s', 'p2') ?></h3>
			
			<p><?php _e('Users allowed to add ToDo items:', 'p2') ?></p>
			<p>
				<?php foreach ( $roles as $tag => $role ): ?>
				<label><input type="checkbox" name="add_todos[]" value="<?php echo $tag ?>"<?php if ( isset($role['capabilities']['p2_add_todo']) ): ?>checked="checked"<?php endif ?> /> <?php echo $role['name'] ?></label> 
				<?php endforeach; ?> 
			</p>
			
			<p><?php _e('Users allowed to check/uncheck ToDo items:', 'p2') ?></p>
			<p>
				<?php foreach ( $roles as $tag => $role ): ?>
				<label><input type="checkbox" name="edit_todos[]" value="<?php echo $tag ?>"<?php if ( isset($role['capabilities']['p2_edit_todo']) ): ?>checked="checked"<?php endif ?> /> <?php echo $role['name'] ?></label> 
				<?php endforeach; ?> 
			</p>
			
			<h3><?php _e('Email Settings', 'p2') ?></h3>
			
			<p><label><?php _e('Email &quot;From&quot; Address:', 'p2') ?> 
				<input type="text" name="email_from" value="<?php echo get_option('p2_email') ?>" size="50" /></label></p>
				
			<p><label><?php _e('Email &quot;From&quot; Name:', 'p2') ?> 
				<input type="text" name="email_from_name" value="<?php echo get_option('p2_email_name') ?>" size="50" /></label></p>
			
			<h3><?php _e('Private Blog', 'p2') ?></h3>
			
			<p><label><input type="checkbox" id="p2-private-blog" name="private"<?php if ( get_option('p2_is_private') ): ?> checked="checked"<?php endif; ?> /> <?php _e('Make this blog private.', 'p2') ?></label></p>
			
			<p><label><?php _e('Display this message to users who are not logged in', 'p2') ?>:<br>
				<textarea name="private_msg" id="p2-private-msg" rows="10" cols="50"><?php echo stripcslashes(get_option('p2_private_msg', 'p2')) ?></textarea></label></p>
		
			<p><input type="submit" name="submit" value="<?php _e('Save Changes', 'p2') ?>" class="button-primary" /></p>
		
		</form>
		
</div>