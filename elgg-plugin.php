<?php

return [
	'plugin' => [
		'name' => 'Registration Form',
		'version' => '7.0.0',
	],

	'bootstrap' => \FormsRegister\Bootstrap::class,

	'actions' => [
		'validation/validusername' => [
			'access' => 'public',
		],
		'validation/availableusername' => [
			'access' => 'public',
		],
	],

	'events' => [
		'action' => [
			'register' => [
				\FormsRegister\Events::class . '::prepareActionValues' => ['priority' => 1],
			],
		],
		'register' => [
			'user' => [
				\FormsRegister\Events::class . '::registerUser' => ['priority' => 1],
			],
		],
	],

	'view_extensions' => [
		'theme_sandbox/forms' => [
			'theme_sandbox/forms/register' => [],
		],
	],

	'views' => [
		'default' => [
			'zxcvbn/' => __DIR__ . '/vendors/zxcvbn/',
		],
	],
];
