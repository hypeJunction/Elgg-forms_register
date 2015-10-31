<?php

use Symfony\Component\HttpFoundation\Response;

elgg_ajax_gatekeeper();

$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

$username = trim(get_input('username', ''));

$available = true;
if (get_user_by_username($username)) {
	$available = false;
}

$status = $available ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY;

$data = json_encode(array(
	'username' => $username,
	'available' => $available,
));

$response = new Response($data, $status, ['content-type' => 'application/json']);
$response->prepare(_elgg_services()->request)->send();

exit;
