<?php

namespace Zprint;

use Zprint\Model\Location;

return function ($tab, $page) {
	$locations = array_map(function ($el) {
		return $el->getData();
	}, Location::getAll());

	$users = array_map(function ($location) {
		return $location['users'];
	}, $locations);

	$users = array_reduce($users, function ($a, $b) {
		return array_merge($a, $b);
	}, []);
	$users = array_unique($users);

	$users_values = array_map(function ($id) {
		return get_user_by('id', $id)->display_name;
	}, $users);
	$users = array_combine($users, $users_values);

	$printers = Printer::getPrinters(); ?>
	<style>
		.zprint-list {
			margin-top: 10px;
		}

		.zprint-list td {
			line-height: 30px;
			position: relative;
		}

		.zprint-list .dashicons {
			margin-top: 5px;
		}

		.widefat .status {
			width: 120px;
			text-align: center;
		}

		.zprint-list .list {
			margin: 0;
			margin-top: -5px;
			left: 0;
			right: 0;
			max-height: 30px;
			line-height: 30px;
			padding: 5px;
			overflow: hidden;
			position: absolute;
			background: white;
			text-overflow: ellipsis;
		}

		.zprint-list tr:nth-child(2n+1) .list {
			background: #f9f9f9;
		}

		.zprint-list .list:hover {
			max-height: none;
			z-index: 2;
		}

		.zprint-list .list li {
			display: inline;
			word-wrap: break-spaces;
			margin-bottom: 0;
		}

		.zprint-list {
			min-width: 1024px;
		}
		.zprint-list-view {
			overflow-y: auto;
		}
	</style>
	<div class="zprint-list-view">
		<table class="wp-list-table widefat fixed striped posts zprint-list">
			<tbody>
			<tr>
				<th style="width: 200px;">
					<?php _e('Title', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</th>
				<?php if (defined('\ZPOS\ACTIVE') && \ZPOS\ACTIVE): ?>
					<th class="status">
						<?php _e('Web Order', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</th>
				<?php else: ?>
					<th class="status">
						<?php _e('Enabled', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</th>
				<?php endif; ?>
				<?php if (defined('\ZPOS\ACTIVE') && \ZPOS\ACTIVE): ?>
					<th class="status">
						<?php _e('POS Order', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</th>
				<?php endif; ?>
				<th>
					<?php _e('Printers', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</th>
				<th>
					<?php _e('Users', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</th>
				<th>
					<?php _e('Template', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</th>
				<th>
					<?php _e('Size', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</th>
				<th style="width: 100px; text-align: right;">
					<?php _e('Actions', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</th>
			</tr>
			<?php foreach ($locations as $location): ?>
				<tr class="no-items">
					<td><span class="dashicons dashicons-exerpt-view" style="margin-right: 5px;"></span><strong><a
								href="<?= add_query_arg('id', $location['id'], $page->getUrl($tab)); ?>"
								class="row-title"><?= $location['title']; ?></a></strong></td>
					<td class="status">
						<span class="dashicons dashicons-<?= ($location['web_order'] ? 'yes' : 'minus') ?>"></span>
					</td>
					<?php if (defined('\ZPOS\ACTIVE') && \ZPOS\ACTIVE): ?>
						<td class="status">
							<span class="dashicons dashicons-<?= ($location['pos_order_only'] ? 'yes' : 'minus') ?>"></span>
						</td>
					<?php endif; ?>
					<td>
						<ul class="list">
							<?= implode(", ", array_map(function ($printer) use ($printers) {
								return '<li>' . $printers[$printer] . '</li>';
							}, $location['printers'])); ?>
						</ul>
					</td>
					<td>
						<ul class="list">
							<?= implode(", ", array_map(function ($user) use ($users) {
								return '<li>' . $users[$user] . '</li>';
							}, $location['users'])); ?>
						</ul>
					</td>
					<td>
						<?= Location::getTemplates()[$location['template']]; ?>
					</td>
					<td>
						<?php
						if ($location['size'] !== "custom") {
							echo Location::getSizes()[$location['size']['name']];
						} else {
							if ($location['width'] > 0) {
								echo 'W&nbsp;' . $location['width'] . __('mm', 'Print-Google-Cloud-Print-GCP-WooCommerce');
								echo ' &times; ';
								echo 'H&nbsp;' . ($location['height'] > 0 ? $location['height'] . __('mm', 'Print-Google-Cloud-Print-GCP-WooCommerce') : __('Auto', 'Print-Google-Cloud-Print-GCP-WooCommerce'));
							} else {
								echo __('Auto', 'Print-Google-Cloud-Print-GCP-WooCommerce');
							}
						} ?>
					</td>
					<td style="text-align: right;">
						<a href="<?= add_query_arg('id', $location['id'], $page->getUrl($tab)); ?>" class="button">
							<?php _e('Edit', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
};


