<?php

namespace Zprint;

use Zprint\Template\Index;

class Templates
{
	public function __construct()
	{
		self::registerTemplate(new \Zprint\Templates\Customer());
		self::registerTemplate(new \Zprint\Templates\Details());
		self::registerTemplate(new \Zprint\Templates\Order());
	}

	public static function registerTemplate(Template\Index $template)
	{
		add_filter('Zprint\getTemplates', function ($templates) use ($template) {
			$templates[$template->getSlug()] = $template;
			return $templates;
		});
	}

	public static function getTemplates() {
		return apply_filters('Zprint\getTemplates', []);
	}

	public static function getTemplate($slug)
	{
		$templates = self::getTemplates();
		if ($template = $templates[$slug]) {
			return $template;
		} else {
			return null;
		}
	}

	public static function getPath($template, $format)
	{
		if ($template instanceof Index) {
			$templatePath = $template->getPath($format);
		}

		$templatePath = apply_filters('Zprint\getTemplatePath', $templatePath, $template, $format);

		return $templatePath;
	}
}
