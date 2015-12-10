<?php

return [

	'settings:forms:register:password:min_strength' => 'Minimum password strength',
	'settings:forms:register:password:min_strength:help' => 'Prevent user registration with weak passwords',
	'settings:forms:register:password:no_strength_check' => 'Do not check',
	'settings:forms:register:password:weak' => 'Weak (score 1)',
	'settings:forms:register:password:medium' => 'Medium (score 2)',
	'settings:forms:register:password:strong' => 'Strong (score 3)',
	'settings:forms:register:password:very_strong' => 'Very strong (score 4)',

	'settings:forms:register:first_last_name' => 'First and Last name',
	'settings:forms:register:first_last_name:help' => 'Replace Display name field with First and Last name fields',

	'settings:forms:register:autogen_name' => 'Autogenerate name',
	'settings:forms:register:autogen_name:help' => 'Remove display name field from the registration form, and generate display name based on email (or first and last name, if enabled)',

	'settings:forms:register:autogen_username' => 'Autogenerate username',
	'settings:forms:register:autogen_username:help' => 'Remove username field from the registration form, and generate it based on email (or first and last name, if enabled)',

	'settings:forms:register:autogen_username_algo' => 'Username generating algorithm',
	'settings:forms:register:autogen_username_algo:help' => 'Specify which algorithm is to be used when generating the username. On username collisions, or usernames being too short, all algorithms will suffix the username',
	'settings:forms:register:autogen_username_algo:first_name_only' => 'First name only',
	'settings:forms:register:autogen_username_algo:full_name' => 'First and last name with a dot separator',
	'settings:forms:register:autogen_username_algo:email' => 'Username extracted from email address',

	'settings:forms:register:autogen_password' => 'Autogenerate password',
	'settings:forms:register:autogen_password:help' => 'Remove password fields, and generate a high entropy random password',

	'settings:forms:register:hide_password_repeat' => 'Hide repeat password',
	'settings:forms:register:hide_password_repeat:help' => 'Remove repeat password field',

	'settings:forms:register:first_last_name' => 'First and Last name',
	'settings:forms:register:first_last_name:help' => 'Replace Display name field with First and Last name fields',

	'settings:forms:register:header' => 'Intro text',
	'settings:forms:register:header:help' => 'Text to add above the registration form (flush the caches for changes to take effect)',

	'settings:forms:register:footer' => 'Footer text',
	'settings:forms:register:footer:help' => 'Text to add below the registration form (flush the caches for changes to take effect)',

	'forms:register:header' => elgg_get_plugin_setting('header', 'forms_register', ''),
	'forms:register:footer' => elgg_get_plugin_setting('footer', 'forms_register', ''),

	'forms:register:first_name' => 'First Name',
	'forms:register:last_name' => 'Last Name',
	
	'actions:register:error:first_last_name' => 'First and Last name are required',
	'actions:register:error:password_strength' => 'The password is too weak. Please choose a more secure password',

	'validation:error:type:validusername' => 'This username contains invalid characters.',
	'validation:error:type:availableusername' => 'This username is not available.',
	'validation:error:type:minstrength' => 'The password is too weak',
	
];
