<?php
namespace Zprint\Template;

interface Options {
	public function renderOptions($options);
	public function processOptions($options);
}
