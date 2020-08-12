<?php

namespace Zprint;

use Zprint\Aspect\Page;
use Zprint\Model\Location;
use Zprint\Template\Index;
use Zprint\Template\Options;

return function (Location $location, TabPage $tab, Page $page) {

	$redirect_to = $page->getUrl($tab);
	if (isset($_POST['test_print'])) {
		$order = Order::getSampleOrder();
		Printer::reprintOrder($order, [$location->getID()]);
		$_SESSION[Page::getName($page, $tab)] = 'printed';
		$redirect_to = add_query_arg('id', $location->getID(), $redirect_to);
	} elseif (isset($_POST['delete'])) {
		if (count(Admin\Location::getBoxes())) {
			Admin\Location::processBoxes($location, true);
		}
		$location->delete();
		$_SESSION[Page::getName($page, $tab)] = 'deleted';
	} else {
		$location->title = esc_sql($_POST['zpl_title']);
		$location->enabledWEB = (bool)$_POST['zpl_web_order'];
		$location->enabledPOS = (bool)$_POST['zpl_pos_order_only'];
		$users = (array)$_POST['zpl_users'];
		$location->users = array_filter($users, function ($user) {
			return get_user_by('id', $user);
		});
		$printers = (array)$_POST['zpl_printers'];
		$all_printers = array_keys(Printer::getPrinters());
		$location->printers = array_filter($printers, function ($printer) use ($all_printers) {
			return in_array($printer, $all_printers);
		});

		$template_slug = $_POST['zpl_template'];
		if (Location::validateTemplate($template_slug)) {
			$location->template = $template_slug;

			$template = Templates::getTemplate($template_slug);

			if ($template instanceof Options && $template instanceof Index) {
				$location->setTemplateOption($template->processOptions($location->getTemplateOption()));
			}
		}

		$size = $_POST['zpl_size'];
		if (Location::validateSize($size)) {
			$location->size = $size;
		}

		$format = $_POST['zpl_format'];
		if (Location::validateFormat($format)) {
			$location->format = $format;
		}
		if ($format === 'plain') {
			$location->symbolsWidth = +$_POST['zpl_symbolsLength'];
			$location->printSymbolsDebug = isset($_POST['zpl_printSymbolsDebug']);
		} else {
			$location->font = [
				'basicSize' => +$_POST['zpl_fontSize'],
				'basicWeight' => +$_POST['zpl_fontWeight'],
				'headerSize' => +$_POST['zpl_headerSize'],
				'headerWeight' => +$_POST['zpl_headerWeight'],
			];
		}

		$orientation = $_POST['zpl_orientation'];
		if (Location::validateOrientation($orientation)) {
			$location->orientation = $orientation;
		}

		if (isset($_POST['zpl_margins_custom'])) {
			$margins = array_slice($_POST['zpl_margins'], 0, 4);
			$margins = array_map('intval', $margins);
			$margins = array_replace(array_fill(0, 4, 0), $margins);
		} else {
			$margins = null;
		}
		$location->margins = $margins;

		$shipping = $_POST['zpl_shipping'];
		$location->shipping = [
			'cost' => boolval($shipping['cost']),
			'billing_shipping_details' => boolval($shipping['billing_shipping_details']),
			'customer_details' => boolval($shipping['customer_details']),
			'method' => boolval($shipping['method']),
			'delivery_pickup_type' => boolval($shipping['delivery_pickup_type']),
		];

		$total = $_POST['zpl_total'];
		$location->total = [
			'cost' => boolval($total['cost'])
		];

		if ($location->size === "custom") {
			$location->width = +$_POST['zpl_width'];
			$location->height = +$_POST['zpl_height'];
		} else {
			$location->width = null;
			$location->height = null;
		}

		$logo = (isset($_POST['zpl_appearance_logo']) && !empty($_POST['zpl_appearance_logo']))
			? (int)$_POST['zpl_appearance_logo']
			: null;

		$location->appearance = [
			'logo' => $logo,
			'Check Header' => esc_sql($_POST['zpl_appearance_check_header']),
			'Company Name' => esc_sql($_POST['zpl_appearance_company_name']),
			'Company Info' => esc_sql($_POST['zpl_appearance_company_info']),
			'Order Details Header' => esc_sql($_POST['zpl_appearance_order_details_header']),
			'Footer Information #1' => esc_sql($_POST['zpl_appearance_footer_information_1']),
			'Footer Information #2' => esc_sql($_POST['zpl_appearance_footer_information_2']),
		];

		if (!$location->getID()) {
			$location->save();
		}

		if (count(Admin\Location::getBoxes())) {
			Admin\Location::processBoxes($location);
		}
		$location->save();

		$_SESSION[Page::getName($page, $tab)] = 'saved';
		$redirect_to = add_query_arg('id', $location->getID(), $redirect_to);
	}

	header("Location: " . $redirect_to);
	exit;
};
