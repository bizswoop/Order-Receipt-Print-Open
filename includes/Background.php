<?php
namespace Zprint;

use Zprint\Aspect\Box;
use Zprint\Aspect\Page;
use Zprint\Aspect\InstanceStorage;

class Background
{
	public function __construct()
	{
		\add_action('wp', function () {
			static::check();
      static::backgroundCallListener();
		});
	}

  public static function getTmpDir($file = null)
	{
		$file = $file !== null ? DIRECTORY_SEPARATOR . $file : '';
		return PLUGIN_ROOT . DIRECTORY_SEPARATOR . 'tmp' . $file;
  }
  
  public static function check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_zprint_bg_init'])) {
      $check = $_POST['_zprint_bg_init'];
      if (file_get_contents(static::getTmpDir($check)) !== $check) {
        return;
      }
      unlink(static::getTmpDir($check));
      header("Connection: close\r\n");
      header("Content-Encoding: none\r\n");
      header("Content-Length: 0");
      ignore_user_abort(true);
      set_time_limit(0);

      $option_name = InstanceStorage::getGlobalStorage()->asCurrentStorage(function (){
        return Page::get('printer setting')->scope(function () {
          $general = TabPage::get('general');
          $bg_printing = Input::get('bg_printing');
	        return $general->getName($bg_printing);
        });
      });

      // ["1"] - means true
      update_option($option_name, ["1"]);
      exit;
    }
  }

	public static function init()
	{
    do_action('Zprint\backgroundPrintCheck');
    ob_start();
		$check = dechex(intval("0" . rand(1, 9) . rand(1, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9)));
		if (!file_exists(static::getTmpDir())) {
			mkdir(static::getTmpDir());
		}
		$file = static::getTmpDir($check);
		file_put_contents($file, $check);

		$client = new \GuzzleHttp\Client([
			'base_uri' => home_url()
		]);
		$post['_zprint_bg_init'] = $check;

		$client->requestAsync('POST', '/', [
			'form_params' => $post,
			'synchronous' => false
    ])->wait();
    ob_end_clean();
  }
  
  public static function enabled() {
    return InstanceStorage::getGlobalStorage()->asCurrentStorage(function (){
      return Page::get('printer setting')->scope(function () {
        $general = TabPage::get('general');
        $bg_printing = Input::get('bg_printing');
        $optimization = Box::get('optimization');
        $value = $bg_printing->getValue($optimization, null, $general);
        return $value && $value[0] === "1";
      });
    });
  }

  public static function backgroundCallListener() {
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_zprint_print'])) {
			$check = $_POST['_zprint_print'];
			if (file_get_contents(Background::getTmpDir($check)) !== $check) {
				return;
			}
			unlink(Background::getTmpDir($check));
			header("Connection: close\r\n");
			header("Content-Encoding: none\r\n");
			header("Content-Length: 0");
			ignore_user_abort(true);
			set_time_limit(0);
			$order_id = (int)$_POST['order'];
			Log::info(Log::PRINTING, ["#$order_id", 'print']);
			Printer::rawPrintOrder($order_id, $_POST['arguments']);
			exit;
		}
	}
}
