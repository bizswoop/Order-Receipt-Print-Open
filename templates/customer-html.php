<?php namespace Zprint;
/* @var $order \WC_Order */
/* @var $location_data */
?>
<html>
<head>
	<style><?php include 'style.php'; ?></style>
</head>
<body>
<header>
	<?php if (get_appearance_setting('logo')) { ?>
		<img src="<?= get_appearance_setting('logo'); ?>" class="logo" alt="Logo">
	<?php } ?>
	<?php if (get_appearance_setting('Check Header')) { ?>
		<h1><?= get_appearance_setting('Check Header'); ?></h1>
	<?php } ?>
	<?php if (get_appearance_setting('Company Name')) { ?>
		<h2><?= get_appearance_setting('Company Name'); ?></h2>
	<?php } ?>
	<?php if (get_appearance_setting('Company Info')) { ?>
		<h3><?= get_appearance_setting('Company Info'); ?></h3>
	<?php } ?>
</header>
<table class="info">
	<thead>
	<tr>
		<th><?php _e('Order Number', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
		<th><?php _e('Date', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
		<?php if ($location_data['total']['cost']) { ?>
			<th><?php _e('Total', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
		<?php } ?>
		<th><?php _e('Payment Method', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="4"><?php _e('Time ordered', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
			- <?= date_i18n(\get_option('time_format', 'H:i'), $order->get_date_created()); ?></td>
	</tr>
	<?php if ($location_data['shipping']['delivery_pickup_type']) { ?>
		<tr>
			<td colspan="4"><?= get_shipping_details($order); ?></td>
		</tr>
	<?php } ?>
	</tfoot>
	<tbody>
	<tr>
		<td><?= $order->get_id(); ?></td>
		<td><?= date_i18n(\get_option('date_format', 'm/d/Y'), $order->get_date_created()); ?></td>
		<?php if ($location_data['total']['cost']) { ?>
			<td><?= wc_price($order->get_total(), array('currency' => $order->get_currency())); ?></td>
		<?php } ?>
		<td><?= $order->get_payment_method_title(); ?></td>
	</tr>
	</tbody>
</table>

<?php if (get_appearance_setting('Order Details Header')) { ?>
	<h2 class="caption"><?= get_appearance_setting('Order Details Header'); ?></h2>
<?php } ?>
<table class="order">
	<thead>
	<tr>
		<th colspan="2"><?php _e('Product', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
		<th><?php _e('Total', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<?php if ($location_data['total']['cost']) { ?>
		<tr>
			<td colspan="2"><?php _e('Subtotal', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
			<td><?= $order->get_subtotal_to_display(); ?></td>
		</tr>
	<?php } ?>
	<?php if ($location_data['shipping']['cost']) { ?>
		<tr>
			<td colspan="2"><?php _e('Shipping', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
			<td><?= wc_price($order->get_shipping_total(), array('currency' => $order->get_currency())); ?></td>
		</tr>
	<?php } ?>
	<?php if ($location_data['total']['cost']) { ?>
		<tr>
			<td colspan="2"><?php _e('Tax', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
			<td><?= wc_price($order->get_total_tax(), array('currency' => $order->get_currency())); ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php _e('Payment Method', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
			<td><?= $order->get_payment_method_title(); ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php _e('Total', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
			<td><?= wc_price($order->get_total(), array('currency' => $order->get_currency())); ?></td>
		</tr>
		<?php if ($order->get_meta('pos-tip')): ?>
			<tr>
				<td colspan="2"><?php _e('Add Tip Amount', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
				<td><?= wc_price($order->get_meta('pos-tip'), array('currency' => $order->get_currency())); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ($order->get_meta('pos-cash-tendered')): ?>
			<tr>
				<td colspan="2"><?php _e('Amount Collected', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
				<td><?= wc_price($order->get_meta('pos-cash-tendered'), array('currency' => $order->get_currency())); ?></td>
			</tr>
		<?php endif; ?>
	<?php } ?>
	</tfoot>
	<?php foreach ($order->get_items() as $item) {
		/* @var $item \WC_Order_item */
		$meta = $item['item_meta'];
		$meta = array_filter($meta, function ($key) {
			return !in_array($key, Order::getHiddenKeys());
		}, ARRAY_FILTER_USE_KEY);
		?>
		<tbody>
		<tr>
			<td colspan="2"><?= $item['name']; ?> &times; <?= $item['qty']; ?></td>
			<td
				rowspan="<?= count($meta) + 1; ?>"><?= wc_price($item->get_data()['total'], array('currency' => $order->get_currency())); ?></td>
		</tr>
		<?php $meta = array_map(function ($meta, $key) {
			$result = '<tr>';
			$result .= '<td>' . $key . '</td>';
			$result .= '<td>' . $meta . '</td>';
			$result .= '</tr>';
			return $result;
		}, $meta, array_keys($meta));
		echo implode(PHP_EOL, $meta);
		?>
		</tbody>
	<?php } ?>
		<?php foreach ($order->get_fees() as $fee) { ?>
				<tbody>
				<tr>
						<td colspan="2"><?= $fee->get_name() ?></td>
						<td><?= wc_price($fee->get_total(), array('currency' => $order->get_currency())); ?></td>
				</tr>
				</tbody>
		<?php } ?>
</table>

<?php if ($location_data['shipping']['customer_details'] && (!empty($order->get_billing_first_name()) || !empty($order->get_billing_last_name()) || !empty($order->get_billing_phone()))): ?>
	<h2 class="caption"><?php _e('Customer Details', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></h2>
<?php endif; ?>
<table class="customer">
	<?php if ($location_data['shipping']['customer_details'] && (!empty($order->get_billing_first_name()) || !empty($order->get_billing_last_name()) || !empty($order->get_billing_phone()))): ?>
		<tbody class="base">
		<?php if (!empty($order->get_billing_first_name()) || !empty($order->get_billing_last_name())) { ?>
			<tr>
				<td><?php _e('Name', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
				<td><?= $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></td>
			</tr>
		<?php } ?>
		<?php if (!empty($order->get_billing_phone())) { ?>
			<tr>
				<td><?php _e('Telephone', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
				<td><?= $order->get_billing_phone(); ?></td>
			</tr>
		<?php } ?>
		<?php if (!empty($order->get_billing_email())) { ?>
			<tr>
				<td colspan="2"><?php _e('Email', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
			</tr>
			<tr>
				<td colspan="2"><?= $order->get_billing_email(); ?></td>
			</tr>
		<?php } ?>
		</tbody>
	<?php
	endif; ?>
	<?php if (!empty($order->get_customer_note())) { ?>
		<tbody class="notes">
		<tr>
			<td width="50%" colspan="2">
				<?php _e('Order Notes', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?= $order->get_customer_note(); ?>
			</td>
		</tr>
		</tbody>
	<?php } ?>
	<?php if ($location_data['shipping']['method'] && $shipping_method = $order->get_shipping_method()) { ?>
		<tbody class="base">
		<tr>
			<td><?php _e('Shipping method', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></td>
			<td>
				<?= $shipping_method; ?>
			</td>
		</tr>
		</tbody>
	<?php } ?>
</table>
<footer>
	<?php if (get_appearance_setting('Footer Information #1')) { ?>
		<h4><?= get_appearance_setting('Footer Information #1'); ?></h4>
	<?php } ?>

	<?php if (get_appearance_setting('Footer Information #2')) { ?>
		<h5><?= get_appearance_setting('Footer Information #2'); ?></h5>
	<?php } ?>
</footer>
</body>
</html>
