<?php

$form = elgg_view_form('register', array(
	'action' => '#',
	'method' => 'GET',
	'validate' => true,
));
echo elgg_view_module('aside', 'Registration Form', $form);