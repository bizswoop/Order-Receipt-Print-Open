<?php
namespace Zprint\Aspect;

defined('ABSPATH') or die('No script kiddies please!');

class TabPage extends Page
{
    public $page = null;

    public function attach()
    {
        $obj = func_get_args();

        $obj = $this->filterAttach($obj);

        return call_user_func_array('parent::attach', $obj);
    }

    public function attachFew(array $obj)
    {
        $obj = $this->filterAttach($obj);

        return call_user_func('parent::attachFew', $obj);
    }

    private function filterAttach($obj)
    {
        $obj = array_filter($obj, function ($el) {
            if (is_a($el, Page::class)) {
                trigger_error(static::class . ' not allow attach ' . Page::class . ' objects', E_USER_WARNING);
                return false;
            } else {
                return true;
            }
        });
        return $obj;
    }
}
