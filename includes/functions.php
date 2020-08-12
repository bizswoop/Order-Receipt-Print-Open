<?php

namespace Zprint;

function date_i18n($format, $time = false)
{
	if ($time instanceof \WC_DateTime) {
		return $time->date_i18n($format);
	}

	return \date_i18n($format, $time);
}

function get_plugins($plugin_folder = '')
{
	$plugins = \get_plugins($plugin_folder);
	$plugins = array_map(function ($plugin, $key) {
		return array_merge(['PluginKey' => $key], $plugin);
	}, $plugins, array_keys($plugins));
	$keys = array_map(function ($plugin) {
		return empty($plugin['TextDomain']) ? $plugin['Name'] : $plugin['TextDomain'];
	}, $plugins);
	return array_combine($keys, $plugins);
}

function is_plugin_active($plugin)
{
	if (!is_array($plugin)) {
		$plugin = [$plugin];
	}
	return array_reduce($plugin, function ($acc, $plugin) {
		try {
			$plugin = get_plugin($plugin);
			return $acc || \is_plugin_active($plugin['PluginKey']);
		} catch (\Exception $exception) {
			return $acc;
		}
	}, false);
}

function is_plugin_installed($plugin)
{
	if (!is_array($plugin)) {
		$plugin = [$plugin];
	}
	return array_reduce($plugin, function ($acc, $plugin) {
		try {
			get_plugin($plugin);
			return true;
		} catch (\Exception $exception) {
			return $acc;
		}
	}, false);
}

function get_plugin($plugin)
{
	$plugins = get_plugins();

	if (isset($plugins[$plugin])) {
		return $plugins[$plugin];
	} else {
		throw new \Exception('Plugin not found');
	}
}

function get_shipping_details($order)
{
		$shipping_type = $order->get_meta('_zh_shipping_type');
		$shipping_label = \ZZHoursDelivery\Settings::get_delivery_label($order->get_meta('_zh_shipping_type'));
		$date = date_i18n(\get_option('date_format', 'm/d/Y'), strtotime($order->get_meta('_zh_shipping_date')));
		$time = \ZZHoursDelivery\cast_to_time_format($order->get_meta('_zh_shipping_time'));
		switch ($shipping_type) {
				case 'pickup':
						$location = $order->get_meta('_zh_shipping_location');
						return "{$shipping_label} - {$location} - {$date} - {$time}";
				case 'delivery':
						return "{$shipping_label} - {$date} - {$time}";
				default:
						return $shipping_label;
		}
}
