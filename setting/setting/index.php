<?php

namespace Zprint;

use Zprint\Aspect\Page;

return function (Page $setting_page) {
	$setting = new TabPage('setting');
	$setting
		->setLabel('singular_name', __('Settings', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($setting_page);

	call_user_func(include_once 'googleAccess.php', $setting, $setting_page);
	call_user_func(include_once 'userRoles.php', $setting, $setting_page);
};
