<script>
$(document).ready(function(){
	var total;
	var width,html = "",label = "";
function loading(){
	$(".poll").html("<div style='padding-left:45%'><img src='modules/Survei/477.gif'></div>");
}
	// load first question
	get_poll();

	$(".button").on("click",function(){
		var ans = $("input[type=radio]:checked").val();

		if(ans){
			loading();
$(".poll-content > ul").empty();
			$.ajax({
				type: "POST",
				url: "<?php echo URL; ?>/modules/Survei/poll/ajax.php",
				data : "act=suba&ans="+ans,
				dataType: "json",
				success: function(response){

					total = response.total;
					if(response.success == 1){
						html="";
						$.each(response.opt, function(aid,label) {
							if(response.details[aid] == undefined){
								width  = 0
								acount = 0
							}else{
								acount = response.details[aid];
								width = Math.round((parseInt(acount)/parseInt(total)) * 100);
							}
							if(width < 50 ){ var alert = "danger"} else if((width >=50) && (width <= 75)){ var alert = "primary"; }else{alert="success"}
	html+='<li class="list-group-item"><label>'+label+' ('+acount+' votes)</label></label><div class="progress"><div class="progress-bar  progress-bar-'+alert+'" style="width:'+width+'%">'+width+'%</div></div></li>';
								});

						html += '<p><span class="total">Total votes : <b>'+total+'</b></span>';
						$(".poll").html("");
						$(".poll-content > ul").append(html);
						$(".poll-content > ul").slideDown("slow");

					}else{
						alert("Seems Something Error ?");
					}
				}
			});

		}
	});
});

function get_poll(){
	loading();
	$(".poll-content > ul").html("");
	$.ajax({
		type: "POST",
		url: "<?php echo URL; ?>/modules/Survei/poll/ajax.php",
		data : "act=getq",
		dataType: "json",
		success: function(response){
			var ans1, ans = "";
			if($(".poll").html().length)
			$(".poll").css("display","none");
			if(response.id){
				$.each(response.answers, function(i,val) {

					ans+='<li class="list-group-item"><div class="radio"><label><input class="rad" type="radio" name="poll_options" id="'+i+'" value="'+i+'"/><label for="'+i+'">'+val+'</label></label></div></li>';

				});
				$(".poll").html("");
				$(".question").html(response.question);
				$(".poll-content > ul").append(ans);
			}else{
				$(".next").remove();
				$(".question").html('');
				$(".button").remove();
				$(".poll").fadeIn("slow").html("<span class='err'>OOPS. No more question there! :(</span>");
			}
		}
	});
}

function loading(){
	$(".poll").html("<div style='padding-left:45%'><img src='modules/Survei/poll/477.gif'></div>");
}
</script>
