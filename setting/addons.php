<?php

namespace Zprint;

use Zprint\Aspect\Box;
use Zprint\Aspect\Page;

return function (Page $setting_page) {
	$addons = new TabPage('addons');
	$addons
		->setLabel('singular_name', __('Add-ons', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($setting_page)
		->setArgument('hideForm', true)
		->setArgument('contentPage', function () {
			wp_enqueue_style('zprint_addons-style', plugins_url('assets/addons.css', PLUGIN_ROOT_FILE), [], PLUGIN_VERSION);

			$enable = admin_url('plugins.php');

			?>
			<h4>Plugins</h4>

			<section class="addons">
				<article class="addon">
					<h1>Custom Print Template</h1>
					<p>A custom development service for building your own custom print template.</p>
					<footer>
						<div class="info">
							<a href="https://www.bizswoop.com/wp/print/templates">More info</a>
						</div>
					</footer>
				</article>
				<article class="addon">
					<h1>White Label Branding</h1>
					<p>An Add-on to remove BizSwoop branding for a while label print template solution.</p>
					<footer>
						<div class="state">
							<?php if (is_plugin_active(['White-Label-Branding', 'Print-Branding-GCP-WooCommerce'])): ?>
								<span class="active">Active</span>
							<?php elseif (is_plugin_installed(['White-Label-Branding', 'Print-Branding-GCP-WooCommerce'])): ?>
								<a href="<?= $enable ?>" class="enable">Enable</a>
							<?php else: ?>
								<a href="https://www.bizswoop.com/wp/print/branding/">Install</a>
							<?php endif; ?>
						</div>
						<div class="info">
							<a href="https://www.bizswoop.com/wp/print/branding/">More info</a>
						</div>
					</footer>
				</article>
				<article class="addon">
					<h1>Product Mapping</h1>
					<p>An Add-on to allow product and category mapping to print locations.</p>
					<footer>
						<div class="state">
							<?php if (is_plugin_active('Product-Mapping-GCP-WooCommerce')): ?>
								<span class="active">Active</span>
							<?php elseif (is_plugin_installed('Product-Mapping-GCP-WooCommerce')): ?>
								<a href="<?= $enable ?>" class="enable">Enable</a>
							<?php else: ?>
								<a href="https://www.bizswoop.com/wp/print/mapping">Install</a>
							<?php endif; ?>
						</div>
						<div class="info">
							<a href="https://www.bizswoop.com/wp/print/mapping">More info</a>
						</div>
					</footer>
				</article>
			</section>
			<?php
		});

	return $addons;
};
