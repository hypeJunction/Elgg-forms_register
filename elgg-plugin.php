<?php

return [
	'plugin' => [
		'name' => 'Registration Form',
		'version' => '4.0.0',
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

	'hooks' => [
		'action' => [
			'register' => [
				\FormsRegister\Hooks::class . '::prepareActionValues' => ['priority' => 1],
			],
		],
		'register' => [
			'user' => [
				\FormsRegister\Hooks::class . '::registerUser' => ['priority' => 1],
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
			'zxcvbn/' => __DIR__ . '/vendor/bower-asset/zxcvbn/dist/',
		],
	],
];
