<?php
/**
 * Username input with validation
 *
 * @uses $vars['data-parsley-validusername'] Validate username characters
 * @uses $vars['data-parsley-availableusername'] Validate username availability
 */
if (empty($vars['data-parsley-validusername']) && empty($vars['data-parsley-availableusername'])) {
	return;
}
?>
<script>
	require(['elements/forms/validation/username']);
</script>