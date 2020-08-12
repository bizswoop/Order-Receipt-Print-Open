<?php
namespace Zprint;
class Input extends \Zprint\Aspect\Input
{
    const TYPE_FAKE_FILE = 'FakeFile';
    const TYPE_SMART_BUTTON = 'SmartButton';
    const TYPE_INFO = 'Info';

    public function label($post, $parent)
    {
        if (isset($this->args['label_for_disabled'])) {
            return '<label>' . $this->labels['singular_name'] . '</label>';
        }
        return parent::label($post, $parent);
    }

    public function renderInput($post, $parent) {
        if(isset($this->args['renderInput\before'])) {
					echo apply_filters('\Aspect\Input\renderInput\before', $this->args['renderInput\before'], $this, $this->args);
				}
        parent::renderInput($post, $parent);
        if(isset($this->args['renderInput\after'])) {
					echo apply_filters('\Aspect\Input\renderInput\after', $this->args['renderInput\after'], $this, $this->args);
				}
    }

    public function attributes($attrs) {
        $attrs = apply_filters('\Aspect\Input\attributes', $attrs);

        $attrs = array_map(function($value, $key) {
            if($value === null) return null;

            $key = esc_attr($key);
            $value = esc_attr($value);

            return "{$key}='{$value}'";
        }, $attrs, array_keys($attrs));

        $attrs = array_filter($attrs);

        return implode(" ", $attrs);
    }

    public function htmlFakeFile($post, $parent)
    {
        $value = $this->getValue($parent, 'attr', $post);
        $name = $this->nameInput($post, $parent);
        $id = $name;
        $attrs = compact('value', 'name', 'id');
        ?>
        <input
            class="large-text code"
            type="file"
            <?=$this->attributes($attrs); ?>
        />
        <?php
    }

    public function htmlSmartButton($post, $parent)
    {
        $name = $this->nameInput($post, $parent);
        $id = $name;

        $onclick = isset($this->args['onclick']) ? $this->args['onclick'] : null;

				$disabled = (isset($this->args['disabled']) && $this->args['disabled']) ? 'disabled' : null;
    
        $attrs = compact('disabled', 'name', 'id', 'onclick');
        ?>
        <button
            class="button"
            value="1"
            <?=$this->attributes($attrs); ?>
        >
            <?= isset($this->labels['button_name'])
                ? $this->labels['button_name']
                : $this->labels['singular_name']; 
            ?>
        </button>
        <?php
    }

    public function htmlText($post, $parent)
    {
        $name = $this->nameInput($post, $parent);
        $id = $name;
        $value = $this->getValue($parent, 'attr', $post);
        $disabled = (isset($this->args['disabled']) && $this->args['disabled']) ? 'disabled' : null;

        $attrs = compact('value', 'name', 'id', 'disabled');
        ?>
        <input
            class="large-text code"
            type="text" <?= $disabled; ?>
            <?=$this->attributes($attrs); ?>
        />
        <?php
    }

    public function htmlInfo()
    {
        echo $this->args['content'];
    }
}
