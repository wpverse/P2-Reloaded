function rearrange() {
	
	// Arrange the lists to form a long horizontal list of todos.
	var total_width = 0;
	$('#lists > ul > li').each(function() {
		total_width += $(this).width();
	});
	total_width += $('#lists > ul > li:last-child').width();
	$('#lists').width(total_width);
	
}

// --------------------------------------------------------------------------

function handle_error(r) {
	if (r.status == 'error') {
		alert(r.msg);
	}
	else {
		alert('There was an error while we were trying to process your request. Please try again.');
	}
}

// --------------------------------------------------------------------------

$(function() {
	
	rearrange();
	
	// --------------------------------------------------------------------------
	
	// Add Modals
	$('.facebox').facebox();
	
	// --------------------------------------------------------------------------
	
	$('.new-list-form').live('submit', function(evt) {
		
		evt.preventDefault();
		
		var title = $.trim($('.new-list-title', this).val());
		var desc = $.trim($('.new-list-desc', this).val());
		
		if (title == '') {
			alert('The To-Do List title is required.');
			$('.new-list-title', this).focus();
			return;
		}
		
		$('input, textarea', this).prop('disabled', true);
		
		var data = {
			action: 'p2_new_post',
			title: title,
			message: desc,
			_wp_nonce: P2.post_nonce
		}
		
		$.post(P2.ajax_url, data, function(r) {
			
			if (r.status == 'ok') {
				
				if (r.html != undefined) {
					
					var li = $('<li><a href="#add-todo-modal" class="facebox">' + title +' +</a><ul><li>No items yet.</li></ul></li>');
					$('.facebox', li).facebox();
					$('#lists > ul').prepend(li);
					
					rearrange();
				
				}
				else {
					alert('Thank you for posting. Your post will be shown once an administrator approves it.');
				}
				
			}
			else {
				handle_error(r);
			}
			
			$(document).trigger('close.facebox');
			$('input, textarea', this).prop('disabled', false);
			
		}, 'json');
		
	});
	
	// --------------------------------------------------------------------------
	
	$('.add-todo-form').live('submit', function(evt) {
		
		evt.preventDefault();
		
		var todo = $('.add-todo-msg', this).val();
		if (todo == '') {
			alert('Enter a To-Do Message.');
			$('.add-todo-msg', this).focus();
			return;
		}
		
		var post_id = $('#facebox > div > .content').attr('class').match(/post(\d+)/);
		post_id = post_id[1];
		
		$('input', this).prop('disabled', true);
		
		var data = {
			action: 'p2_new_comment',
			comment: todo + ' #todo',
			comment_post_id: post_id,
			comment_parent: null,
			_wp_nonce: P2.comment_nonce,
		};
		$.post(P2.ajax_url, data, function(r) {
		
			if (r.status == 'ok') {
				$('#list-'+ post_id +' > ul').prepend('<li><label><input type="checkbox" value="'+ r.id +'" class="todo"> ' + todo +'</label></li>');
			}
			else {
				handle_error(r);
			}
			
			$(document).trigger('close.facebox');
			$('input', this).prop('disabled', true);
			
		}, 'json');
		
	});
	
	// --------------------------------------------------------------------------
	
	$('.todo').live('change', function(evt) {
		
		var submit = $('#mark-todo-modal').find('input[type=submit]');
		
		var mark_as;
		if ($(this).prop('checked')) {
			mark_as = 'checked';
			submit.val('Check To-Do');
			$(this).prop('checked', false);
		}
		else {
			mark_as = 'unchecked';
			submit.val('Uncheck To-Do');
			$(this).prop('checked', true);
		}
		
		var id = $(this).val();
		$('#mark-todo-modal').find('.mark-todo-id').val(id);
		
		var post_id = $(this).parents('.post').attr('id').substr(5);
		$('#mark-todo-modal').find('.mark-todo-postid').val(post_id);
		
		$.facebox({ div: '#mark-todo-modal' }, mark_as + ' comment-'+ id);
		
	});
	
	// --------------------------------------------------------------------------
	
	$('.mark-todo-form').live('submit', function(evt) {
		
		evt.preventDefault();
		
		var mark_as = 'checked';
		if ( $('#facebox > div > .content').hasClass('unchecked') ) {
			mark_as = 'unchecked';
		}
		
		var id = $('.mark-todo-id', this).val();
		var post_id = $('.mark-todo-postid', this).val();
		var msg = $('.mark-todo-msg', this).val();
		
		$('input', this).prop('disabled', true);
		
		var data = {
			action: 'p2_mark_comment',
			comment_id: id,
			post_id: post_id,
			mark_as: mark_as,
			message: msg,
			_wp_nonce: P2.mark_comment_nonce
		};
		$.post(P2.ajax_url, data, function(r) {
		
			if (r.status == 'ok') {
				$('#todo-'+ id).prop('checked', mark_as == 'checked');
			}
			else {
				handle_error(r);
			}
			
			$(document).trigger('close.facebox');
			$('input', this).prop('disabled', false);
			
		}, 'json')
		
	});
	
});