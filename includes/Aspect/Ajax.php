<?php
namespace Zprint\Aspect;

defined('ABSPATH') or die('No script kiddies please!');

class Ajax
{
    public static function add_action($tag, $callback)
    {
        add_action('wp_ajax_nopriv_'.$tag, $callback);
        add_action('wp_ajax_'.$tag, $callback);
    }
}
