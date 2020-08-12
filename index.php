<?php
/**
 * Plugin Name: Order Receipt Print for WooCommerce Google Cloud Print
 * Plugin URI: http://www.bizswoop.com/wp/print
 * Description: Easily Add Support for Printing WooCommerce Orders with Google Cloud Print
 * Version: 3.0.13
 * Text Domain: Print-Google-Cloud-Print-GCP-WooCommerce
 * Domain Path: /lang
 * WC requires at least: 2.4.0
 * WC tested up to: 4.3.0
 * Author: BizSwoop a CPF Concepts, LLC Brand
 * Author URI: http://www.bizswoop.com
 */

namespace Zprint;

const ACTIVE = true;
const PLUGIN_ROOT = __DIR__;
const PLUGIN_ROOT_FILE = __FILE__;
const PLUGIN_VERSION = '3.0.13';
const ASPECT_PREFIX = 'zp';
defined('ABSPATH') or die('No script kiddies please!');
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';

spl_autoload_register(function ($name) {
	$raw_name = $name;
	$name = explode('\\', $name);
	$name[0] = 'includes';
	$name = array_filter($name);

	$path = __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $name) . '.php';

	$path = apply_filters(
		'zprint_autoload_path',
		$path,
		$name,
		$raw_name,
		PLUGIN_VERSION
	);

	if (file_exists($path)) {
		require_once $path;
	}
}, false);


new Setup();
