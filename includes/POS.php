<?php
namespace Zprint;

class POS
{
	public function __construct()
	{
		\add_action('woocommerce_rest_insert_shop_order_object', [static::class, 'processInsertOrderFromPOS'], 1000);
	
		add_filter('pre_option_zprint_print_pos', function () {
			return Printer::isEnabledPrinting(Printer::POS_PRINT) ? "yes" : "no";
		});
		add_filter('pre_option_zprint_print_web', function () {
			return Printer::isEnabledPrinting(Printer::WEB_PRINT) ? "yes" : "no";
		});
		add_filter('pre_option_zprint_print_pos_order_only', function () {
			return Printer::isEnabledPrinting(Printer::ORDER_ONLY_PRINT) ? "yes" : "no";
		});
	}
	public static function processInsertOrderFromPOS($order_id)
	{
		$order = new \WC_Order($order_id);
		$print = (bool)$order->get_meta('_pos_print', true);
		if ($print) {
			$user_id = wp_get_current_user()->id;
			Printer::printOrder($order, [[LocationFilter::USER, $user_id], [LocationFilter::POS_ORDER_ONLY, true, 'bool']]);
		}
	}
}
