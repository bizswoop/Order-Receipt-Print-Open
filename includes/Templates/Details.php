<?php

namespace Zprint\Templates;

use Zprint\Template\Basic;
use Zprint\Template\Index;
use Zprint\Template\TemplateSettings;

class Details extends Basic implements Index, TemplateSettings
{
	public function getName()
	{
		return __('Customer Order Receipt', 'Print-Google-Cloud-Print-GCP-WooCommerce');
	}

	public function getSlug()
	{
		return 'details';
	}

	public function getTemplateSettings()
	{
		return [
			'shipping' => [
				'cost' => true,
				'billing_shipping_details' => true,
				'method' => true,
				'delivery_pickup_type' => defined('\ZZHoursDelivery\ACTIVE')
			],
			'total' => [
				'cost' => true
			]
		];
	}
}
