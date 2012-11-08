<?php
/**
 * Elgg mathcaptcha plugin based on captcha plugin by Curverider Ltd
 * 
 * @package ElggMathCaptcha
 * @author Saket Saurabh
 *
 * Original Code
 *
 * @package ElggCaptcha
 * @author Curverider Ltd
 * @copyright Curverider Ltd 2008-2010
 */
function mathcaptcha_init() {
	global $CONFIG;
	
	// Register page handler for captcha functionality
	elgg_register_page_handler('mathcaptcha','captcha_page_handler');
	
	// Extend CSS
	elgg_extend_view('css/elgg','mathcaptcha/css');
	
	// Extend js
	elgg_extend_view('js/elgg', 'mathcaptcha/js');
	elgg_extend_view('js/initialise_elgg','mathcaptcha/js');
			
	// Default length
	$CONFIG->captcha_length = 3;
	
	// Register a function that provides some default override actions
	elgg_register_plugin_hook_handler('actionlist', 'captcha', 'captcha_actionlist_hook');
	
	// Register actions to intercept
	$actions = array();
	$actions = trigger_plugin_hook('actionlist', 'captcha', null, $actions);
	
	if (($actions) && (is_array($actions)))	{
		foreach ($actions as $action) {
			elgg_register_plugin_hook_handler("action", $action, "captcha_verify_action_hook");
		}
	}
}

function captcha_page_handler($page) {
	global $CONFIG;
	
	if (isset($page[0])) {
		set_input('captcha_token', $page[0]);
	}

	include($CONFIG->pluginspath . "mathcaptcha/captcha.php");
}

/**
 * Generate a token to act as a seed value for the captcha algorithm.
 */
function captcha_generate_token() {
	// Use action token plus some random for uniqueness
	return md5(generate_action_token(time()) . rand()); 
}

/**
 * Generate a captcha based on the given seed value and length.
 *
 * @param string $seed_token
 * @return string
 */
function captcha_generate_captcha($seed_token) {
	global $CONFIG;
	
	/*
	 * We generate a token out of the random seed value + some session data, 
	 * this means that solving via pr0n site or indian cube farm becomes
	 * significantly more tricky (we hope).
	 * 
	 * We also add the site secret, which is unavailable to the client and so should
	 * make it very very hard to guess values before hand.
	 * 
	 */

	return strtolower(substr(md5(generate_action_token(0) . $seed_token), 0, $CONFIG->captcha_length));
}

/**
 * Generate a mathematical expression and its answer based on the given code.
 *
 * @param string $code
 * @return array
 */
function captcha_get_question($code) {
	$question = array();
	switch (hexdec(substr($code, 1, 1)) % 3) {
		case 0:
			$operator = "+";
			$question[1] = hexdec(substr($code,0,1))+hexdec(substr($code,2,1));
			break;
		case 1:
			$operator = "-";
			$question[1] = hexdec(substr($code,0,1))-hexdec(substr($code,2,1));
			break;
		case 2:
			$operator = "x";
			$question[1] = hexdec(substr($code,0,1))*hexdec(substr($code,2,1));
			break;
	}
	$question[0] = hexdec(substr($code,0,1))." ".$operator." ".hexdec(substr($code,2,1))." = ?";

	return $question;
}

/**
 * Verify a captcha based on the input value entered by the user and the seed token passed.
 *
 * @param string $input_value
 * @param string $seed_token
 * @return bool
 */
function captcha_verify_captcha($input_value, $seed_token) {
	$answer = captcha_get_question(captcha_generate_captcha($seed_token));
	if (strcasecmp($input_value, $answer[1]) == 0) {
		return true;
	}
	return false;
}

/**
 * Listen to the action plugin hook and check the captcha.
 *
 * @param string $hook
 * @param string $entity_type
 * @param mixed $returnvalue
 * @param array $params
 */
function captcha_verify_action_hook($hook, $entity_type, $returnvalue, $params) {
	$token = get_input('captcha_token');
	$input = get_input('captcha_input');
	
	if (($token) && (captcha_verify_captcha($input, $token))) {
		return true;
	}
	
	register_error(elgg_echo('captcha:captchafail'));

	// forward to referrer or else action code sends to front page
	forward(REFERER);

	return FALSE;
}

/**
 * This function returns an array of actions the captcha will expect a captcha for, 
 * other plugins may add their own to this list thereby extending the use.
 *
 * @param string $hook
 * @param string $entity_type
 * @param mixed $returnvalue
 * @param array $params
 */
function captcha_actionlist_hook($hook, $entity_type, $returnvalue, $params) {
	if (!is_array($returnvalue)) {
		$returnvalue = array();
	}
		
	$returnvalue[] = 'register';
	$returnvalue[] = 'user/requestnewpassword';
		
	return $returnvalue;
}

elgg_register_event_handler('init', 'system', 'mathcaptcha_init');
