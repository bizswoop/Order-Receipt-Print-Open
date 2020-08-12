<?php

namespace Zprint;

use Zprint\Aspect\Box;
use Zprint\Aspect\Page;

return function (Page $setting_page) {
	$logs = new TabPage('logs');
	$logs
		->setLabel('singular_name', __('Logs', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($setting_page);

	if (file_exists(Log::getBasicLogFilePath())) {
		$plugin = new Box('plugin');
		$plugin
			->attachTo($logs)
			->setLabel('singular_name', __('Plugin', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
			->scope(function ($plugin) use ($logs, $setting_page) {
				$link = new Input('link');
				$link
					->setLabel('singular_name', __('Log file content', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
					->setType(Input::TYPE_INFO)
					->setArgument('content', '<a href="' . Log::getBasicLogFilePath(false) . '" target="_blank">See plugin.log</a>')
					->attachTo($plugin);

				$clear = new Input('clear');
				$clear
					->setLabel('singular_name', __('Log file', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
					->setLabel('button_name', __('Clear log file', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
					->attachTo($plugin)
					->setType(Input::TYPE_SMART_BUTTON);

				if ($setting_page->isRequested($logs)) {
					$size = human_filesize(filesize(Log::getBasicLogFilePath()));
					$clear->setLabel('singular_name', sprintf(__('Log file (Size: %s)', 'Print-Google-Cloud-Print-GCP-WooCommerce'), $size));
				}
				add_filter('\Zprint\Aspect\Input\saveBefore', function ($data, $object, $key_name) use ($clear) {
					if ($object === $clear && $data) {
						file_put_contents(Log::getBasicLogFilePath(), '');
					}
					return $data;
				}, 10, 3);
			});
	}

	$print = new Box('print');
	$print
		->setLabel('singular_name', __('Print', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($logs)
		->scope(function ($print) use ($logs, $setting_page) {
			$input = new Input('active');
			$input
				->setLabel('singular_name', __('Active', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
				->setType(Input::TYPE_CHECKBOX)
				->attach([1, __('Save info logs', 'Print-Google-Cloud-Print-GCP-WooCommerce')])
				->attachTo($print);

			if (file_exists(Log::getPrintLogFilePath())) {
				$link = new Input('link');
				$link
					->setLabel('singular_name', __('Log file content', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
					->setType(Input::TYPE_INFO)
					->setArgument('content', '<a href="' . Log::getPrintLogFilePath(false) . '" target="_blank">'.__('See print.log', 'Print-Google-Cloud-Print-GCP-WooCommerce').'</a>')
					->attachTo($print);

				$clear = new Input('clear');
				$clear
					->setLabel('singular_name', __('Log file', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
					->setLabel('button_name', __('Clear log file', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
					->attachTo($print)
					->setType(Input::TYPE_SMART_BUTTON);

				if ($setting_page->isRequested($logs)) {
					$size = human_filesize(filesize(Log::getPrintLogFilePath()));
					$clear->setLabel('singular_name', sprintf(__('Log file (Size: %s)', 'Print-Google-Cloud-Print-GCP-WooCommerce'), $size));
				}
				add_filter('\Zprint\Aspect\Input\saveBefore', function ($data, $object, $key_name) use ($clear) {
					if ($object === $clear && $data) {
						file_put_contents(Log::getPrintLogFilePath(), '');
					}
					return $data;
				}, 10, 3);
			}
		});

	return $logs;
};

function human_filesize($bytes, $decimals = 2)
{
	$sz = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
