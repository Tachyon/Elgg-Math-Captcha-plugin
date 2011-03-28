<?php
/**
 * Elgg mathcaptcha plugin question page generator
 * 
 * @package ElggMathCaptcha
 * @author Saket Saurabh
 */

global $CONFIG;
$token = get_input('captcha_token');

$json = array();
$json['success'] = false;

if ($token) { 
	// Get the question
	$question = captcha_get_question(captcha_generate_captcha($token));
		
	// Create the array to retruned
	$json['success']=true;
	$json['q']=$question[0];		
}
else {
	$json['q']=elgg_echo('captcha:errorloading');
}

// Output the quetion as a valid JSON object
echo json_encode($json);
