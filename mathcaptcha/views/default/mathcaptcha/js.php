$(document).ready(function(){
		$.ajax({
			url: "<?php echo $vars['url'] . "mathcaptcha/$token"; ?>" + $('#captcha_token').val(),
			dataType: 'json',
			success: function(data){
			$('#mathcaptacha').html(data.q);
			}
		});	
});
