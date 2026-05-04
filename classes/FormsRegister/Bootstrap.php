<?php

namespace FormsRegister;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * @return void
	 */
	public function init() {
		if (\elgg_is_active_plugin('forms_validation')) {
			\elgg_extend_view('input/text', 'elements/forms/validation/username');
			\elgg_extend_view('input/password', 'elements/forms/validation/password');
			\elgg_extend_view('forms/register', 'elements/forms/validation/register');
		}
	}
}
