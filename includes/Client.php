<?php
namespace Zprint;

use Zprint\Aspect\Page;
use Zprint\Aspect\Box;
use Zprint\Aspect\InstanceStorage;

class Client
{
	public function __construct()
	{
		static::initAuth();
	}

	private static $client = null;

	/**
	 * @return \Google_Client|null
	 * @throws \Google_Exception
	 */
	public static function getClient()
	{
		if (static::$client === null) {
			$client = new \Google_Client();
			static::$client = $client;

			if ($client_secret = static::getClientSecret()) {
				$client->setRedirectUri('postmessage');
				$client->setAuthConfig($client_secret);
				$client->addScope('https://www.googleapis.com/auth/cloudprint');
				$client->setIncludeGrantedScopes(true);
				$client->setAccessType("offline");

				if ($token = static::getToken()) {
					$client->setAccessToken($token);

					if ($client->isAccessTokenExpired()) {
						$client->refreshToken(null);
						static::setToken($client->getAccessToken(), true);
					}
				}
			}
		}
		return static::$client;
	}

	public static function setToken($token, $refresh = false)
	{
		if ($token) {
			$client = static::getClient();
			if (!$client->getRefreshToken() && !$refresh) {
				if (!is_admin()) {
					wp_mail(get_option('admin_email'), 'zPrint Error', 'Please update Google Auth Info');
					return;
				}
				$client->revokeToken();
				update_option('zprint_google_token', null);

				$client->setRedirectUri(add_query_arg('zprint', 'google_auth', get_admin_url()));
				$auth_url = $client->createAuthUrl();
				header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
				exit;
			}

			$old_token = (array) static::getToken();
			$token = array_merge($old_token, $token);
			$token = maybe_serialize($token);
		}
		update_option('zprint_google_token', $token);
	}

	public static function getToken()
	{
		return maybe_unserialize(get_option('zprint_google_token', null));
	}

	public static function getClientSecret()
	{
		return InstanceStorage::getGlobalStorage()->asCurrentStorage(function () {
			$printer_setting = Page::get('printer setting');

			$value = $printer_setting->scope(function () {
				$setting = TabPage::get('setting');
				$googlePrintAccess = Box::get('google print access');
				$settingFile = Input::get('client secret');

				return $settingFile->getValue($googlePrintAccess, null, $setting);
			});
			$value = json_decode($value, true);
			return $value;
		});
	}

	public static function getClientHTTP()
	{
		$client = static::getClient();
		if (static::getToken()) {
			$client->setAccessToken(static::getToken());
			$httpClient = $client->authorize();
			return $httpClient;
		} else {
			return false;
		}
	}

	public static function initAuth()
	{
		try {
			if (is_admin() && isset($_GET['zprint']) && $_GET['zprint'] === 'google_auth' && isset($_GET['code'])) {
				$client = Client::getClient();
		
				$url = InstanceStorage::getGlobalStorage()->asCurrentStorage(function () {
					$setting = Page::get('printer setting');
		
					return $setting->scope(function (Page $setting) {
						$tab = TabPage::get('setting');
		
						return $setting->getUrl($tab);
					});
				});
		
				$data = $client->authenticate($_GET['code']);
		
				if ($data['error']) {
					ob_start(); ?>
				<h1>Google API auth error</h1>
				<p>Oops, we encountered an error: <?= $data['error_description']; ?></p>
		
				<p>
					<b>Tips to fixing the error:</b>
				<ul>
					<li>Verify Google API Account is Active</li>
					<li>Verify Google Account Credentials</li>
					<li>Verify Redirect URI</li>
					<li>Verify Client Secret</li>
				</ul>
				</p>
		
				<h2 style="text-align: center">Still Having Problems? Visit <a target="_blank" href="http://www.bizswoop.com/support/">bizswoop.com/support</a></h2>
		
				<p><a href="<?= $url; ?>">Back to setting page</a></p>
				<pre>Error code: <?= $data['error']; ?></pre>
				<?php
				wp_die(ob_get_clean(), 'Google API auth error');
					exit;
				}
		
				Client::setToken($client->getAccessToken());
		
				header('Location: ' . $url);
		
				exit;
			}
		} catch (\Exception $exception) {
			if ($exception instanceof \GuzzleHttp\Exception\ClientException) {
				$error = json_decode($exception->getResponse()->getBody()->getContents(), true);
				Log::warn(Log::BASIC, [$exception->getMessage(), $error['error']]);
				if ($error['error'] === 'invalid_grant') {
					Client::setToken(null);
				}
			} else {
				Log::warn(Log::BASIC, [$exception->getMessage()]);
			}
			require_once ABSPATH . '/wp-load.php';
			require_once ABSPATH . '/wp-includes/pluggable.php';
			\wp_mail(\get_option('admin_email'), 'Error - Print with Google Cloud Print (GCP) WooCommerce', "Site \"" . home_url() . "\" has an error with the app Print with Google Cloud Print (GCP) WooCommerce.");
			die('Error - Print with Google Cloud Print (GCP) WooCommerce. If this error will repeatedly please contact with site admin.');
		}
	}
}
