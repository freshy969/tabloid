<?php

/*
	Server-side response to Ajax version check requests
*/

require_once INCLUDE_DIR . 'app/admin.php';
require_once INCLUDE_DIR . 'app/users.php';

if (qa_get_logged_in_level() < USER_LEVEL_ADMIN) {
	echo "AJAX_RESPONSE\n0\n" . qa_lang_html('admin/no_privileges');
	return;
}

$uri = qa_post_text('uri');
$currentVersion = qa_post_text('version');
$isCore = qa_post_text('isCore') === "true";

if ($isCore) {
	$contents = qa_retrieve_url($uri);

	if (strlen($contents) > 0) {
		$response = qa_html($contents); // Output the current version number
	} else {
		$response = qa_lang_html('admin/version_latest_unknown');
	}
} else {
	$metadataUtil = new Q2A_Util_Metadata();
	$metadata = $metadataUtil->fetchFromUrl($uri);

	if (strlen(@$metadata['version']) > 0) {
		if (version_compare($currentVersion, $metadata['version']) < 0) {
			if (qa_qa_version_below(@$metadata['min_q2a'])) {
				$response = strtr(qa_lang_html('admin/version_requires_q2a'), array(
					'^1' => qa_html('v' . $metadata['version']),
					'^2' => qa_html($metadata['min_q2a']),
				));
			} elseif (qa_php_version_below(@$metadata['min_php'])) {
				$response = strtr(qa_lang_html('admin/version_requires_php'), array(
					'^1' => qa_html('v' . $metadata['version']),
					'^2' => qa_html($metadata['min_php']),
				));
			} else {
				$response = qa_lang_html_sub('admin/version_get_x', qa_html('v' . $metadata['version']));

				if (strlen(@$metadata['uri'])) {
					$response = '<a href="' . qa_html($metadata['uri']) . '" style="color:#d00;">' . $response . '</a>';
				}
			}
		} else {
			$response = qa_lang_html('admin/version_latest');
		}
	} else {
		$response = qa_lang_html('admin/version_latest_unknown');
	}
}

echo "AJAX_RESPONSE\n1\n" . $response;
