<?php

/*
	Page which performs Facebook login action
*/

class facebook_login_page
{
	private $directory;

	public function load_module($directory, $urltoroot)
	{
		$this->directory = $directory;
	}

	public function match_request($request)
	{
		return ($request == 'facebook-login');
	}

	public function process_request($request)
	{
		if ($request == 'facebook-login') {
			$app_id = qa_opt('facebook_app_id');
			$app_secret = qa_opt('facebook_app_secret');
			$tourl = qa_get('to');
			if (!strlen($tourl))
				$tourl = qa_path_absolute('');

			if (strlen($app_id) && strlen($app_secret)) {
				require_once $this->directory . 'facebook.php';

				$facebook = new Facebook(array(
					'appId' => $app_id,
					'secret' => $app_secret,
					'cookie' => true,
				));

				$fb_userid = $facebook->getUser();

				if ($fb_userid) {
					try {
						$user = $facebook->api('/me?fields=email,name,verified,location,website,about,picture.width(250)');

						if (is_array($user))
							qa_log_in_external_user('facebook', $fb_userid, array(
								'email' => @$user['email'],
								'handle' => @$user['name'],
								'confirmed' => @$user['verified'],
								'name' => @$user['name'],
								'location' => @$user['location']['name'],
								'website' => @$user['website'],
								'about' => @$user['bio'],
								'avatar' => strlen(@$user['picture']['data']['url']) ? qa_retrieve_url($user['picture']['data']['url']) : null,
							));

					} catch (FacebookApiException $e) {
					}

				} else {
					qa_redirect_raw($facebook->getLoginUrl(array('redirect_uri' => $tourl)));
				}
			}

			qa_redirect_raw($tourl);
		}
	}
}
