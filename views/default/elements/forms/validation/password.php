<?php
/**
 * Validate password strength
 * @uses $vars['data-parsley-minstrength'] Minimum password strength for validation
 */
if (empty($vars['data-parsley-minstrength'])) {
	return;
}
?>
<script>
	require(['elements/forms/validation/password']);
</script>

