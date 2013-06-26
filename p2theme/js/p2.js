var UPDATE_MINS = 5;
var active_xhr = [];
var currentElement = null;
var last_cancel = null;

// --------------------------------------------------------------------------

function handle_error(r) {
	if (r.status == 'error') {
		alert(r.msg);
	}
	else {
		alert('There was an error while we were trying to process your request. Please try again.');
	}
};

// --------------------------------------------------------------------------

function flash(el) {	
	$(el).css('background-color', '#fff99e')
		 .animate({
		 	 backgroundColor: '#fff'
		 }, 'slow');
};

// --------------------------------------------------------------------------

function focus_it(to_element) {
	
	var pos = to_element.offset();
		
	currentElement = to_element;
	
	var top = pos.top;
	var left = pos.left;
	
	if ($('#wpadminbar').size() > 0) {
		top -= $('#wpadminbar').height();
	}
	
	$.scrollTo(top, left);
	
};

// --------------------------------------------------------------------------

var edit_form = $('<form action="" method="post"> \
					<p><textarea style="width: 98%; height: 100px; margin-top: 10px;"></textarea></p> \
					<p><input type="submit" value="Save Changes"> or \
					<a href="#" class="edit-cancel">Cancel</a> \
					<small class="indicator"><img src="' + P2.theme_url + '/images/edit-indicator.gif"> \
					Saving...</small></p>\
					</form>');

// --------------------------------------------------------------------------

var posts_to = null;
function p2_update_posts() {

	var data = {
		action: 'p2_get_posts',
		last_refresh: P2.last_refresh,
		_wp_nonce: P2.posts_nonce	
	};
	
	$.post(P2.ajax_url, data, function(r) {
		
		if (r.status == 'ok') {
			if (r.html != '') {
				$('article:first').before(r.html);
			}
			P2.last_refresh = r.last_refresh;
		}
		
		if (r.ids.length > 0) {
			$.each(r.ids, function(i, id) {
				flash($('#post-'+ id))
			});
		}
		
		posts_to = setTimeout(p2_update_posts, 3600 * UPDATE_MINS);
		
	}, 'json');
	
};

// --------------------------------------------------------------------------

var comments_to = null;
function p2_update_comments() {

	var data = {
		action: 'p2_get_comments',
		last_refresh: P2.last_refresh,
		_wp_nonce: P2.comments_nonce	
	};
	
	$.post(P2.ajax_url, data, function(r) {
		
		if (r.status == 'ok') {
			
			var index = 0;
			var iterations = 0;
			
			while (r.comments.length > 0) {
				
				var comment = r.comments[index];
				
				if ( comment != undefined ) {
					
					if (comment.parent == 0) {
						$('#post-'+ comment.post_id).children('.post-comments').children('ul').append(comment.html);
						r.comments.splice(index, 1);
					}
					else {
						var parent = $('#comment-'+ comment.parent);
						if (parent.size() > 0) {
							if (parent.children('ul.children').size() == 0) {
								parent.append('<ul class="children"></ul>');
							}
							parent.children('ul.children').append(comment.html);
							r.comments.splice(index, 1);
							
							flash($('#comment-'+ comment.id));
						}
					}
				}
				
				if (r.comments[index + 1] != undefined)
					index++;
				else
					index = 0;
					
				iterations++;
				if (iterations > 10) {
					break;
				}
				
			}
			
			P2.last_refresh = r.last_refresh;
			
		}
		
		comments_to = setTimeout(p2_update_comments, 3600 * UPDATE_MINS);
		
	}, 'json');
	
};

// --------------------------------------------------------------------------

function pause_updaters() {
	
	clearTimeout(posts_to);
	clearTimeout(comments_to);
	
}

// --------------------------------------------------------------------------

function resume_updaters() {
	
	posts_to = setTimeout(p2_update_posts, 3600 * UPDATE_MINS);
	comments_to = setTimeout(p2_update_comments, 3600 * UPDATE_MINS);
	
}

// --------------------------------------------------------------------------

$(function() {
	
	posts_to = setTimeout(p2_update_posts, 3600 * UPDATE_MINS);
	comments_to = setTimeout(p2_update_comments, 3600 * UPDATE_MINS);
	
	// --------------------------------------------------------------------------
	
	$('#p2-hide-threads').click(function(evt) {
		
		evt.preventDefault();
		
		var a = $(this);
		
		if (a.text() == 'Hide threads') {
			$('.post-comments').each(function() {
				if ($(this).children('ul').children().size() > 0) {
					$(this).hide();
				}
			});
			a.text('Show threads');
		}
		else {
			$('.post-comments').show();
			a.text('Hide threads');
		}
		
	});
	
	// --------------------------------------------------------------------------
	
	var dw = $(window).width();
	var dh = $(window).height();
	var popup = $('#p2-the-keyboard-shortcuts');
	popup.css({
		left: ((dw - popup.width()) / 2) - 25,
		top: ((dh - popup.height()) / 2) - 25
	});
	$('#p2-keyboard-shortcuts').click(function(evt) {
		
		evt.preventDefault();
		popup.toggle();
		
	});
	
	// --------------------------------------------------------------------------
	
	$('.comment-reply-link').click(function() {
		$('.post-comments').show();
	});
	
	// --------------------------------------------------------------------------
	
	$('.comment-edit-link').click(function(evt) {
	
		evt.preventDefault();
		
		var li = $(evt.target).parent().parent().parent();
		var comment_id = li.attr('id').substr(8);
		
		// If we already have a form, ignore edit request
		if (li.children('.comment-content').next().is('form')) {
			return;
		}
		
		pause_updaters();
		
		// Insert the editing form
		var form = edit_form.clone();
		form.find('textarea, input').prop('disabled', true);
		li.children('.comment-content').after(form)
									   .hide()
									   .next().find('textarea')
										 	  .val('Loading...');
		last_cancel = form.find('.edit-cancel');
		
		// Catch form submit
		form.submit(function(evt) {
				
			evt.preventDefault();
			
			var comment_content = $.trim(form.find('textarea').val());
			
			form.find('.indicator').fadeIn();
			form.find('textarea').prop('disabled', true);
			form.find('input').prop('disabled', true);
			
			var data = {
				action: 'p2_edit_comment',
				comment_id: comment_id,
				comment_content: comment_content,
				_wp_nonce: P2.edit_comment_nonce	
			};
			$.post(P2.ajax_url, data, function(r) {
				if (r.status == 'ok') {
					li.children('.comment-content').show()
												   .html(r.comment_content)
												   
					form.remove();
				}
				else {
					handle_error(r);
				}
			}, 'json');
			
			resume_updaters();
				
		});
		
		// Handle edit cancels
		form.find('.edit-cancel').click(function(evt) {
			evt.preventDefault();
			form.remove();
			li.children('.comment-content').show();
			resume_updaters();
		});
		
		var data = {
			action: 'p2_edit_comment',
			comment_id: comment_id,
			_wp_nonce: P2.edit_comment_nonce
		};
		$.post(P2.ajax_url, data, function(r) {
			if (r.status == 'ok') {
				
				form.find('textarea, input').prop('disabled', false);
				form.find('textarea').val(r.comment_content)
									 .focus();
				
			}
			else {
				handle_error(r);
			}
			
		}, 'json');
	
	});
	
	// --------------------------------------------------------------------------
	
	$('.p2-edit-post').click(function(evt) {
	
		evt.preventDefault();
		
		var article = $(evt.target).parent().parent().parent();
		var post_id = article.attr('id').substr(5);
		
		// If we already have a form, ignore edit request
		if (article.children('.post-content').next().is('form')) {
			return;
		}
		
		pause_updaters();
		
		// Insert the editing form
		var form = edit_form.clone();
		form.find('textarea, input').prop('disabled', true);
		article.children('.post-content').after(form)
										 .hide()
										 .next().find('textarea')
										 		.val('Loading...');
		last_cancel = form.find('.edit-cancel');
		
		
		// Catch form submit
		form.submit(function(evt) {
				
			evt.preventDefault();
			
			var post_content = $.trim(form.find('textarea').val());
			
			form.find('.indicator').fadeIn();
			form.find('textarea').prop('disabled', true);
			form.find('input').prop('disabled', true);
			
			var data = {
				action: 'p2_edit_post',
				post_id: post_id,
				post_content: post_content,
				_wp_nonce: P2.edit_post_nonce	
			};
			$.post(P2.ajax_url, data, function(r) {
				if (r.status == 'ok') {
					article.children('.post-content').html(r.post_content)
													 .show();
					form.remove();
					resume_updaters();
				}
				else {
					handle_error(r);
				}
			}, 'json');
				
		});
		
		// Handle edit cancels
		form.find('.edit-cancel').click(function(evt) {
			evt.preventDefault();
			form.remove();
			article.children('.post-content').show();
			resume_updaters();
		});
		
		// Load (unfiltered) post content
		var data = {
			action: 'p2_edit_post',
			post_id: post_id,
			_wp_nonce: P2.edit_post_nonce	
		};
		$.post(P2.ajax_url, data, function(r) {
			if (r.status == 'ok') {
				form.find('textarea, input').prop('disabled', false);
				form.find('textarea').val(r.post_content)
									 .focus();
				resume_updaters();
			}
			else {
				handle_error(r);
			}
		}, 'json');
	
	});
	
	// --------------------------------------------------------------------------
	
	$('#submit').after('<small class="indicator"> \
							<img src="'+ P2.theme_url +'/images/comment-indicator.gif" width="12" height="12"> \
							Adding your comment...</small>');
							
	// --------------------------------------------------------------------------
	
	$('#post-form').submit(function(evt) {
		
		evt.preventDefault();
		
		var msg = $.trim($('#post-text').val());
		var tags = $.trim($('#post-tags').val());
		
		if (msg == '') {
			alert('Please enter your message.');
			$('#post-text').focus();
			return;
		}
		
		$('#post-text').prop('disabled', true);
		$('#post-tags').prop('disabled', true);
		
		$('#posting-indicator').show();
		
		var data = {
			action: 'p2_new_post',
			message: msg,
			tags: tags,
			_wp_nonce: P2.post_nonce
		}
		
		if (P2.attachments != undefined) {
			data.attachments = P2.attachments.join(',');
			$('#p2-attachments-input').prop('disabled', true);
		};
		
		pause_updaters();
		
		$.post(P2.ajax_url, data, function(r) {
			
			if (r.status == 'ok') {
				
				if (r.html != undefined) {
					
					var first = $('article:first');
					var article = $(r.html)
					
					article.insertBefore(first);
					flash($('#post-'+ r.id));
					
					$('#p2-attachments').html('');
					$('#p2-attachments-input').prop('disabled', false);
					P2.attachments = [];
				
				}
				else {
					alert('Thank you for posting. Your post will be shown once an administrator approves it.');
				}
				
				$('#post-text').prop('disabled', false)
							   .val('');
				$('#post-tags').prop('disabled', false)
							   .val('');
				
				P2.last_refresh = r.last_refresh;
				
			}
			else {
				handle_error(r);
			}
			
			$('#posting-indicator').hide();
			resume_updaters();
			
		}, 'json');
			
	});
	
	// --------------------------------------------------------------------------
	
	$('#post-it').click(function() {
		$('#post-form').submit();
	});
	
	// --------------------------------------------------------------------------
	
	$('#commentform').submit(function(evt) {
	
		evt.preventDefault();
		
		var form = $(this);
		var comment = $.trim($('#comment').val());
		
		if (comment == '') {
			alert('Please enter your comment.');
			$('#comment').focus();
			return;
		}
		
		var data = {
			action: 'p2_new_comment',
			comment: comment,
			comment_post_id: $('#comment_post_ID').val(),
			comment_parent: $('#comment_parent').val(),
			_wp_nonce: P2.comment_nonce,
		};
		
		// If we have an unfiltered html nonce then we add it
		if ( typeof P2.comment_unfiltered_html_nonce != 'undefined' ) {
			data._wp_nonce_unfiltered_html = P2.comment_unfiltered_html_nonce;
		}
		
		// If we have an author name field then we need to perform additional
		// validation for non-logged in users
		if ($('#author').size() > 0)
		{
			
			var name = $.trim($('#author').val());
			var email = $.trim($('#email').val());
			var url = $.trim($('#url').val());
			
			if (name == '') {
				alert('Enter your name.');
				$('#name').focus();
				return;
			}
			
			if (email == '' && email.length < 6) {
				alert('Invalid email address.');
				$('#email').focus();
				return;
			}
			
			data.comment_author = name;
			data.comment_author_email = email;
			data.comment_author_url = url;
			
		}
		
		$('#comment').prop('disabled', true);
		
		var submit = $(this).find('#submit');
		submit.prop('disabled', true)
			  .next()
			   	.fadeIn();
		
		pause_updaters();
		
		$.post(P2.ajax_url, data, function(r) {
			if (r.status == 'ok') {
				
				if (r.is_approved) {
					var ul = form.parent().prev();
					if (ul.is('li')) {
						if (ul.children('ul.children').size() == 0) {
							ul.append('<ul class="children"></ul>');
						}
						ul = ul.children('ul.children');
					}
					
					if (ul) {
						ul.append(r.html);
					}
					
					flash($('#comment-'+ r.id));
				}
				else {
					alert('Your comment has been added but is awaiting admin approval. Thank you for commenting.');
				}
				
				$('#comment').val('')
							 .prop('disabled', false);
				submit.prop('disabled', false)
					  .next().fadeOut();
				$('#cancel-comment-reply-link').click();
				
				P2.last_refresh = r.last_refresh;
				
			}
			else {
				handle_error(r);
				$('#comment').val('')
							 .prop('disabled', false);
				submit.prop('disabled', false)
					  .next().fadeOut();
			}
			
			resume_updaters();
			
		}, 'json');
	
	});
	
	// --------------------------------------------------------------------------
	
	$('.comment-todo').change(function() {
		
		var checkbox = $(this);
		var msg = prompt('Enter optional message:');
		
		var mark_as;
		if (checkbox.prop('checked')) {
			checkbox.parents('li').removeClass('todo-comment-unchecked')
								  .addClass('todo-comment-checked');
			mark_as = 'checked';
		}
		else {
			checkbox.parents('li').removeClass('todo-comment-checked')
								  .addClass('todo-comment-unchecked');
			mark_as = 'unchecked';
		}
		
		if (msg != null) {
			
			pause_updaters();
			checkbox.parent().after('<span class="indicator" style="display: inline" id="mark-indicator"> \
				<img src="'+ P2.theme_url +'/images/indicator-whitebg.gif" width="12" height="12"> \
				Saving...</span>');
			
			var data = {
				action: 'p2_mark_comment',
				comment_id: checkbox.val(),
				post_id: checkbox.parents('article').attr('id').substr(5),
				mark_as: checkbox.prop('checked') ? 'checked' : 'unchecked',
				message: msg,
				_wp_nonce: P2.mark_comment_nonce
			};
			
			$.post(P2.ajax_url, data, function(r) {
				
				if (r.status == 'ok') {
					
					var div = checkbox.parent().parent().parent();
					
					if (div.siblings('ul.children').size() > 0) {
						div.siblings('ul.children').append(r.html);
					}
					else {
						div.parent().append('<ul class="children">'+ r.html +'</ul>');
					}
					
					flash($('#comment-'+ r.id));
					
					$('#mark-indicator').remove();
					P2.last_refresh = r.last_refresh;
					
				}
				else {
					handle_error(r);
				}
				
				resume_updaters();
				
			}, 'json');
			
		}
		else {
			$(this).prop('checked', !$(this).prop('checked'));
		}
	});
	
	// --------------------------------------------------------------------------
	
	$(document).keyup(function(evt) {
	
		var key = evt.keyCode;
		
		if ($(evt.target).is('textarea') || $('evt.target').is('input[type=text]')) {
			return;
		}
		
		switch (key) {
			case 67:
				$('#post-text').focus();
				break;
			case 74:
				if (currentElement) {
					if (currentElement.is('article')) {
						if ($('.comments', currentElement).children().size() > 0) {
							focus_it($('.comments > li:first', currentElement));
						}
						else {
							focus_it(currentElement.next());
						}	
					}
					else {
						if ($('ul.children', currentElement).size() > 0) {
							focus_it($('ul.children > li:first', currentElement));
						}
						else if (currentElement.next().size() > 0) {
							focus_it(currentElement.next());
						}
						else if (currentElement.parents('.comment').next().size() > 0) {
							focus_it(currentElement.parents('.comment').next());
						}
						else {
							focus_it(currentElement.parents('article').next());
						}
					}
				}
				else {
					focus_it($('#posts > article:first'));
				}
				break;
			case 75:
				if (currentElement) {
					if (currentElement.is('.comment')) {
						if (currentElement.prev().size() > 0) {
							var prev_comment = currentElement.prev();
							if ($('ul.children', prev_comment).size()) {
								focus_it($('.comment:last', prev_comment));
							}
							else {
								focus_it(prev_comment);
							}
						}
						else {
							if (currentElement.parents('.comment').size() > 0) {
								focus_it(currentElement.parents('.comment'));
							}
							else {
								focus_it(currentElement.parents('article'));
							}
						}
					}
					else {
						if (currentElement.prev().size() > 0) {
							var prev_post = currentElement.prev();
							if ($('.comments', prev_post).children().size() > 0) {
								focus_it($('.comment:last', prev_post))
							}
							else {
								focus_it(prev_post);
							}
						}
					}
				}
				else {
					var last_post = $('#posts > article:last');
					if ($('.comments', last_post).children().size() > 0) {
						focus_it($('.comment:last', last_post))
					}
					else {
						focus_it(last_post);
					}
				}
				break;
			case 27:
				if (last_cancel) {
					last_cancel.click();
					last_cancel = null;
				}
				else {
					$('#p2-the-keyboard-shortcuts').hide();
				}
				break;
			case 79:
				$('#p2-hide-threads').click();
				break;
			case 82:
				if (currentElement) {
					if ($('.comment-reply-link:first', currentElement).size() > 0) {
						$('.comment-reply-link:first', currentElement).click();
					}
				}
				break;
			case 69:
				if (currentElement) {
					if (currentElement.is('article')) {
						$('.p2-edit-post:first', currentElement).click();
					}
					else {
						$('.comment-edit-link:first', currentElement).click();
					}
				}
				break;
			case 84:
				$.scrollTo(0, 0);
				break;
		}
	
	});

});