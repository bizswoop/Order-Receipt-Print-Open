<?php

namespace Zprint;

use \Zprint\Model\Location;
use \Zprint\Aspect\InstanceStorage;
use \Zprint\Aspect\Page;
use \Zprint\Aspect\Box;

class Printer
{
	const POS_PRINT = 'pos';
	const WEB_PRINT = 'web';
	const ORDER_ONLY_PRINT = 'order_only';

	public function __construct()
	{
		if (Printer::isEnabledPrinting(Printer::WEB_PRINT)) {
			\add_action('woocommerce_order_status_changed', [static::class, 'processOrderStatusChange']);
		}
	}

	public static function processOrderStatusChange($order)
	{
		$order = new \WC_Order($order);

		$valid_status = $order->has_status(Order::getValidOrderStatusForWebPrinting());

		Log::info(Log::PRINTING, ['check process print', $order->get_id(), $order->get_status()]);

		if ($valid_status && $order->get_meta('_pos_by', true) !== 'pos' && !$order->needs_payment() && !$order->get_meta('_zprint_web_printer', true)) {
			Log::info(Log::PRINTING, ['process print', $order->get_id()]);
			$order->add_meta_data('_zprint_web_printer', true, true);
			$order->save();
			Printer::printOrder($order, [[LocationFilter::WEB_ORDER, true, 'bool']]);
		}
	}

	public static function getTemplates($filter, $order)
	{
		$locations = Location::getAll();
		$all_locations = $locations;

		if ($filter instanceof LocationFilter) {
			$locations = $filter->filter($locations);
		} elseif (is_array($filter)) {
			$locations = array_reduce($filter, function ($locations, $filter) {
				/* @var $filter LocationFilter */
				return $filter->filter($locations);
			}, $locations);
		}

		$locations = apply_filters('Zprint\filterLocations', $locations, $order, $all_locations, $filter);

		$templates = array_map(function ($location) {
			/* @var $location Location */
			return $location->getData();
		}, $locations);

		return $templates;
	}

	public static function reprintOrder($order, $locations)
	{
		$order_id = ($order instanceof \WC_Order) ? $order->get_id() : $order;
		$order = wc_get_order($order);
		Log::info(Log::PRINTING, ["#$order_id", 'reprint order']);
		Printer::printOrder($order, [[LocationFilter::LOCATION, $locations, 'int_array']]);
	}

	public static function printOrder($order, $arguments)
	{
		register_shutdown_function(function ($order, $arguments) {
			do_action('Zprint\printOrder', $order, $arguments);
			$order_id = ($order instanceof \WC_Order) ? $order->get_id() : $order;
			Log::info(Log::PRINTING, ["#$order_id", 'init print']);
			if (Background::enabled()) {
				ob_start();
				$check = dechex(intval("0" . rand(1, 9) . rand(1, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9)));
				if (!file_exists(Background::getTmpDir())) {
					mkdir(Background::getTmpDir());
				}
				$file = Background::getTmpDir($check);
				file_put_contents($file, $check);

				$client = new \GuzzleHttp\Client([
					'base_uri' => home_url()
				]);
				$post['_zprint_print'] = $check;
				$post['order'] = $order_id;
				$post['arguments'] = $arguments;

				$client->requestAsync('POST', '/', [
					'form_params' => $post,
					'synchronous' => false
				])->wait();
				ob_end_clean();
			} else {
				set_time_limit(0);
				static::rawPrintOrder($order, $arguments);
			}
		}, $order, $arguments);
	}

	public static function rawPrintOrder($order, $arguments)
	{
		$order_id = ($order instanceof \WC_Order) ? $order->get_id() : $order;
		Log::info(Log::PRINTING, ["#$order_id", 'raw print']);

		if (!$order instanceof \WC_Order) {
			$order = new \WC_Order($order);
		}

		$arguments = apply_filters('Zprint\printOrderArguments', $arguments, $order);

		$filter = array_map(function ($argument) {
			$value = $argument[1];
			if ($argument[2] === 'bool') {
				$value = (bool)$value;
			} elseif ($argument[2] === 'int') {
				$value = (int)$value;
			} elseif ($argument[2] === 'int_array') {
				$value = array_map('intval', (array) $value);
			}
			return new LocationFilter($argument[0], $value);
		}, $arguments);

		$templates_data = static::getTemplates($filter, $order);

		return static::printTemplates($templates_data, $order);
	}

	public static function printTemplates($templates_data, $order)
	{
		$result = array_map(function ($template_data) use ($order) {
			return static::printDocument('Order ' . $order->id, $order, $template_data);
		}, $templates_data);

		$codes = ['status', 'error'];

		$result = array_map(function ($code) use ($result) {
			$status = array_map(function ($e) use ($code) {
				return $e[$code];
			}, $result);
			if (count(array_unique($status)) === 1) {
				return current($status);
			} else {
				return $status;
			}
		}, $codes);

		return array_combine($codes, $result);
	}

	public static function printDocument($title, $order, $template_data)
	{
		$httpClient = Client::getClientHTTP();
		$printers = $template_data['printers'];
		$contentType = Document::formatToContentType($template_data['format']);

		$multipart = [
			[
				'name' => 'title',
				'contents' => $title
			],
			[
				'name' => 'content',
				'contents' => Document::generatePrint($order, $template_data)
			],
			[
				'name' => 'contentType',
				'contents' => $contentType
			],
			[
				'name' => 'ticket',
				'contents' => Document::getTicket($template_data)
			]
		];

		$printers = array_map(function ($printer) use ($multipart, $httpClient) {
			$printer = [[
				'name' => 'printerid',
				'contents' => $printer
			]];
			$multipart = array_merge($multipart, $printer);
			$response = $httpClient->request('POST', 'https://www.google.com/cloudprint/submit', [
				'multipart' => $multipart
			]);

			$response = $response->getBody()->getContents();
			$response = json_decode($response);
			return $response;
		}, $printers);

		$printers_success = array_map(function ($response) {
			if ($response->success) {
				$job = $response->job;
				Log::info(Log::PRINTING, [$job->title, $job->status, $job->id]);
			}
			if ($response->errorCode) {
				Log::warn(Log::PRINTING, [$response->errorCode, $response->message]);
			}
			return $response->success;
		}, $printers);

		$printers_success = array_filter($printers_success);
		if (count($printers_success) === count($printers)) {
			return [
				'status' => true,
				'error' => null
			];
		} else {
			return [
				'status' => false,
				'error' => $printers
			];
		}
	}

	public static function isEnabledPrinting($type)
	{
		if (!in_array($type, [static::WEB_PRINT, static::POS_PRINT, static::ORDER_ONLY_PRINT])) {
			return false;
		}
		$allowed_print = (array)InstanceStorage::getGlobalStorage()->asCurrentStorage(function () {
			$setting_page = Page::get('printer setting');
			return $setting_page->scope(function () {
				$tab = TabPage::get('general');
				$box = Box::get('automatic order printing');
				$input = Input::get('enable automatic printing');
				return $input->getValue($box, null, $tab);
			});
		});

		return in_array($type, $allowed_print);
	}

	public static function getPrinters()
	{
		if (!Client::getToken()) {
			return [];
		}
		$httpClient = Client::getClientHTTP();
		$response = $httpClient->request('get', 'https://www.google.com/cloudprint/search');
		$response = $response->getBody()->getContents();
		$response = json_decode($response);
		$printer_list = $response->printers;

		$values = array_map(function ($printer) {
			return $printer->displayName;
		}, $printer_list);
		$key = array_map(function ($printer) {
			return $printer->id;
		}, $printer_list);

		return array_combine($key, $values);
	}
}
