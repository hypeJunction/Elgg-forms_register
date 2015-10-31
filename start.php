<?php

/**
 * Registration form
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'forms_register_init');

/**
 * Initialize the plugin
 * @return void
 */
function forms_register_init() {

	if (elgg_is_active_plugin('forms_validation')) {
		elgg_extend_view('input/text', 'elements/forms/validation/username');
		elgg_extend_view('input/password', 'elements/forms/validation/password');
		elgg_extend_view('forms/register', 'elements/forms/validation/register');
	}

	elgg_extend_view('theme_sandbox/forms', 'theme_sandbox/forms/register');

	elgg_register_action('validation/validusername', __DIR__ . '/actions/validation/validusername.php', 'public');
	elgg_register_action('validation/availableusername', __DIR__ . '/actions/validation/availableusername.php', 'public');

	elgg_register_plugin_hook_handler('action', 'register', 'forms_register_prepare_action_values', 1);
	elgg_register_plugin_hook_handler('register', 'user', 'forms_register_user_hook', 1);
}

/**
 * Generates a unique available and valid username
 *
 * @param string $username Username prefix
 * @return string
 */
function forms_register_generate_username($username = '') {

	$username = iconv('UTF-8', 'ASCII//TRANSLIT', $username);
	$blacklist = '/[\x{0080}-\x{009f}\x{00a0}\x{2000}-\x{200f}\x{2028}-\x{202f}\x{3000}\x{e000}-\x{f8ff}]/u';
	$blacklist2 = array(' ', '\'', '/', '\\', '"', '*', '&', '?', '#', '%', '^', '(', ')', '{', '}', '[', ']', '~', '?', '<', '>', ';', '|', '¬', '`', '@', '-', '+', '=');
	$username = preg_replace($blacklist, '', $username);
	$username = str_replace($blacklist2, '.', $username);

	$ia = elgg_set_ignore_access(true);
	$ha = access_get_show_hidden_status();
	access_show_hidden_entities(true);

	$minlength = elgg_get_config('minusername') ? : 4;
	$available = strlen($username) >= $minlength && !get_user_by_username($username);
	try {
		validate_username($username);
	} catch (Exception $e) {
		$username = 'u';
		$available = false;
	}

	while (!$available) {
		$randlength = strlen($username) < $minlength ? 6 : 4;
		$suffix = (new ElggCrypto())->getRandomString($randlength, '0123456789');
		$username = "$username$suffix";
		$available = get_user_by_username($username);
	}

	access_show_hidden_entities($ha);
	elgg_set_ignore_access($ia);

	return $username;
}

/**
 * Validates and prepares values for 'register' action
 * @return void
 */
function forms_register_prepare_action_values() {

	elgg_make_sticky_form('register');

	$first_name = get_input('first_name');
	$last_name = get_input('last_name');
	$email = get_input('email', '');
	$name = get_input('name');
	$username = get_input('username');
	$password = get_input('password');

	list($email_username) = explode('@', $email);

	if (elgg_get_plugin_setting('first_last_name', 'forms_register') && !$name) {
		if (!$first_name || !$last_name) {
			register_error(elgg_echo('actions:register:error:first_last_name'));
			forward(REFERRER);
		}
		set_input('name', "$first_name $last_name");
	} else if (elgg_get_plugin_setting('autogen_name', 'forms_register') && !$name) {
		set_input('name', $email_username);
	}

	if (elgg_get_plugin_setting('autogen_username', 'forms_register') && !$username) {
		$username = forms_register_generate_username($first_name ? : $email_username);
		set_input('username', $username);
	}

	if (elgg_get_plugin_setting('autogen_password', 'forms_register')) {
		$password = generate_random_cleartext_password();
		set_input('password', $password);
		set_input('password2', $password);
	} else {
		if ($min_strength = elgg_get_plugin_setting('min_password_strength', 'forms_register')) {
			// @todo: add other user inputs
			$zxcvbn = new \ZxcvbnPhp\Zxcvbn();
			$strength = $zxcvbn->passwordStrength($password);
			if ($strength < $min_strength) {
				register_error(elgg_echo('actions:register:error:password_strength'));
				forward(REFERER);
			}
		}
		if (elgg_get_plugin_setting('hide_password_repeat', 'forms_register')) {
			set_input('password2', $password);
		}
	}
}

/**
 * Saves additional input values on user registration
 *
 * @param string $hook   "register"
 * @param string $type   "user"
 * @param string $return Allow registration to proceed
 * @param string $params Hook params
 * @return void
 */
function forms_register_user_hook($hook, $type, $return, $params) {

	$user = elgg_extract('user', $params);

	if (elgg_get_plugin_setting('first_last_name', 'forms_register')) {
		$user->first_name = get_input('first_name');
		$user->last_name = get_input('last_name');
	}
}
