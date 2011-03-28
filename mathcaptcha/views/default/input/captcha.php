<?php
/**
 * Elgg mathcaptcha plugin captcha hook view override.
 * 
 * @package ElggMathCaptcha
 * @author Saket Saurabh
 */

// Generate a token which is then passed into the captcha algorithm for verification
$token = captcha_generate_token();
?>
	
<div class="captcha">
	<input type="hidden" name="captcha_token" id="captcha_token" value="<?php echo $token; ?>" />
	<label>
		<?php echo elgg_echo('captcha:entercaptcha'); ?><br /><br />
		<div class="captcha-right">
			<span id="mathcaptacha"></span>
		</div><br />
		<div class="captcha-left">
			<?php echo elgg_view('input/text', array('internalname' => 'captcha_input', 'class' => 'captcha-input-text')); ?>
		</div>
	</label>
</div>