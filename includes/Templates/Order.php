<?php

namespace Zprint\Templates;

use Zprint\Template\Basic;
use Zprint\Template\Index;
use Zprint\Template\TemplateSettings;

class Order extends Basic implements Index, TemplateSettings
{
	public function getName()
	{
		return __('Order Receipt', 'Print-Google-Cloud-Print-GCP-WooCommerce');
	}

	public function getSlug()
	{
		return 'order';
	}

	public function getTemplateSettings()
	{
		return [
			'shipping' => [
				'billing_shipping_details' => true,
				'method' => true,
				'delivery_pickup_type' => defined('\ZZHoursDelivery\ACTIVE')
			]
		];
	}
}
