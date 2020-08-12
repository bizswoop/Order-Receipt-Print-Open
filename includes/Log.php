<?php

namespace Zprint;

use Zprint\Aspect\Box;
use Zprint\Aspect\Page;
use Zprint\Aspect\InstanceStorage;

class Log
{
	const BASIC = "plugin";
	const PRINTING = "print";

	private static function getPath($file, $isPath = true)
	{
		$dir = wp_upload_dir();
		$path = $isPath ? $dir['basedir'] : $dir['baseurl'];
		$path .= DIRECTORY_SEPARATOR . 'zprint';
		if($file === null) {
			return $path;
		}
		return $path . DIRECTORY_SEPARATOR . $file;
	}

	public static function getPrintLogFilePath($isPath = true)
	{
		return static::getPath(static::PRINTING . '.log', $isPath);
	}

	public static function getBasicLogFilePath($isPath = true)
	{
		return static::getPath(static::BASIC . '.log', $isPath);
	}

	public static function isPrintLogsEnabled()
	{
		return InstanceStorage::getGlobalStorage()->asCurrentStorage(function () {
			return Page::get('printer setting')
				->scope(function ($setting_page) {
					$logs = TabPage::get('logs');
					return Box::get('print')
						->scope(function ($print) use ($logs) {
							return in_array("1", (array)Input::get('active')
								->getValue($print, null, $logs));
						});
				});
		});
	}

	public static function log($status, $type, $messageArgs)
	{
		if( !function_exists('get_plugin_data') ){
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugin_data = get_plugin_data(PLUGIN_ROOT_FILE);
		$version = $plugin_data['Version'];
		$baseArgs = [date("Y/m/d h:i:s"), $status, $version];
		$file = null;

		switch ($type) {
			case static::BASIC:
				{
					$file = static::getBasicLogFilePath();
					break;
				}
			case static::PRINTING:
				{
					if (!static::isPrintLogsEnabled()) {
						return;
					}
					$baseArgs[] = 'BG: ' . (Background::enabled() ? 'Enabled' : 'Disabled');
					$file = static::getPrintLogFilePath();
					break;
				}
			default:
				return;
		}

		$data = array_merge($baseArgs, $messageArgs);
		$data = array_filter($data, function ($e) {
			return $e !== null;
		});

		$dir = static::getPath(null);
		if(!file_exists($dir)) {
			mkdir($dir);
		}

		if ($file) {
			file_put_contents($file, implode("|", $data) . PHP_EOL, FILE_APPEND);
		}
	}

	public static function info($type, $messageArgs)
	{
		static::log("INFO", $type, $messageArgs);
	}

	public static function warn($type, $messageArgs)
	{
		static::log("WARNING", $type, $messageArgs);
	}
}
