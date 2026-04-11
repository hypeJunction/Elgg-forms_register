<?php

$username = trim(get_input('username', ''));

$available = true;

elgg_call(ELGG_SHOW_DISABLED_ENTITIES, function () use ($username, &$available) {
	if (get_user_by_username($username)) {
		$available = false;
	}
});

if (!$available) {
	return elgg_error_response('', '', 422);
}

return elgg_ok_response([
	'username' => $username,
	'available' => $available,
]);
