<?php

namespace Zprint;

use Zprint\Aspect\Box;
use Zprint\Aspect\InstanceStorage;
use Zprint\Aspect\Page;

class Order
{
	public static function getSampleOrder()
	{
		static $order = false;
		if ($order === false) {
			$orders = \wc_get_orders([
				'limit' => 1,
				'type' => 'shop_order',
				'orderby' => 'date',
				'order' => 'DESC',
				'status' => 'completed',
				'return' => 'ids'
			]);

			if (count($orders) > 0) {
				$order = array_pop($orders);
			}
		}

		return $order ? $order : null;
	}

	public static function getValidOrderStatusForWebPrinting()
	{
		return (array)InstanceStorage::getGlobalStorage()->asCurrentStorage(function () {
			$setting_page = Page::get('printer setting');
			return $setting_page->scope(function () {
				$tab = TabPage::get('general');
				$box = Box::get('automatic order printing');
				$input = Input::get('web orders automatic print statuses');
				return $input->getValue($box, null, $tab);
			});
		});
	}

	public static function getHiddenKeys()
	{
		return apply_filters('woocommerce_hidden_order_itemmeta', [
			'_reduced_stock',
			'_qty',
			'_tax_class',
			'_product_id',
			'_variation_id',
			'_line_subtotal',
			'_line_subtotal_tax',
			'_line_total',
			'_line_tax',
			'method_id',
			'cost',
			'Additional'
		]);
	}
}
