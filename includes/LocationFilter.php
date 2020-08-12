<?php

namespace Zprint;

class LocationFilter
{
	public $type = null;
	public $argument = null;
	const USER = 'user';
	const LOCATION = 'location';
	const WEB_ORDER = 'web_order';
	const POS_ORDER_ONLY = 'pos_order_only';

	private static $allowed_types = [self::WEB_ORDER, self::USER, self::LOCATION, self::POS_ORDER_ONLY];

	/**
	 * LocationFilter constructor.
	 * @param $type
	 * @param $argument
	 * @throws \Exception
	 */
	public function __construct($type, $argument)
	{
		if (!in_array($type, self::$allowed_types)) {
			throw new \Exception($type . ' is not correct ' . __CLASS__ . ' type');
		}

		$this->type = $type;
		$this->argument = $argument;
	}

	public function filter($locations)
	{
		$argument = $this->argument;
		$type = $this->type;
		$filter = function ($location) use ($argument, $type) {
			/* @var $location \Zprint\Model\Location */
			switch ($type) {
				case self::USER:
					{
						if (is_array($argument)) {
							return count(array_diff($argument, $location->users)) < count($argument);
						} else {
							return in_array($argument, $location->users);
						}
						break;
					}
				case
				self::WEB_ORDER:
					{
						return $location->enabledWEB === $argument;

						break;
					}
				case self::POS_ORDER_ONLY:
					{
						return $location->enabledPOS === $argument;

						break;
					}
				case self::LOCATION:
					{
						return in_array($location->getID(), $argument);
						break;
					}
				default:
					return true;
			}
		};
		$locations = array_filter($locations, $filter);

		return $locations;
	}
}
