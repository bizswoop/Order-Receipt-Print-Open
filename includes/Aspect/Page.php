<?php
namespace Zprint\Aspect;

defined('ABSPATH') or die('No script kiddies please!');

class Page extends Base
{
    protected static $objects = array();
    protected $tabs = [];

    public function __construct($name)
    {
        parent::__construct($name);
        $object = $this;

        add_action('admin_menu', function () use ($object) {
            if (isset($object->args['parent_slug'])) {
                call_user_func(array($object, 'addSubMenuPage'));
            } else {
                call_user_func(array($object, 'addMenuPage'));
            }
        });

				add_filter('option_page_capability_'.self::getName($this), function ($cap){
					$cap = $this->getCapability();
					return $cap;
				});

        add_action('init', function () use ($object) {
            foreach ($object->attaches as $attach) {
                if (is_a($attach, Page::class)) {
                    /* @var $attach \Zprint\Aspect\Page */
                    $attach->setArgument('parent_slug', $object::getName($object));
                    remove_action('admin_menu', array($attach, 'addMenuPage'));
                    if (!is_a($attach, '\Zprint\Aspect\TabPage')) {
                        add_action('admin_menu', array($attach, 'addSubMenuPage'));
                    }
                    continue;
                } elseif (is_a($attach, Box::class)) {
                    /* @var $attach \Zprint\Aspect\Box */
                    $section = $attach;
                } else {
                    throw new \Exception('Incorrect input parameters');
                }
                add_action('admin_init', function () use ($section, $object) {
                    add_settings_section($object::getName($section, $object), $section->labels['singular_name'], array($section, 'descriptionBox'), $object::getName($object));
                });
                foreach ($section->attaches as $field) {
                    /* @var $field \Zprint\Aspect\Input */
                    add_action('admin_init', function () use ($field, $section, $object) {
											if (!(isset($field->args['disabled']) && $field->args['disabled'])) {
                            register_setting($object::getName($object), $field->nameInput($object, $section), function ($data) use ($field, $object, $section) {
                                list($data, $key) = $field->processingData($object, $section);
                                return $data;
                            });
                        }

                        $page = $object::getName($object);
                        if (is_a($object, '\Zprint\Aspect\TabPage')) {
                            /* @var $object \Zprint\Aspect\TabPage */
                            $object::getName($object->page);
                        }
                        add_settings_field($field->nameInput($object, $section), $field->label($object, $section), array($field, 'render'), $page, $object::getName($section, $object), array($object, $section));
                    });
                }
            }
        });
    }

    protected function hasTabs()
    {
        return count($this->tabs) > 0;
    }

    protected function currentTab($checkTab = null)
    {
        $current_tab_slug = isset($_GET['tab']) ? $_GET['tab'] : null;
        $tab = current($this->tabs);
        if (isset($this->tabs[$current_tab_slug])) {
            $tab = $this->tabs[$current_tab_slug];
        }

        if ($checkTab === null) {
            return $tab;
        } else {
            return $tab === $checkTab;
        }
    }

    protected function renderCurrentTab()
    {
        $tab = $this->currentTab();
        $tab->renderPageForm();
    }

    public function getUrl(TabPage $withTab = null)
    {
        $page = add_query_arg('page', self::getName($this), admin_url('admin.php'));

        if ($withTab !== null) {
            $page = add_query_arg('tab', $withTab->name, $page);
        }

        return $page;
    }

    public function isRequested(TabPage $withTab = null)
    {
        $current_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $admin_page = parse_url($this->getUrl($withTab));
        $current = parse_url($current_url);

        if($current === false || $admin_page === false) return false;

        $diff = array_diff($admin_page, $current);

        if (isset($diff['path'])) return false;

        $admin_page_query = parse_url($this->getUrl($withTab), PHP_URL_QUERY);
        if ($withTab !== null) {
            $current_url = add_query_arg('tab', $this->currentTab()->name, $current_url);
        }
        $current_query = parse_url($current_url, PHP_URL_QUERY);

        parse_str($admin_page_query, $admin_page_query);
        parse_str($current_query, $current_query);

        $diff = array_diff($admin_page_query, $current_query);

        return count($diff) === 0;
    }

    protected function renderTabSelector()
    {
        $tabs = $this->tabs;
        if (!$this->hasTabs()) return;
        ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab) {
                $classes = ['nav-tab'];
                if ($this->currentTab($tab)) $classes[] = 'nav-tab-active';
                ?>
                <a href="<?= $this->getUrl($tab); ?>"
                   class="<?= implode(' ', $classes); ?>"><?= $tab->labels['singular_name']; ?></a>
            <?php }
            if(isset($this->args['new_link']) && $this->args['new_link']) echo $this->args['new_link'];
            ?>
        </h2>
    <?php }

    public function getCapability() {
				$capability = 'manage_options';
				return isset($this->args['capability']) ? $this->args['capability'] : $capability;
		}

    public function addMenuPage()
    {
        add_menu_page($this->labels['singular_name'], $this->labels['singular_name'], $this->getCapability(), self::getName($this), array($this, 'renderPage'));
    }

    public function addSubMenuPage()
    {
        add_submenu_page($this->args['parent_slug'], $this->labels['singular_name'], $this->labels['singular_name'], $this->getCapability(), self::getName($this), array($this, 'renderPage'));
    }

    public function renderPage()
    {
    	?>
        <div class="wrap">
            <h2><?= get_admin_page_title() ?></h2>

            <?php settings_errors(); ?>

            <?php $this->renderTabSelector(); ?>

            <?php if ($this->hasTabs()) {
                $this->renderCurrentTab();
            } else {
                $this->renderPageForm();
            } ?>
        </div>
    <?php }

    protected function renderPageForm()
    {
        ?>
        <form action="options.php" method="POST" enctype="multipart/form-data">
            <?php
            settings_fields(self::getName($this));
            do_settings_sections(self::getName($this));
            submit_button();
            ?>
        </form>
    <?php }

    public function attach()
    {
        $object = $this;
        $obj = func_get_args();
        $obj = array_map(function ($el) use ($object) {
            if (is_a($el, '\Zprint\Aspect\TabPage')) {
                $el->page = $object;
                $object->tabs[$el->name] = $el;
            }
            return $el;
        }, $obj);

        return call_user_func_array('parent::attach', $obj);
    }

    public function attachFew(array $obj)
    {
        $object = $this;
        $obj = array_map(function ($el) use ($object) {
            if (is_a($el, '\Zprint\Aspect\TabPage')) {
                $el->page = $object;
                $object->tabs[$el->name] = $el;
            }
            return $el;
        }, $obj);

        return call_user_func('parent::attachFew', $obj);
    }
}
