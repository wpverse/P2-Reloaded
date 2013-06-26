$(function() {

	P2.attachments = [];

	var form = $('#p2-attachments-input').parents('form');
	
	var iframe = $('<iframe name="p2iframe" id="p2iframe" src="" style="display: none"></iframe>');
	iframe.load(function(evt) {
		
		var json = iframe.contents().find('body').text();
		
		if (json != '') {
			
			var r = $.parseJSON(json);
			
			if (r.misses.length > 0) {
				var str = 'Some files failed to upload:';
				for (var i = 0; i < r.misses.length; i++) {
					str += '\n- '+ r.misses[i];
				}
				str += '\nYou may try uploading them again.';
			}
			
			$.each(r.hits, function(i, v) {
				P2.attachments.push(i);
				$('#p2-attachments').append('<li id="p2-attachment-'+ i +'">'+ v +' (<a href="#" class="p2-attachment-remove">Remove</a>)</li>');
			});
			
			$('#p2-attachments-input').show();
			$('#p2-attachments-indicator').hide();
			form[0].reset();
		
		}
		
	});

	$('#p2-attachments-input').change(function() {
		form.submit();
		$(this).hide();
		$('#p2-attachments-indicator').show();
	});
	
	form.after(iframe)
		.attr('target', 'p2iframe');
		
	$('.p2-attachment-remove').live('click', function(evt) {
		
		evt.preventDefault();
		
		var li = $(this).parent().fadeOut();
		var id = li.attr('id').substr(14);
		var index = $.inArray(id, P2.attachments);
		if (index != -1) {
			P2.attachments.splice(index, 1);
		}
		
		var data = {
			action: 'p2_remove_attachment',
			attachment_id: id,
			_wp_nonce: P2.remove_attachment_nonce
		};
		$.post(P2.ajax_url, data, function(r) {
			if ( r.status != 'ok') {
				handle_error(r);
				li.show();
				P2.attachments.push(id);
			}
		}, 'json');
		
	});

});