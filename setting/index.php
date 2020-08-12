<?php

namespace Zprint;

use Zprint\Aspect\InstanceStorage;
use Zprint\Aspect\Page;
use Zprint\Aspect\Box;

$setting_page = new Page('printer setting');

$setting_page
	->setArgument('parent_slug', 'woocommerce')
	->setLabel('singular_name', __('Print Settings', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
	->scope(function (Page $setting_page) {
		call_user_func(include_once 'general.php', $setting_page);
		call_user_func(include_once 'locations.php', $setting_page);
		call_user_func(include_once 'setting/index.php', $setting_page);
		call_user_func(include_once 'logs.php', $setting_page);
		call_user_func(include_once 'addons.php', $setting_page);
	});

function get_appearance_setting($name)
{
	global $zprint_appearance;
	$allowed_names = [
		'logo',
		'Check Header',
		'Company Name',
		'Company Info',
		'Order Details Header',
		'Footer Information #1',
		'Footer Information #2'
	];

	if (!in_array($name, $allowed_names)) {
		return false;
	}

	$data = $zprint_appearance[$name];

	if ($name === 'logo') {
		if (empty($data)) {
			return false;
		}
		$path = get_attached_file($data);
		$type = pathinfo($path, PATHINFO_EXTENSION);
		$data = file_get_contents($path);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
		return $base64;
	}
	return $data;
}
