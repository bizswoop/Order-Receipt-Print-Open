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
	<?php if (get_appearance_setting('Company Name')) { ?>
		<h2><?= get_appearance_setting('Company Name'); ?></h2>
	<?php } ?>
	<h3><?= sprintf(__('Order #%s', 'Print-Google-Cloud-Print-GCP-WooCommerce'), $order->get_id()) ?></h3>
	<h4>
		<?php _e('Date', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
		: <?= date_i18n(\get_option('date_format', 'm/d/Y'), $order->get_date_created()); ?><br />
		<?php _e('Time', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
		: <?= date_i18n(\get_option('time_format', 'H:i'), $order->get_date_created()); ?>
	</h4>
		<?php if ($location_data['shipping']['delivery_pickup_type']) { ?>
				<h4><?= get_shipping_details($order); ?></h4>
		<?php } ?>
</header>

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

<?php if ($location_data['shipping']['billing_shipping_details']) { ?>
	<h2 class="caption"><?php _e('Customer Details', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></h2>
<?php } ?>

<table class="customer_details">
	<tbody class="base">
	<?php if ($location_data['shipping']['billing_shipping_details']) { ?>
		<tr>
			<th><?php _e('Billing address', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
		</tr>
		<tr>
			<td>
				<?php echo ($address = $order->get_formatted_billing_address()) ? $address : __('N/A', 'woocommerce'); ?>
				<?php if ($order->get_billing_phone()) : ?>
					<br /><?php echo esc_html($order->get_billing_phone()); ?>
				<?php endif; ?>
				<?php if ($order->get_billing_email()) : ?>
					<p><?php echo esc_html($order->get_billing_email()); ?></p>
				<?php endif; ?>
			</td>
		</tr>
	<?php } ?>
	<?php if ($location_data['shipping']['method'] && $shipping_method = $order->get_shipping_method()) { ?>
		<tr>
			<th><?php _e('Shipping method', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
		</tr>
		<tr>
			<td>
				<?= $shipping_method; ?>
			</td>
		</tr>
	<?php } ?>
	<?php if ($location_data['shipping']['billing_shipping_details'] && !wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ($shipping = $order->get_formatted_shipping_address())) : ?>
		<tr>
			<th><?php _e('Shipping address', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></th>
		</tr>
		<tr>
			<td><?php echo $shipping; ?></td>
		</tr>
	<?php endif; ?>
	</tbody>
	<?php
	if (!empty($order->get_customer_note())): ?>
		<tbody class="notes">
		<tr>
			<th>
				<?php _e('Order Notes', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
			</th>
		</tr>
		<tr>
			<td>
				<?= $order->get_customer_note(); ?>
			</td>
		</tr>
		</tbody>
	<?php endif; ?>
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
