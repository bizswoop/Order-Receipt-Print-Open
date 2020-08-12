<?php 
namespace Zprint;
class TabPage extends \Zprint\Aspect\TabPage
{
  protected function renderPageForm() {
    if(isset($this->args['contentPage']) && $this->args['contentPage']) {
      if(is_callable($this->args['contentPage'])) {
        echo call_user_func($this->args['contentPage'], $this);
      } else {
        echo $this->args['contentPage'];
      }
    }
    
    if(!(isset($this->args['hideForm']) && $this->args['hideForm'])) parent::renderPageForm();
  }
}
