<?php

namespace Zprint;

use \Zprint\Aspect\InstanceStorage;
use \Zprint\Aspect\Page;
use \Zprint\Aspect\Box;

abstract class DocumentBase
{
	public static function getTicket($template_data)
	{
		$print = [];

		$ticket = [
			"version" => "1.0",
			"print" => &$print
		];

		$copies = InstanceStorage::getGlobalStorage()->asCurrentStorage(function () {
			$printer_setting = Page::get('printer setting');
			return $printer_setting->scope(function () {
				$general = TabPage::get('general');
				$aop = Box::get('automatic order printing');
				$copies = Input::get('copies');
				return $copies->getValue($aop, null, $general);
			});
		});

		if (!filter_var($copies, FILTER_VALIDATE_INT) || $copies <= 0) {
			$copies = 1;
		}

		$print['copies'] = ['copies' => $copies];
		$margins = $template_data['margins'];

		if($margins !== null) {
			$print['margins'] = [
				'top_microns' => $margins[0] * 1000,
				'right_microns' => $margins[1] * 1000,
				'bottom_microns' => $margins[2] * 1000,
				'left_microns' => $margins[3] * 1000
			];
		}
		$print['page_orientation'] = [
			'type' => +$template_data['orientation']
		];

		$size = $template_data['size'];
		if (isset($size['width']) && $size['width'] > 0) {
			$media_size = [
				"width_microns" => $size['width'] * 1000
			];

			if (isset($size['height']) && $size['height'] > 0) {
				$media_size['height_microns'] = $size['height'] * 1000;
			} else {
				$media_size['is_continuous_feed'] = true;
			}

			$print['media_size'] = $media_size;
		}

		$ticket = json_encode($ticket);

		return $ticket;
	}

	public static function generatePrint($order, $location_data)
	{
		global $zprint_appearance, $zprint_location_id;
		$zprint_location_id = $location_data['id'];

		if (!$order instanceof \WC_Order) {
			$order = wc_get_order($order);
		}

		$format = $location_data['format'];
		$template_name = $location_data['template'];
		$template = Templates::getTemplate($template_name);

		$symbolsWidth = $location_data['symbolsWidth'];
		$symbolsSeparator = $location_data['printSymbolsDebug'] ? '.' : ' ';

		$fontSize = $location_data['font']['basicSize'];
		$fontWeight = $location_data['font']['basicWeight'];
		$headerSize = $location_data['font']['headerSize'];
		$headerWeight = $location_data['font']['headerWeight'];

		$templateOptions = isset($location_data['options']['templateOptions'][$template_name])
			? $location_data['options']['templateOptions'][$template_name]
			: [];

		$zprint_appearance = $location_data['appearance'];

		$templatePath = Templates::getPath($template, $format);

		if (!file_exists($templatePath)) return null;

		if($format === 'plain') {
			Document::setLineLength($symbolsWidth);
			Document::setSymbolsSeparator($symbolsSeparator);
		}

		do_action('Zprint\startGeneratePrint');
		ob_start();
		include $templatePath;
		$content = ob_get_contents();
		$content .= static::brandingMessage($format);
		switch ($format) {
			case 'plain':
				$content = str_replace("\t", '', $content);
				break;
			case 'html':
			default:
				$content = str_replace(["\n", "\t"], [' ', ''], $content);
		}
		ob_end_clean();
		do_action('Zprint\endGeneratePrint');

		$zprint_appearance = null;
		$zprint_location_id = null;

		return $content;
	}

	public static function brandingMessage($format)
	{
		ob_start();
		switch ($format) {
			case 'plain':
				{
					echo Document::emptyLine();
					echo Document::centerLine('Powered by BizSwoop');
					echo Document::centerLine('www.bizswoop.com/print');
					break;
				}
			case 'html':
			default:
				{
					?>
					<br/>
					<div style="text-align: center; font-size: 12px;">Powered by BizSwoop</div>
					<div style="text-align: center; font-size: 11px;">www.bizswoop.com/print</div>
					<?php
					break;
				}
		}

		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	public static function brandingStatus() {
		return true;
	}

	public static function symbolsAlign($left, $right, $setLengths = null)
	{
		$left = self::formatString($left);
		$right = self::formatString($right);


		global $zprint_lengths, $zprint_symbols_separator;
		$str_length = ($setLengths === null) ? $zprint_lengths : $setLengths;

		$left_length = mb_strlen($left);
		$right_length = mb_strlen($right);

		if ($left_length + $right_length > $str_length) {
			if ($left_length > $str_length) {
				$lines = self::strSplit($left, $str_length);
				$last_line = array_pop($lines);
				$content = '';
				foreach ($lines as $line) $content .= self::line($line);
				$content .= self::symbolsAlign($last_line, $right, $setLengths);
				return $content;
			}

			if ($right_length > $str_length) {
				$lines = self::strSplit($right, $str_length);
				$first_line = array_shift($lines);
				$content = '';
				$content .= self::symbolsAlign($left, $first_line, $setLengths);
				foreach ($lines as $line) $content .= self::line($line);
				return $content;
			}

			$content = '';
			$content .= self::symbolsAlign($left, null, $setLengths);
			$content .= self::symbolsAlign(null, $right, $setLengths);
			return $content;

		} else {
			$separator_string = str_repeat($zprint_symbols_separator, $str_length - ($left_length + $right_length));
			return self::line($left . $separator_string . $right);
		}
	}

	public static function setLineLength($lineLength)
	{
		global $zprint_lengths;
		$zprint_lengths = $lineLength;
	}

	public static function setSymbolsSeparator($symbolsSeparator) {
		global $zprint_symbols_separator;
		$zprint_symbols_separator = $symbolsSeparator;
	}

	public static function line($content, $setLengths = null)
	{
		$content = self::formatString($content);

		global $zprint_lengths, $zprint_symbols_separator;
		$str_length = ($setLengths === null) ? $zprint_lengths : $setLengths;
		$content_length = mb_strlen($content);

		if ($content_length === 0) {
			return null;
		} elseif ($content_length > $str_length) {
			$lines = self::strSplit($content, $str_length);
			$content = '';
			foreach ($lines as $line) $content .= self::line($line);
			return $content;
		} else {
			return self::strPad($content, $str_length, $zprint_symbols_separator, STR_PAD_RIGHT) . self::emptyLine();
		}
	}

	public static function emptyLine()
	{
		return PHP_EOL;
	}

	public static function centerLine($content, $setLengths = null)
	{
		$content = self::formatString($content);

		global $zprint_lengths, $zprint_symbols_separator;
		$str_length = ($setLengths === null) ? $zprint_lengths : $setLengths;
		$content_length = mb_strlen($content);
		if ($content_length === 0) {
			return null;
		} elseif ($content_length > $str_length) {
			return self::line($content, $str_length);
		} else {
			return self::line(self::strPad($content, $str_length, $zprint_symbols_separator, STR_PAD_BOTH), $str_length);
		}
	}

	public static function formatString($string)
	{
		return html_entity_decode(strip_tags($string));
	}

	public static function strSplit($str, $l = 0)
	{
		if ($l > 0) {
			$ret = array();
			$len = mb_strlen($str, "UTF-8");
			for ($i = 0; $i < $len; $i += $l) {
				$ret[] = mb_substr($str, $i, $l, "UTF-8");
			}
			return $ret;
		}
		return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
	}

	public static function strPad($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT)
	{
		$str_len = mb_strlen($str);
		$pad_str_len = mb_strlen($pad_str);
		if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
			$str_len = 1; // @debug
		}
		if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
			return $str;
		}

		$result = null;
		$repeat = ceil($str_len - $pad_str_len + $pad_len);
		if ($dir == STR_PAD_RIGHT) {
			$result = $str . str_repeat($pad_str, $repeat);
			$result = mb_substr($result, 0, $pad_len);
		} else if ($dir == STR_PAD_LEFT) {
			$result = str_repeat($pad_str, $repeat) . $str;
			$result = mb_substr($result, -$pad_len);
		} else if ($dir == STR_PAD_BOTH) {
			$length = ($pad_len - $str_len) / 2;
			$repeat = ceil($length / $pad_str_len);
			$result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length))
				. $str
				. mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
		}

		return $result;
	}

	public static function formatToContentType($format)
	{
		switch ($format) {
			case 'plain':
				return 'text/plain';
			case 'html':
			default:
				return 'text/html';
		}
	}
}
