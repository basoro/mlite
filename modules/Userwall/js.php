<script src="https://code.jquery.com/jquery-migrate-1.2.1.js"></script>
<script type="text/javascript">
    var domain = "<?php echo URL;?>/modules/Userwall/inc/";
      // Simple infinite Scrolling

      $(function(){

          var $timeline = $('#tupdate'),
              $spinner = $('#Spinner').hide();

          function loadMore(){

            $(window).unbind('scroll.posts');

            $spinner.show();

            $.ajax({
              url: "<?php echo URL;?>/modules/Userwall/inc/loadmore.php?<?php if(isset($_GET['user']) && $_GET['user'] !== '') { echo 'user='.$_GET['user'].'&'; } ?>lastPost="+ $(".pointer:last").attr('id'),
              success: function(html){
                  if(html){
                      $timeline.append(html);
                      $spinner.hide();
                  }else{
                      $spinner.html('<p>No more posts to show.</p>');
                  }

                  $(window).bind('scroll.posts',scrollEvent);
              }
            });
          }


          //lastAddedLiveFunc();
          $(window).bind('scroll.posts',scrollEvent);

          function scrollEvent(){
            var wintop = $(window).scrollTop(), docheight = $(document).height(), winheight = $(window).height();
            var  scrolltrigger = 0.95;

            if  ((wintop/(docheight-winheight)) > scrolltrigger)  loadMore();
          }

      });

 $(function(){
	  $('#tabs div').hide();
	  $('#tabs div:first').show();
	  $('#tabs ul li:first').addClass('active');
	  $('#tabs ul li a').click(function(){
	  $('#tabs ul li').removeClass('active');
	  $(this).parent().addClass('active');
	  var currentTab = $(this).attr('href');
	  $('#tabs div').hide();
	  $(currentTab).show();
	  return false;
	  });


	  jQuery('a.acomment-reply').live("click", function(e) {
			var getpID =  jQuery(this).attr('id').replace('acomment-comment-','');
			jQuery("#acomment-comment-"+getpID).hide();
			jQuery("#fb-"+getpID).css('display','block');
			jQuery("#ac-input-"+getpID).focus();
						e.preventDefault();
								});

	jQuery('a.comment_cancel').live("click", function(e) {

			var getpID =  jQuery(this).attr('id');

			jQuery("#fb-"+getpID).css('display','');
			jQuery("#acomment-comment-"+getpID).show();
			jQuery("#ac-input-"+getpID).val('');
		e.preventDefault();
	});



});
    </script>
<script src="<?php echo URL;?>/modules/Userwall/inc/assets/javascripts/all.js" type="text/javascript"></script>
<script src="<?php echo URL;?>/modules/Userwall/inc/assets/javascripts/ajaxupload.3.5.js" type="text/javascript"></script>
<script type="text/javascript" >
	jQuery(document).ready(function() {
		var btnUpload=jQuery('#upload_pic');
		var status=jQuery('#statuss');
		new AjaxUpload(btnUpload, {
			action: '<?php echo URL;?>/modules/Userwall/inc/upload-img.php',
			name: 'uploadfile',
			onSubmit: function(file, ext){
				 if (! (ext && /^(jpg|jpeg|gif|png)$/.test(ext))){
                    // extension is not allowed
					status.text('Only JPG or GIF files are allowed');
					return false;
				}
				status.text('Uploading...');
			},
			onComplete: function(file, response){
				//On completion clear the status
				status.text('');

				//Add uploaded file to list
				if(response==="success"){
					jQuery('#pic_url').val(file);
					//jQuery('#files').empty();
					//jQuery('#files').text(file+' added').addClass('successe');
					//var ts = Math.round((new Date()).getTime() / 1000);
					jQuery('#files').html('<img src="<?php echo URL;?>/modules/Userwall/inc/uploads/'+file+'" height="100" width="100">');
				} else{
					//jQuery('#files').text(file+' upload failed').addClass('errore');
				}
			}
		});

	});
</script>
<div id="fb-root"></div>
