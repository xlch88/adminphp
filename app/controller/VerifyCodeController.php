<?php
namespace App\Controller;

use App\Lib\GIFEncoder;
use App\Lib\ValidateCode;

class VerifyCodeController{
	public function get(){
		$_vc = new ValidateCode(root . 'assets/font/Mirvoshar.ttf');
		if (version_compare(PHP_VERSION, '5.6') >= 0) {
			for ($a = 0; $a < 5; $a++) {
				ob_start();
				$_vc->doimg();
				$_vc->outPut();
				$imagedata[] = ob_get_clean();
			}
			$gif = new GIFEncoder($imagedata);
			Header('Content-type:image/gif');
			echo $gif->GetAnimation();
		} else {
			$_vc->doimg();
			Header('Content-type:image/png');
			$_vc->outPut();
		}
		$_SESSION["verifyCode"] = $_vc->getCode();
	}
}