<?php
if (!function_exists('exif_imagetype')) {
	function exif_imagetype($currFile) {
		list($width, $height, $type2, $attr) = getimagesize($currFile);
		if ($type2 !== false){
			return $type2;
		}
		return false;
	}
}
function db(){
	global $db;
	return $db;
}