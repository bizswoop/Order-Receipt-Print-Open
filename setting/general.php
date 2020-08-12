<?php

namespace Zprint;

use Zprint\Aspect\Box;
use Zprint\Aspect\Page;

return function (Page $setting_page) {
	$general = new TabPage('general');
	$general
		->setLabel('singular_name', __('General', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($setting_page);

	$aop = new Box('automatic order printing');
	$aop
		->setLabel('singular_name', __('Automatic Order Printing', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($general);

	$enable_aop = new Input('enable automatic printing');
	$enable_aop
		->setLabel('singular_name', __('Enable Automatic Printing', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($aop)
		->setType(Input::TYPE_CHECKBOX);
		
	if (defined('\ZPOS\ACTIVE') && \ZPOS\ACTIVE) {
		$enable_aop
			->attach(['web', __('Web Orders', 'Print-Google-Cloud-Print-GCP-WooCommerce')])
			->attach(['pos', __('POS Orders', 'Print-Google-Cloud-Print-GCP-WooCommerce')])
			->attach(['order_only', __('POS Orders Save', 'Print-Google-Cloud-Print-GCP-WooCommerce')]);
	} else {
		$enable_aop
			->attach(['web', __('Enable', 'Print-Google-Cloud-Print-GCP-WooCommerce')]);
	}

	$web_auto_statuses = new Input('web orders automatic print statuses');

	if (defined('\ZPOS\ACTIVE') && \ZPOS\ACTIVE) {
		$web_auto_statuses->setLabel('singular_name', __('Web Orders Automatic Print Statuses', 'Print-Google-Cloud-Print-GCP-WooCommerce'));
	} else {
		$web_auto_statuses->setLabel('singular_name', __('Orders Automatic Print Statuses', 'Print-Google-Cloud-Print-GCP-WooCommerce'));
	}

	$statuses = wc_get_order_statuses();

	$web_auto_statuses
		->attachTo($aop)
		->setArgument('default', ['scalar' => ['pending', 'processing']])
		->setArgument('multiply')
		->setArgument('divider', '<br/>')
		->setType(Input::TYPE_CHECKBOX);

	foreach ($statuses as $status_code => $status) {
		$status_code = str_replace('wc-', '', $status_code);
		$web_auto_statuses->attach([$status_code, $status]);
	}

	$copies = new Input('copies');
	$copies
		->setLabel('singular_name', __('Copies', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($aop)
		->setArgument('default', '1')
		->setType(Input::TYPE_NUMBER);


	$optimization = new Box('optimization');
	$optimization
		->setLabel('singular_name', __('Optimization', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($general)
		->setArgument('description', __('Hosting Providers May Block Background Printing', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setLabel('singular_name', __('Print Optimization', 'Print-Google-Cloud-Print-GCP-WooCommerce'));

	$bg_printing = new Input('bg_printing');
	$bg_printing->setLabel('singular_name', __('Background Printing', 'Print-Google-Cloud-Print-GCP-WooCommerce'));

	$bg_printing_value = $bg_printing->getValue($optimization, null, $general);

	if ($setting_page->isRequested($general) && session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	if($bg_printing_value && $bg_printing_value[0] === "1")  {
		$bg_printing
			->attachTo($optimization)
			->setType(Input::TYPE_CHECKBOX)
			->setArgument('default', [])
			->attach(['1', __('Enabled', 'Print-Google-Cloud-Print-GCP-WooCommerce')]);

		if (isset($_SESSION['_zprint_bg_init_notice']) && $_SESSION['_zprint_bg_init_notice']) {
			$_SESSION['_zprint_bg_init_notice'] = null;
		}
	} else {
		if(isset($_SESSION['_zprint_bg_init_notice']) && $_SESSION['_zprint_bg_init_notice']) {
			$_SESSION['_zprint_bg_init_notice'] = null;
			add_action('admin_notices', function () {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php _e('Sorry, Background printing test failed. Contact hosting provider or submit a plug-in support request.', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></p>
				</div>
				<?php
			});
		}

		$bg_printing = new Input('bg_printing_button');
		$bg_printing
			->setArgument('description', __('Activation takes few seconds', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
			->setLabel('singular_name', __('Background Printing', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
			->attachTo($optimization)
			->setLabel('button_name', __('Enable', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
			->setType(Input::TYPE_SMART_BUTTON);

		add_filter('\Zprint\Aspect\Input\saveBefore', function ($data, $object, $key_name) use ($bg_printing) {
			if ($object === $bg_printing && $data) {
				$data = null;
				Background::init();
				session_start();
				$_SESSION['_zprint_bg_init_notice'] = true;
				sleep(5);
			}
			return $data;
		}, 10, 3);
	}
};
