<?php
$entity = elgg_extract('entity', $vars);

echo elgg_view_input('select', array(
	'name' => 'params[min_password_strength]',
	'value' => $entity->min_password_strength,
	'options_values' => array(
		0 => elgg_echo('settings:forms:register:password:no_strength_check'),
		1 => elgg_echo('settings:forms:register:password:weak'),
		2 => elgg_echo('settings:forms:register:password:medium'),
		3 => elgg_echo('settings:forms:register:password:strong'),
		4 => elgg_echo('settings:forms:register:password:very_strong'),
	),
	'label' => elgg_echo('settings:forms:register:password:min_strength'),
	'help' => elgg_echo('settings:forms:register:password:min_strength:help'),
));

echo elgg_view_input('select', array(
	'name' => 'params[first_last_name]',
	'value' => $entity->first_last_name,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('settings:forms:register:first_last_name'),
	'help' => elgg_echo('settings:forms:register:first_last_name:help'),
));

echo elgg_view_input('select', array(
	'name' => 'params[autogen_name]',
	'value' => $entity->autogen_name,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('settings:forms:register:autogen_name'),
	'help' => elgg_echo('settings:forms:register:autogen_name:help'),
));

echo elgg_view_input('select', array(
	'name' => 'params[autogen_username]',
	'value' => $entity->autogen_username,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('settings:forms:register:autogen_username'),
	'help' => elgg_echo('settings:forms:register:autogen_username:help'),
));

echo elgg_view_input('select', array(
	'name' => 'params[autogen_password]',
	'value' => $entity->autogen_password,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('settings:forms:register:autogen_password'),
	'help' => elgg_echo('settings:forms:register:autogen_password:help'),
));

echo elgg_view_input('select', array(
	'name' => 'params[hide_password_repeat]',
	'value' => $entity->hide_password_repeat,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('settings:forms:register:hide_password_repeat'),
	'help' => elgg_echo('settings:forms:register:hide_password_repeat:help'),
));

echo elgg_view_input('longtext', array(
	'name' => 'params[header]',
	'value' => $entity->header,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('settings:forms:register:header'),
	'help' => elgg_echo('settings:forms:register:header:help'),
));

echo elgg_view_input('longtext', array(
	'name' => 'params[footer]',
	'value' => $entity->footer,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('settings:forms:register:footer'),
	'help' => elgg_echo('settings:forms:register:footer:help'),
));