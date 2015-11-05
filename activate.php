<?php

if (!is_callable('elgg_view_input')) {
	register_error('"Registration Form" relies on API that has not yet been included into core. Please download and enable "Forms API" plugin that provides temporary wrappers.');
	return false;
}

