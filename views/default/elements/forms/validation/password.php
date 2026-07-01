<?php
/**
 * Validate password strength
 * @uses $vars['data-parsley-minstrength'] Minimum password strength for validation
 */
if (empty($vars['data-parsley-minstrength'])) {
	return;
}

elgg_import_esm('elements/forms/validation/password');
