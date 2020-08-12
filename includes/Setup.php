<?php

namespace Zprint;

class Setup
{
	public function __construct()
	{
		new Translate();
		new Activate();
		new Background();

		do_action('zprint_loaded_base');
		add_action('plugins_loaded', [$this, 'init']);

		if (\file_exists(PLUGIN_ROOT . '/dev.php')) {
			require_once PLUGIN_ROOT . '/dev.php';
		}
	}

	public static function init()
	{
		if (!class_exists('WooCommerce')) {
			add_action('admin_notices', function () {
				?>
				<div class="notice notice-error is-dismissible">
					<p>
						<?php _e(
							'Print Google Cloud Print GCP WooCommerce require WooCommerce',
							'Print-Google-Cloud-Print-GCP-WooCommerce'
						); ?>
					</p>
				</div>
				<?php
			});
			return;
		}

		require_once PLUGIN_ROOT . '/setting/index.php';

		new Admin();
		new Printer();
		new POS();
		new Templates();
		new Client();

		do_action('zprint_loaded');
	}

	public static function getPluginName()
	{
		$path = basename(PLUGIN_ROOT);
		$file = basename(PLUGIN_ROOT_FILE);
		return $path . DIRECTORY_SEPARATOR . $file;
	}
}
