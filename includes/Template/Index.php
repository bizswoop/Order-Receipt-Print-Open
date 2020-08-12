<?php
namespace Zprint\Template;

interface Index {
	public function getName();
	public function getSlug();
	public function getPath($format);
	public function getFormats();
}
