<?php

/*
	Initiates Facebook login plugin
*/


// login modules don't work with external user integration
if (!FINAL_EXTERNAL_USERS) {
	qa_register_plugin_module('login', 'facebook-login.php', 'facebook_login', 'Facebook Login');
	qa_register_plugin_module('page', 'facebook-login-page.php', 'facebook_login_page', 'Facebook Login Page');
	qa_register_plugin_layer('facebook-layer.php', 'Facebook Login Layer');
}
