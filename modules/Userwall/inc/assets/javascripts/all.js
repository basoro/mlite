jQuery(document).ready(function(){
				   
		jQuery('.fb_submit').click(function(){
			var form_name  = jQuery(this).attr('title');
			var obj = jQuery(this);
			jQuery(".ajax_indi").show();
			switch (form_name)
			{
		    case "picpost":
					var message = jQuery("#pmessage").val();
					var wall_image = jQuery("#pic_url").val();
					if(wall_image == '') {
					  alert("Upload Image!");
					  jQuery(".ajax_indi").hide();
					  return false;
					}
			break;
			case "vidpost":
					var message = jQuery("#vmessage").val();
					var vurl = jQuery("#y_link").val();
					if(vurl == '') {
					  alert("Youtube link cannot be empty!");
					  jQuery(".ajax_indi").hide();
					  return false;
					} else {
						var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
						var match = vurl.match(regExp);
						if (match&&match[7].length==11){
						}else{
						   alert("Enter Valid Youtube Url");
						   return false;
						}
					}
			break;
			case "npost":
					var message = jQuery("#message").val();
			break;
			default:
			alert("something went wrong!");
			}
			
			if((jQuery.trim(message) == ''))
			{
				 alert("Description Cannot be Empty");
				 jQuery(".ajax_indi").hide();
				 return false;
			} else {
				jQuery(this).attr("disabled", "disabled");
				jQuery(this).prop('value', 'Loading...');
				jQuery(this).css('cursor', 'default');
			}
			
			var str = jQuery("#"+form_name).serialize();
			
			jQuery.ajax({
				type: "POST",
				url: domain+"post_updates.php",
				data: str,
				cache: false,
				success: function(html){
					jQuery('#tupdate').prepend(html);
					obj.attr("disabled", false);
					obj.prop('value', 'Post');
					obj.css('cursor', 'pointer');
					jQuery(".ajax_indi").hide();
					jQuery("#files").empty();
					document.getElementById(form_name).reset();
				}
			});
	});
		
		
	//delete individual post comment
	jQuery('a.post-delete').live("click", function() {
		var pp = jQuery(this);
		var post_id =  jQuery(this).attr('id').replace('post_delete_','');
		var c = confirm('Are you sure to delete this post?');
		if(c) {			
			var str="featured=1&pid="+post_id;
			jQuery.ajax({
				type: "POST",
				url: domain+"post_delete.php",
				data: str,
				cache: false,
				success: function(html){
						jQuery('#post-'+post_id).fadeOut(800,function(){
								jQuery('#post-'+post_id).remove();
							});
						
						alert('Post Deleted Successfully');
				}
			});
		}
		return false;
	});
	
	
	//live comment submit
	jQuery('.live_comment_submit').live("click", function() {
			var obj = jQuery(this);									  
			var form_name  = obj.attr('title');	
			var act_id =  obj.attr('id').replace('comment_id_','');
			var comment = jQuery("#ac-input-"+act_id).val();
			var ccount = parseInt(jQuery('#comment_count_'+act_id).html(), 10);
			if(jQuery.trim(comment) == '') {
				 alert('Comment cannot be empty');
				 jQuery("#ac-input-"+act_id).focus();
				 return false;
			} else {
				jQuery(this).attr("disabled", "disabled");
				jQuery(this).prop('value', 'Loading...');
				jQuery(this).css('cursor', 'default');
			}
			var str = jQuery("#"+form_name).serialize();
		   jQuery.ajax({
				type: "POST",
				url: domain+"comment_update.php",
				data: str,
				cache: false,
				success: function(html){
					jQuery('ul#CommentPosted'+act_id).append(jQuery(html).fadeIn('slow'));
					//increment comment count
					jQuery('#comment_count_'+act_id).html(parseInt(jQuery('#comment_count_'+act_id).html(), 10)+1);
						if(ccount == 0) {
							jQuery('#show-all-'+act_id).show();
						}
					jQuery("#ac-input-"+act_id).val('');
					obj.attr("disabled", false);
					obj.prop('value', 'Submit');
					obj.css('cursor', 'pointer');
				}
			});
		
		return false;					
	});	
	
	//delete individual comment
	jQuery('a.comment-delete').live("click", function() {
		var pp = jQuery(this);
		var comment_id =  pp.attr('id').replace('comment_delete_','');
		//comment count value
		var span_count_id = pp.attr('rel');
		var ccount = parseInt(jQuery('#comment_count_'+span_count_id).html(), 10);
		var c = confirm('Are you sure to delete this comment?');
		if(c) {			
			var str="featured=1&cid="+comment_id;
			jQuery.ajax({
				type: "POST",
				url: domain+"comment_delete.php",
				data: str,
				cache: false,
				success: function(html){
						jQuery('#li-comment-'+comment_id).fadeOut(800,function(){
								jQuery('#li-comment-'+comment_id).remove();
							});
						//decrement comment count
						jQuery('#comment_count_'+span_count_id).html(parseInt(jQuery('#comment_count_'+span_count_id).html(), 10)-1);
						if(ccount == 1) {
							jQuery('#show-all-'+span_count_id).hide();
						}
						//alert('Comment Deleted Successfully');
				}
			});
		}
		return false;
	});
	
	
	
	
});