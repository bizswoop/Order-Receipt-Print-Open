<?php

namespace Zprint;

class Translate
{
	public function __construct()
	{
		add_action('plugins_loaded', [$this, 'lang']);
	}

	public function lang()
	{
		load_plugin_textdomain('Print-Google-Cloud-Print-GCP-WooCommerce', false, dirname(plugin_basename(PLUGIN_ROOT_FILE)) . '/lang/');
	}
}
