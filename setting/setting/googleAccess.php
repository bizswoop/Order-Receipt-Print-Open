<?php

namespace Zprint;

use Zprint\Aspect\Box, Zprint\Aspect\Page;

return function (TabPage $setting, Page $setting_page) {
	$googlePrintAccess = new Box('google print access');
	$googlePrintAccess
		->setLabel('singular_name', __('Google Print Access', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($setting);

	$setup = new Input('Setup Google API Credentials');
	$setup
		->setLabel('singular_name', __('Setup Google API Credentials', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setLabel('button_name', __('Setup', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($googlePrintAccess)
		->setArgument('onclick', "window.open('https://console.developers.google.com/apis/', '_blank').focus(); return false;")
		->setType(Input::TYPE_SMART_BUTTON)
		->setArgument('label_for_disabled');

	$redirect = new Input('Redirect URI');
	$redirect
		->setLabel('singular_name', __('Redirect URI', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setLabel('button_name', __('Copy', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($googlePrintAccess)
		->setArgument('renderInput\after', '<input class="code regular-text" type="text" id="copy_auth" disabled value="' . add_query_arg('zprint', 'google_auth', admin_url()) . '">')
		->setArgument('onclick', "var copy = document.getElementById('copy_auth'); copy.disabled = false; copy.focus(); copy.setSelectionRange(0, copy.value.length); document.execCommand('Copy'); copy.setSelectionRange(0, 0); copy.blur(); copy.disabled = true; alert('Copied to the Clipboard: ' + copy.value); return false;")
		->setType(Input::TYPE_SMART_BUTTON)
		->setArgument('label_for_disabled');

	$settingFile = new Input('client secret');
	$settingFile
		->setLabel('singular_name', __('Client Secret', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->attachTo($googlePrintAccess);

	$reset = new Input('reset');
	$reset
		->setLabel('singular_name', __('Reset', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setArgument('onclick', "return confirm('" . __('Are you sure?', 'Print-Google-Cloud-Print-GCP-WooCommerce') . "')")
		->setArgument('label_for_disabled')
		->attachTo($googlePrintAccess)
		->setType(Input::TYPE_SMART_BUTTON);

	$logout = new Input('logout');
	$logout
		->setLabel('singular_name', __('Logout', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setArgument('onclick', "return confirm('" . __('Are you sure?', 'Print-Google-Cloud-Print-GCP-WooCommerce') . "')")
		->setType(Input::TYPE_SMART_BUTTON)
		->setArgument('label_for_disabled');

	$login = new Input('login');
	$login
		->setLabel('singular_name', __('Login', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setArgument('description', __('Please provide access to Google Cloud Print.', 'Print-Google-Cloud-Print-GCP-WooCommerce'))
		->setArgument('label_for_disabled')
		->setType(Input::TYPE_SMART_BUTTON)
		->attachTo($googlePrintAccess);

	if ($settingFile->getValue($googlePrintAccess, null, $setting_page)) {
		$settingFile
			->setArgument('disabled')
			->setType(Input::TYPE_TEXT);

		if (Client::getToken()) {
			$logout->attachTo($googlePrintAccess);
			$setup->detachFrom($googlePrintAccess);
			$redirect->detachFrom($googlePrintAccess);
			$login->detachFrom($googlePrintAccess);

		}
	} else {
		$reset->setArgument('disabled');
		$login->setArgument('disabled');
		$settingFile->setType(Input::TYPE_FAKE_FILE);
	}

	add_filter('\Zprint\Aspect\Input\saveBefore', function ($data, $object, $key_name) use ($setting, $googlePrintAccess, $settingFile, $reset, $login, $logout) {
		$client = Client::getClient();
		if ($object === $settingFile) {
			$data = file_get_contents($_FILES[$key_name]['tmp_name']);
		}
		if ($object === $reset && $data) {
			$client->revokeToken();
			update_option($settingFile->nameInput($setting, $googlePrintAccess), null);
			Client::setToken(null);
		}
		if ($object === $logout && $data) {
			$client->revokeToken();
			Client::setToken(null);
		}
		if ($object === $login && $data) {
			$client->setRedirectUri(add_query_arg('zprint', 'google_auth', get_admin_url()));
			$auth_url = $client->createAuthUrl();
			header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
			exit;
		}

		return $data;
	}, 10, 3);
};
