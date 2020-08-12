<?php

namespace Zprint\Model;

use Zprint\DB;
use Zprint\Client;
use Zprint\Exception\DB as DBException;
use Zprint\Template\Index;
use Zprint\Templates;

class Location
{
	private $id;
	public $title;
	public $enabledWEB;
	public $enabledPOS;
	public $template;
	public $size;
	public $margins = null;
	/* shipping template settings */
	public $shipping = [
		'cost' => true,
		'customer_details' => true,
		'billing_shipping_details' => true,
		'method' => true,
		'delivery_pickup_type' => false
	];
	/* total template settings */
	public $total = [
		'cost' => true
	];
	public $appearance = [
		'logo' => null,
		'Check Header' => '',
		'Company Name' => '',
		'Company Info' => '',
		'Order Details Header' => '',
		'Footer Information #1' => '',
		'Footer Information #2' => '',
	];
	public $height = null;
	public $width = null;
	public $orientation = '2';
	public $format = 'html';
	public $symbolsWidth = 40;
	public $printSymbolsDebug = false;
	public $printers = [];
	public $font = [
		'basicWeight' => 400,
		'basicSize' => 11,
		'headerWeight' => 700,
		'headerSize' => 16
	];
	public $users = [];

	protected $options = [
		'box' => []
	];

	protected $created_at = null;
	protected $created_at_gmt = null;
	protected $updated_at = null;
	protected $updated_at_gmt = null;

	public function __construct($data = null)
	{
		if ($id = filter_var($data, FILTER_VALIDATE_INT)) {
			global $wpdb;
			$prefix = $wpdb->prefix . DB::Prefix;

			$locations = $prefix . DB::Locations;

			$data = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM ${locations} as l WHERE l.id = %d",
					$id
				)
			);

			if ($data === null) {
				throw new DBException('Location not found', DBException::NOT_FOUND);
			}
		}

		if (is_object($data)) {
			$data = apply_filters('Zprint\getLocationDatabaseData', $data);
			$this->id = intval($data->id);
			$this->title = strval($data->title);
			$this->enabledWEB = boolval($data->web_order);
			$this->enabledPOS = boolval($data->pos_order_only);

			// merge with defaults
			$options = array_merge($this->options, maybe_unserialize($data->options));
			$options = apply_filters('Zprint\getLocationOptions', $options);
			$this->options = $options;

			$appearanceDefault = $this->appearance;
			$appearance = isset($options['appearance']) ? $options['appearance'] : [];
			$this->appearance = array_merge($appearanceDefault, $appearance);

			$this->template = isset($options['template']) ? $options['template'] : null;
			$this->size = isset($options['size']) ? $options['size'] : null;
			$this->margins = isset($options['margins']) ? $options['margins'] : null;
			if (isset($options['shipping'])) {
				$this->shipping = array_merge($this->shipping, $options['shipping']);
				$this->shipping['delivery_pickup_type'] = $this->shipping['delivery_pickup_type'] && defined('\ZZHoursDelivery\ACTIVE');
			}
			$this->total = array_merge($this->total, $options['total']);

			$this->height = isset($options['height']) ? $options['height'] : null;
			$this->width = isset($options['width']) ? $options['width'] : null;
			$this->format = isset($options['format']) ? $options['format'] : 'html';
			$this->symbolsWidth = isset($options['symbolsWidth']) ? $options['symbolsWidth'] : $this->symbolsWidth;
			$this->printSymbolsDebug = isset($options['printSymbolsDebug']) ? $options['printSymbolsDebug'] : $this->printSymbolsDebug;
			$this->orientation = isset($options['orientation']) ? $options['orientation'] : $this->orientation;
			$this->font = isset($options['font']) ? (array)$options['font'] : $this->font;

			$this->printers = $data->printers ? explode("|", $data->printers) : [];
			$this->users = $data->users ? array_map('intval', explode("|", $data->users)) : [];

			$this->created_at = strtotime($data->created_at);
			$this->created_at_gmt = strtotime($data->created_at_gmt);
			$this->updated_at = strtotime($data->updated_at);
			$this->updated_at_gmt = strtotime($data->updated_at_gmt);

			do_action('Zprint\initLocationData', $this, $data);
		}
	}

	public function delete()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$locations = $prefix . DB::Locations;

		if ($this->id) {
			$wpdb->delete($locations, ['id' => $this->id], ['%d']);
		}

		do_action('Zprint\removeLocation', $this->id);

		$this->id = null;

		return null;
	}

	public function save()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$locations = $prefix . DB::Locations;

		$template = $this->template;

		if ($this->margins === null) {
			$margins = null;
		} else {
			$margins = array_slice($this->margins, 0, 4);
			$margins = array_map('intval', $margins);
			$margins = array_replace(array_fill(0, 4, 0), $margins);
		}

		$options = array_merge($this->options, [
			'template' => $template,
			'size' => $this->size,
			'margins' => $margins,
			'shipping' => $this->shipping,
			'total' => $this->total,
			'height' => $this->height,
			'width' => $this->width,
			'format' => $this->format,
			'symbolsWidth' => $this->symbolsWidth,
			'printSymbolsDebug' => $this->printSymbolsDebug,
			'orientation' => $this->orientation,
			'font' => $this->font,
			'appearance' => $this->appearance
		]);

		$options = apply_filters('Zprint\setLocationOptions', $options);

		$base_data = [
			'title' => $this->title,
			'web_order' => $this->enabledWEB,
			'pos_order_only' => $this->enabledPOS,
			'options' => maybe_serialize($options),
			'printers' => implode("|", $this->printers),
			'users' => implode("|", $this->users),
			'updated_at' => current_time('mysql'),
			'updated_at_gmt' => current_time('mysql', 1),
		];
		$base_data_where = ['%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s'];

		$base_data = apply_filters('Zprint\setLocationDatabaseData', $base_data);

		if ($this->id) {
			$wpdb->update(
				$locations,
				$base_data,
				['id' => $this->id],
				$base_data_where,
				['%d']
			);
		} else {
			$wpdb->insert(
				$locations,
				array_merge(
					$base_data,
					[
						'created_at' => current_time('mysql'),
						'created_at_gmt' => current_time('mysql', 1),
					]
				),
				array_merge(
					$base_data_where,
					['%s', '%s']
				)
			);

			$this->id = $wpdb->insert_id;
		}
	}

	public function getID()
	{
		return $this->id;
	}

	public function getData()
	{
		$data = [
			'id' => $this->getID(),
			'title' => $this->title,
			'web_order' => $this->enabledWEB,
			'pos_order_only' => $this->enabledPOS,
			'template' => $this->template,
			'symbolsWidth' => $this->symbolsWidth,
			'printSymbolsDebug' => $this->printSymbolsDebug,
			'orientation' => $this->orientation,
			'format' => $this->format,
			'size' => $this->getSize(),
			'font' => $this->font,
			'margins' => $this->margins,
			'printers' => $this->printers,
			'users' => $this->users,
			'shipping' => $this->shipping,
			'total' => $this->total,
			'options' => $this->options,
			'appearance' => $this->appearance
		];

		return apply_filters('Zprint\getLocationDataForTemplate', $data);
	}

	public static function getAll()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Locations;

		$data = $wpdb->get_results(
			"SELECT * FROM ${table}"
		);

		return array_map(function ($el) {
			return new self($el);
		}, $data);
	}

	public function getSize()
	{
		switch ($this->size) {
			case 'a4':
				return [
					'name' => $this->size,
					'width' => 210,
					'height' => 297
				];
			case 'letter':
				return [
					'name' => $this->size,
					'width' => 215.9,
					'height' => 279.4
				];
			case 'custom':
			default:
				return [
					'name' => 'custom',
					'width' => $this->width,
					'height' => $this->height
				];
		}
	}

	public static function getSizes()
	{
		return [
			'a4' => __('A4', 'Print-Google-Cloud-Print-GCP-WooCommerce'),
			'letter' => __('Letter', 'Print-Google-Cloud-Print-GCP-WooCommerce'),
			'custom' => __('Custom', 'Print-Google-Cloud-Print-GCP-WooCommerce')
		];
	}

	public static function getTemplates()
	{
		$templates = Templates::getTemplates();
		return array_map(function ($template) {
			if ($template instanceof Index) {
				return $template->getName();
			} else {
				return $template;
			}
		}, $templates);
	}

	public static function getFormats()
	{
		$basic = [
			'html' => __('HTML', 'Print-Google-Cloud-Print-GCP-WooCommerce'),
			'plain' => __('Plain Text', 'Print-Google-Cloud-Print-GCP-WooCommerce'),
		];

		$templates = apply_filters('Zprint\getTemplates', []);
		$formats = array_reduce($templates, function ($acc, $template) use ($basic) {
			if ($template instanceof Index) {
				return array_merge($acc, array_filter($template->getFormats()));
			} else {
				return array_merge($basic, $acc);
			}
		}, []);

		return array_combine(array_keys($formats), array_map(function ($value, $name) use ($basic) {
			if (array_key_exists($name, $basic)) {
				return $basic[$name];
			} else {
				return $value;
			}
		}, $formats, array_keys($formats)));
	}

	public static function getOrientations()
	{
		return [
			'0' => __('Portrait', 'Print-Google-Cloud-Print-GCP-WooCommerce'),
			'1' => __('Landscape', 'Print-Google-Cloud-Print-GCP-WooCommerce'),
			'2' => __('Auto', 'Print-Google-Cloud-Print-GCP-WooCommerce')
		];
	}

	public static function validateSize($size)
	{
		return in_array($size, array_keys(static::getSizes()));
	}

	public static function validateTemplate($template)
	{
		return in_array($template, array_keys(static::getTemplates()));
	}

	public static function validateFormat($format)
	{
		return in_array($format, array_keys(static::getFormats()));
	}

	public static function validateOrientation($orientation)
	{
		return in_array($orientation, array_keys(static::getOrientations()));
	}

	public static function getAllFormatted()
	{
		if (!Client::getToken()) {
			return false;
		}

		$locations = Location::getAll();

		if (empty($locations)) {
			return [];
		}

		$locations_keys = array_map(function (Location $location) {
			return $location->getID();
		}, $locations);

		$locations = array_map(function (Location $location, $id) {
			return $location->getData();
		}, $locations, $locations_keys);

		$locations = array_combine($locations_keys, $locations);

		return $locations;
	}

	public function getTemplateOption()
	{
		return isset($this->options['templateOptions'][$this->template]) ? $this->options['templateOptions'][$this->template] : [];
	}

	public function setTemplateOption($value)
	{
		if (!isset($this->options['templateOptions'])) $this->options['templateOptions'] = [];
		$this->options['templateOptions'][$this->template] = $value;
	}

	public function getBoxOption($slug)
	{
		return $this->options['box'][$slug];
	}

	public function setBoxOption($slug, $value)
	{
		$this->options['box'][$slug] = $value;
	}

	public static function getCurrent() {
		global $zprint_location_id;
		return new static($zprint_location_id);
	}
}
