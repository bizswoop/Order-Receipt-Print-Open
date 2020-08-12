<?php

namespace Zprint\Template;

use const Zprint\PLUGIN_ROOT;

abstract class Basic implements Index
{
	public function getPath($format)
	{
		$templateName = [$this->getSlug(), $format];
		$templateName = array_filter($templateName);
		$templateName = implode('-', $templateName);

		return PLUGIN_ROOT . '/templates/' . $templateName . '.php';
	}

	public function getFormats()
	{
		return ['html' => true, 'plain' => true];
	}
}
