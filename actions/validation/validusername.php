<?php

$username = trim(get_input('username', ''));

$valid = true;
try {
    elgg()->accounts->assertValidUsername($username);
} catch (\Elgg\Exceptions\Configuration\RegistrationException $e) {
    $valid = false;
}

if (!$valid) {
    return elgg_error_response('', '', 422);
}

return elgg_ok_response([
    'username' => $username,
    'valid' => $valid,
]);
