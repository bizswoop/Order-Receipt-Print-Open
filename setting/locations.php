<?php

namespace Zprint;

use Zprint\Aspect\Page;
use Zprint\Aspect\Box;
use Zprint\Exception\DB as DBException;
use Zprint\Model\Location;
use Zprint\Template\Index;
use Zprint\Template\Options;

return function (Page $setting_page) {
	$locations = new TabPage('locations');
	$locations
		->setLabel('singular_name', __('Locations', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($setting_page)
		->setArgument('hideForm', true)
		->setArgument('contentPage', function ($page) use ($setting_page) {
			try {
				if (isset($_REQUEST['id'])) {
					$id = $_REQUEST['id'];
					try {
						$location = ($id === "new") ? new Location() : new Location($id);

						if ($_SERVER['REQUEST_METHOD'] === "GET") {
							$single = include __DIR__.'/locations/single.php';
							$single($location, $page, $setting_page);
						} else {
							$process = include __DIR__.'/locations/process.php';
							$process($location, $page, $setting_page);
						}
					} catch (DBException $exception) {
						wp_die($exception->getMessage());
					}
				} else {
					$index = include __DIR__.'/locations/index.php';
					$index($page, $setting_page);
				}
			} catch (\Exception $exception) { ?>
				<div class="notice notice-error is-dismissible">
					<p><?= $exception->getMessage(); ?></p>
				</div>
			<?php }
		});

	if ($setting_page->isRequested($locations)) {
		if (!session_id()) {
			session_start(['read_and_close' => true]);
		}
		$status = isset($_SESSION[Page::getName($setting_page, $locations)]) ? $_SESSION[Page::getName($setting_page, $locations)] : null;
		$_SESSION[Page::getName($setting_page, $locations)] = null;

		if ($status) {
			add_action('admin_notices', function () use ($status) {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php
						switch ($status) {
							case 'deleted':
								{
									_e('Location deleted', 'Print-Google-Cloud-Print-GCP-WooCommerce');
									break;
								}

							case 'saved':
								{
									_e('Location saved', 'Print-Google-Cloud-Print-GCP-WooCommerce');
									break;
								}
							case
							'printed':
								{
									_e('Test print initiated, view log for details', 'Print-Google-Cloud-Print-GCP-WooCommerce');
									break;
								}
						}
						?>
					</p>
				</div>
			<?php });
		}

		$setting_page
			->setArgument(
				'new_link',
				'<a href="' . add_query_arg('id', 'new', $setting_page->getUrl($locations)) . '" class="alignright page-title-action">' . __('Add new', 'Print-Google-Cloud-Print-GCP-WooCommerce') . '</a>'
			);
	};
};

