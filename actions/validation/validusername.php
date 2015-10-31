<?php

use Symfony\Component\HttpFoundation\Response;

elgg_ajax_gatekeeper();

$username = trim(get_input('username', ''));

try {
	$valid = validate_username($username);
} catch (Exception $e) {
	$valid = false;
}

$status = $valid ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY;

$data = json_encode(array(
	'username' => $username,
	'valid' => $valid,
));

$response = new Response($data, $status, ['content-type' => 'application/json']);
$response->prepare(_elgg_services()->request)->send();

exit;
