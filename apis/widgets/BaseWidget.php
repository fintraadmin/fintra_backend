<?php


class BaseWidget{
	var $type ;
	var $title ;
	var $subtitle;
	var $cards;
	var $featured_image;
	var $featured_image_txt;

	abstract function set($data);
	abstract function get();
}

?>
