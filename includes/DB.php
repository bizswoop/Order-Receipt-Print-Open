<?php

namespace Zprint;

class DB
{
	const Prefix = "zp_";
	/* Tables */
	const Locations = 'locations';

	public static function create_tables()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . static::Prefix;
		$tables = static::get_option('zprint_tables', []);
		$locations = $prefix . static::Locations;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		if (!in_array('base', $tables)) {
			$wpdb->query(
				"CREATE TABLE IF NOT EXISTS `{$locations}` (
				id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				title TEXT NOT NULL DEFAULT '',
				web_order INT(1),
        pos_order_only INT(1),
        template INT(5),
        printers LONGTEXT,
        users LONGTEXT,
				created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				created_at_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				updated_at_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
			) {$collate};"
			);

			$tables[] = 'base';
		}

		if (!in_array('options', $tables)) {
			$wpdb->query(
				"ALTER TABLE `{$locations}` ADD options LONGTEXT AFTER template"
			);

			$locations_data = $wpdb->get_results("SELECT id, template FROM `{$locations}`");

			foreach ($locations_data as $location_data) {
				$options = call_user_func(function ($template) {
					switch ($template) {
						case 1:
							return [
								'size' => 'custom',
								'width' => 80,
								'height' => 100,
								'template' => 'customer',
							];
						case 2:
							return [
								'size' => 'custom',
								'width' => 80,
								'height' => 100,
								'template' => 'order',
							];
						case 3:
							return [
								'size' => 'letter',
								'template' => 'details',
							];
						case 4:
							return [
								'size' => 'a4',
								'template' => 'details',
							];
						case 5:
							return [
								'size' => 'letter',
								'template' => 'customer',
							];
						case 6:
							return [
								'size' => 'letter',
								'template' => 'order',
							];
						case 7:
							return [
								'size' => 'a4',
								'template' => 'customer',
							];
						case 8:
							return [
								'size' => 'a4',
								'template' => 'order',
							];
						default:
							return [
								'size' => 'custom',
								'width' => 80,
								'height' => 100,
								'template' => 'details',
							];
					}
				}, $location_data->template);

				$wpdb->update(
					$locations,
					['options' => maybe_serialize($options)],
					['id' => $location_data->id],
					['%s'],
					['%d']
				);
			}

			$wpdb->query(
				"ALTER TABLE `{$locations}` DROP COLUMN template"
			);

			$tables[] = 'options';
		}
		static::update_option('zprint_tables', $tables);
	}

	public static function db_activate($network_wide)
	{
		global $wpdb;
		if (is_multisite() && $network_wide) {
			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
			foreach ($blog_ids as $blog_id) {
				switch_to_blog($blog_id);
				static::create_tables();
				restore_current_blog();
			}
		} else {
			static::create_tables();
		}
	}

	private static function get_option($name, $default = false) {
		if(is_multisite()) {
			return get_blog_option(get_current_blog_id(), $name, $default);
		} else {
			return get_option($name, $default);
		}
	}

	private static function update_option($name, $value) {
		if(is_multisite()) {
			return update_blog_option(get_current_blog_id(), $name, $value);
		} else {
			return update_option($name, $value);
		}
	}
}
