<?php

namespace Zprint\Admin;

class Location
{
	private static $boxes = [];

	/**
	 * @param string $name
	 * @param string $slug
	 * @param callable $render
	 * @param callable $processor
	 */
	public static function registerBox($name, $slug, $render, $processor)
	{
		self::$boxes[$slug] = [$name, $render, $processor];
	}

	public static function getBoxes()
	{
		return apply_filters('Zprint\getBoxes', self::$boxes);
	}

	/**
	 * @param \Zprint\Model\Location $location
	 * @param boolean $delete
	 */
	public static function processBoxes($location, $delete = false)
	{
		$boxes = self::getBoxes();

		foreach ($boxes as $slug => $box) {
			list(, , $processor) = $box;

			$currentOptions = $location->getBoxOption($slug);

			$options = call_user_func_array($processor, [$currentOptions, $delete, $location]);
			if($delete === false) $location->setBoxOption($slug, $options);
		}
	}

	/**
	 * @param \Zprint\Model\Location $location
	 * @param callable $renderBox
	 */
	public static function renderBoxes($location, $renderBox)
	{
		$boxes = self::getBoxes();
		ob_start();
		foreach ($boxes as $slug => $box) {
			list($name, $render) = $box;

			$currentOptions = $location->getBoxOption($slug);

			ob_start();
			call_user_func_array($render, [$currentOptions, $location]);
			$render = ob_get_clean();
			call_user_func_array($renderBox, [$name, $slug, $render]);
		}
		return ob_get_clean();
	}
}
