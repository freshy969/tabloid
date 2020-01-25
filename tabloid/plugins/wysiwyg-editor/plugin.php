<?php

/*
	Initiates WYSIWYG editor plugin
*/

qa_register_plugin_module('editor', 'wysiwyg-editor.php', 'wysiwyg_editor', 'WYSIWYG Editor');
qa_register_plugin_module('page', 'wysiwyg-upload.php', 'wysiwyg_upload', 'WYSIWYG Upload');

qa_register_plugin_module('page', 'wysiwyg-ajax.php', 'wysiwyg_ajax', 'WYSIWYG Editor AJAX handler');
