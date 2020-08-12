<?php

namespace Zprint;

class Activate
{
	public function __construct()
	{
		register_activation_hook(PLUGIN_ROOT_FILE, function ($network_wide) {
			DB::db_activate($network_wide);
		});

		add_action('wpmu_new_blog', function ($blog_id) {
			if (is_plugin_active_for_network(Setup::getPluginName())) {
				switch_to_blog($blog_id);
				DB::create_tables();
				restore_current_blog();
			}
		});
	}
}
