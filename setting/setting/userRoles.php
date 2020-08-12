<?php

namespace Zprint;

use Zprint\Aspect\Box, Zprint\Aspect\Page;

return function (TabPage $setting, Page $setting_page) {
	$userRoles = new Box('location user roles');
	$userRoles
		->setLabel('singular_name', __('Location User Roles', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setArgument('description', __('Select Roles for Location Mapping to Users', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($setting);

	$roles = wp_roles()->role_names;
	$roles = array_map(function ($value, $key) {
		return [$key, $value];
	}, array_values($roles), array_keys($roles));

	$rolesInput = new Input('roles');
	$rolesInput
		->attachTo($userRoles)
		->setLabel('singular_name', __('Selected Roles', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setArgument('default', ['scalar' => ['administrator', 'shop_manager']])
		->setArgument('multiply', true)
		->setType(Input::TYPE_CHECKBOX)
		->attachFew($roles);
};
