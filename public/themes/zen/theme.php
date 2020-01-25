<?php

/*
	Zen Theme for Tabloid
*/

class html_theme extends html_theme_base
{
	protected $theme = 'zen';

	// use local font files instead of Google Fonts
    private $localfonts = false;

	// theme subdirectories
	private $js_dir = 'js';
	private $icon_url = 'images/icons';
	private $theme_dir = '/themes/zen/'; 

	private $fixed_topbar = false;
	private $welcome_widget_class = 'wet-asphalt';
	private $ask_search_box_class = 'turquoise';
	// Size of the user avatar in the navigation bar
	private $nav_bar_avatar_size = 52;

	// use new block layout in rankings
	protected $ranking_block_layout = true;

	// Adding aditional meta for responsive design	 
	public function head_metas()
	{
		$this->output('<meta name="viewport" content="width=device-width, initial-scale=1"/>');
		parent::head_metas();
	}
	
	// Adding theme stylesheets
	public function head_css()
	{
		// add RTL CSS file
		if ($this->isRTL)
			//$this->content['css_src'][] = $this->rooturl . 'styles-rtl.css';
			$this->content['css_src'][] = $this->themedir . 'styles-rtl.css';
/*
		if ($this->localfonts) {
			// add Ubuntu font locally (inlined for speed)
			$this->output_array(array(
				'<style>',
				'@font-face {',
				' font-family: "Ubuntu"; font-style: normal; font-weight: 400;',
				' src: local("Ubuntu"), url("' . $this->themedir . 'fonts/Ubuntu-regular.woff") format("woff");',
				'}',
				'@font-face {',
				' font-family: "Ubuntu"; font-style: normal; font-weight: 700;',
				' src: local("Ubuntu Bold"), local("Ubuntu-Bold"), url("' . $this->themedir . 'fonts/Ubuntu-700.woff") format("woff");',
				'}',
				'@font-face {',
				' font-family: "Ubuntu"; font-style: italic; font-weight: 400;',
				' src: local("Ubuntu Italic"), local("Ubuntu-Italic"), url("' . $this->themedir . 'fonts/Ubuntu-italic.woff") format("woff");',
				'}',
				'@font-face {',
				' font-family: "Ubuntu"; font-style: italic; font-weight: 700;',
				' src: local("Ubuntu Bold Italic"), local("Ubuntu-BoldItalic"), url("' . $this->themedir . 'fonts/Ubuntu-700italic.woff") format("woff");',
				'}',
				'</style>',
			));
		}
		else {
*/			
            $this->content['css_src'][] = 'https://fonts.googleapis.com/css?family=Arsenal:400,400i,700,700i&amp;subset=cyrillic" rel="stylesheet';
//		}

        $this->content['css_src'][] = $this->theme_dir . COLOR_SCHEME . '.css';

		parent::head_css();

		// output some dynamic CSS inline
		$this->head_inline_css();
	}
	
	// Adding theme javascripts
	public function head_script()
	{		
		$jsUrl = $this->theme_dir . 'js/core.js';
		$this->content['script'][] = '<script src="' . $jsUrl . '"></script>';

		parent::head_script();
	}
	
	// Adding point count for logged in user	
	public function logged_in()
	{
		parent::logged_in();
		if (qa_is_logged_in()) {
			$userpoints = qa_get_logged_in_points();
			$pointshtml = $userpoints == 1
				? qa_lang_html_sub('main/1_point', '1', '1')
				: qa_html(qa_format_number($userpoints))
			;
			$this->output('<div class="qam-logged-in-points">' . $pointshtml . '</div>');
		}
	}
	
	// Adding body class dynamically. Override needed to add class on admin/approve-users page	 
	public function body_tags()
	{
		$class = 'qa-template-' . qa_html($this->template);
		$class .= empty($this->theme) ? '' : ' qa-theme-' . qa_html($this->theme);

		if (isset($this->content['categoryids'])) {
			foreach ($this->content['categoryids'] as $categoryid) {
				$class .= ' qa-category-' . qa_html($categoryid);
			}
		}

		// add class if admin/approve-users page
		if ($this->template === 'admin' && qa_request_part(1) === 'approve')
			$class .= ' qam-approve-users';

		if ($this->fixed_topbar)
			$class .= ' qam-body-fixed';

		$this->output('class="' . $class . ' qa-body-js-off"');
	}
	
	// Login form for user dropdown menu.
	public function nav_user_search()
	{
		// outputs login form if user not logged in
		$this->output('<div class="qam-account-items-wrapper">');

		$this->qam_user_account();

		$this->output('<div class="qam-account-items clearfix">');

		if (!qa_is_logged_in()) {
			if (isset($this->content['navigation']['user']['login']) && !FINAL_EXTERNAL_USERS) {
				$login = $this->content['navigation']['user']['login'];
				$this->output(
					'<form action="' . $login['url'] . '" method="post">',
						'<input type="text" name="emailhandle" dir="auto" placeholder="' . trim(qa_lang_html(qa_opt('allow_login_email_only') ? 'users/email_label' : 'users/email_handle_label'), ':') . '"/>',
						'<input type="password" name="password" dir="auto" placeholder="' . trim(qa_lang_html('users/password_label'), ':') . '"/>',
						'<div><input type="checkbox" name="remember" id="qam-rememberme" value="1"/>',
						'<label for="qam-rememberme">' . qa_lang_html('users/remember') . '</label></div>',
						'<input type="hidden" name="code" value="' . qa_html(qa_get_form_security_code('login')) . '"/>',
						'<input type="submit" value="' . $login['label'] . '" class="qa-form-tall-button qa-form-tall-button-login" name="dologin"/>',
					'</form>'
				);

				// remove regular navigation link to log in page
				unset($this->content['navigation']['user']['login']);
			}
		}

		$this->nav('user');
		$this->output('</div> <!-- END qam-account-items -->');
		$this->output('</div> <!-- END qam-account-items-wrapper -->');
	}
	
	// Modify markup for topbar.	
	public function nav_main_sub()
	{
		$this->output('<div class="qam-main-nav-wrapper clearfix">');
		$this->output('<div class="sb-toggle-left qam-menu-toggle"><i class="icon-th-list"></i></div>');
		$this->nav_user_search();
		$this->logo();
		$this->nav('main');
		$this->output('</div> <!-- END qam-main-nav-wrapper -->');
		$this->nav('sub');
	}
	
	// Remove the '-' from the note for the category page (notes).
	public function nav_link($navlink, $class)
	{
		if (isset($navlink['note']) && !empty($navlink['note'])) {
			$search = array(' - <', '> - ');
			$replace = array(' <', '> ');
			$navlink['note'] = str_replace($search, $replace, $navlink['note']);
		}
		parent::nav_link($navlink, $class);
	}
	
	// Rearranges the layout:
	// - Swaps the <tt>main()</tt> and <tt>sidepanel()</tt> functions
	// - Moves the header and footer functions outside qa-body-wrapper
	// - Keeps top/high and low/bottom widgets separated	 
	public function body_content()
	{
		$this->body_prefix();
		$this->notices();

		$this->widgets('full', 'top');
		$this->header();

		$this->output('<div class="qa-body-wrapper">', '');
		$this->widgets('full', 'high');

		$this->output('<div class="qa-main-wrapper">', '');
		$this->main();
        $this->banners(); 
		$this->sidepanel();
		$this->output('</div> <!-- END main-wrapper -->');

		$this->widgets('full', 'low');
		$this->output('</div> <!-- END body-wrapper -->');

		$this->footer();

		$this->body_suffix();
	}
	
	// Header in full width top bar	 
	public function header()
	{
		$class = $this->fixed_topbar ? ' fixed' : '';

		$this->output('<div id="qam-topbar" class="clearfix' . $class . '">');

		$this->nav_main_sub();
		$this->output('</div> <!-- END qam-topbar -->');

		$this->output($this->ask_button());
		$this->qam_search('the-top', 'the-top-search');
	}
	
	// Footer in full width bottom bar	 
	public function footer()
	{ /*
		$this->output('<div class="qam-footer-box">');

		$this->output('<div class="qam-footer-row">');
		$this->widgets('full', 'bottom');
		$this->output('</div> <!-- END qam-footer-row -->');

		parent::footer();
		$this->output('</div> <!-- END qam-footer-box -->'); */
	}
	
	// Overridden to customize layout and styling	
	public function sidepanel()
	{
		// remove sidebar for user profile pages
		if ($this->template == 'user')
			return;

		$this->output('<div id="qam-sidepanel-toggle"><i class="icon-left-open-big"></i></div>');
		$this->output('<div class="qa-sidepanel" id="qam-sidepanel-mobile">');
		$this->qam_search();
		$this->widgets('side', 'top');
		$this->sidebar();
		$this->widgets('side', 'high');
		$this->widgets('side', 'low');
		if (isset($this->content['sidepanel']))
			$this->output_raw($this->content['sidepanel']);
		$this->feed();
		$this->widgets('side', 'bottom');
		$this->output('</div> <!-- qa-sidepanel -->', '');
	}

	public function banners()
	{
        // Do not show side banner when we have the central
        return;

		// remove sidebar for user profile pages
		if ($this->template == 'user')
			return;

		$this->output('<div id="qam-sidepanel-toggle"><i class="icon-left-open-big"></i></div>');
		$this->output('<div class="qa-sidepanel" id="qam-sidepanel-mobile">');

        $this->output('<div style="margin-bottom: 5px;"><a href="http://tabloid.dev/?tabloid" target="_blank"><img src="/banners/reddit-banner.jpg" style="border-radius: 3px;"></a></div>', '');

		$this->output('</div> <!-- qa-sidepanel -->', '');
	}
	
	// Sidebar Links
	public function sidebar()
	{
		/*

        //$this->output('<div id="qam-sidepanel-toggle"><i class="icon-left-open-big"></i></div>');
        $this->output('            

            <div class="qa-widgets-side qa-widgets-side-top">
            <div class="qa-widget-side qa-widget-side-top">

            <h2>Tabloid Links</h2>

			<a href="http://tabloid.dev" style="font-size: 0.85em;" target="_blank"><b>Tabloid</b> | Main Website</a><br/>
			<a href="http://tabloid.dev" style="font-size: 0.85em;" target="_blank"><b>Forum</b> | User Questions</a><br/>
			<a href="http://github.com/gotzmann/tabloid" style="font-size: 0.85em;" target="_blank"><b>GitHub</b> | Source Code</a><br/>

            </div></div>
            
		');
		
		*/

		if (isset($this->content['sidebar'])) {
			$sidebar = $this->content['sidebar'];
			if (!empty($sidebar)) {
				$this->output('<div class="qa-sidebar ' . $this->welcome_widget_class . '">');
				$this->output_raw($sidebar);
				$this->output('</div> <!-- qa-sidebar -->', '');
			}
		}
	}
		
	public function item_title($item)
	{
		
		$closedText = qa_lang('main/closed');
		$imgHtml = empty($item['closed'])
			? ''
			: '<img src="' . $this->theme_dir . $this->icon_url . '/closed-q-list.png" class="qam-q-list-close-icon" alt="' . $closedText . '" title="' . $closedText . '"/>';

		if ($item['raw']['type'] == 'POST') {
			$this->output(
				'<div class="item-title">',				
				$imgHtml,
				'<a href="' . $item['url'] . '">' . $item['title'] . '</a>',
				'</div>'
			);
		} else {
			$this->output(
				'<div class="qa-q-item-title">',
				// add closed note in title
				$imgHtml,
				'<a href="' . $item['url'] . '">' . $item['title'] . '</a>',
				'</div>'
			);
		}
	}
	
	// Add RSS feeds icon	 
	public function favorite()
	{
		parent::favorite();

		// RSS feed link in title
		if (isset($this->content['feed']['url'])) {
			$feed = $this->content['feed'];
			$label = isset($feed['label']) ? $feed['label'] : '';
			$this->output('<a href="' . $feed['url'] . '" title="' . $label . '"><i class="icon-rss qam-title-rss"></i></a>');
		}
	}
	
	// Add closed icon for closed questions	 
	public function title()
	{
		$q_view = isset($this->content['q_view']) ? $this->content['q_view'] : null;

		// link title where appropriate
		$url = isset($q_view['url']) ? $q_view['url'] : false;

		// add closed image
		$closedText = qa_lang('main/closed');
		$imgHtml = empty($q_view['closed'])
			? ''
			: '<img src="' . $this->theme_dir . $this->icon_url . '/closed-q-view.png" class="qam-q-view-close-icon" alt="' . $closedText . '" width="24" height="24" title="' . $closedText . '"/>';

		if (isset($this->content['title'])) {
			$this->output(
				$imgHtml,
				$url ? '<a href="' . $url . '">' : '',
				$this->content['title'],
				$url ? '</a>' : ''
			);
		}
	}

	
	// Add view counter to question list	 
	public function q_item_stats($q_item)
	{
		$this->output('<div class="qa-q-item-stats">');

		$this->voting($q_item);
		$this->a_count($q_item);
		parent::view_count($q_item);

		$this->output('</div>');
	}
	
	// Add view counter to question view
	public function q_view_stats($q_view)
	{
		$this->output('<div class="qa-q-view-stats">');

		$this->voting($q_view);
		$this->a_count($q_view);
		parent::view_count($q_view);

		$this->output('</div>');
	}

	// NB! This q_view_main OVERWRITES the base one from theme-base.php

	// Modify user whometa, move to top	 
	public function q_view_main($q_view)
	{
		
		$this->output('<div class="qa-q-view-main">');

		if (isset($q_view['main_form_tags'])) {
			$this->output('<form ' . $q_view['main_form_tags'] . '>'); // form for buttons on question
		}
		
		$this->view_count($q_view);

        $this->post_avatar_meta($q_view, 'qa-q-view');

		$this->q_view_content($q_view);
		$this->q_view_extra($q_view);
		$this->q_view_follows($q_view);
		$this->q_view_closed($q_view);
		$this->post_tags($q_view, 'qa-q-view');

		$this->q_view_buttons($q_view);

		if (isset($q_view['main_form_tags'])) {
			if (isset($q_view['buttons_form_hidden']))
				$this->form_hidden_elements($q_view['buttons_form_hidden']);
			$this->output('</form>');
		}

		$this->c_list(isset($q_view['c_list']) ? $q_view['c_list'] : null, 'qa-q-view');
		$this->c_form(isset($q_view['c_form']) ? $q_view['c_form'] : null);

		$this->output('</div> <!-- END qa-q-view-main -->');
	}

	
	// Hide votes when zero	
	public function vote_count($post)
	{
		if ($post['raw']['basetype'] === 'C' && $post['raw']['netvotes'] == 0) {
			$post['netvotes_view']['data'] = '';
		}

		parent::vote_count($post);
	}

	
	// Move user whometa to top in answer	 
	public function a_item_main($a_item)
	{
		$this->output('<div class="qa-a-item-main">');

		if (isset($a_item['main_form_tags'])) {
			$this->output('<form ' . $a_item['main_form_tags'] . '>'); // form for buttons on answer
		}

		$this->post_avatar_meta($a_item, 'qa-a-item');

		if ($a_item['hidden'])
			$this->output('<div class="qa-a-item-hidden">');
		elseif ($a_item['selected'])
			$this->output('<div class="qa-a-item-selected">');

		$this->a_selection($a_item);
		if (isset($a_item['error']))
			$this->error($a_item['error']);
		$this->a_item_content($a_item);

		if ($a_item['hidden'] || $a_item['selected'])
			$this->output('</div>');

		$this->a_item_buttons($a_item);

		if (isset($a_item['main_form_tags'])) {
			if (isset($a_item['buttons_form_hidden']))
				$this->form_hidden_elements($a_item['buttons_form_hidden']);
			$this->output('</form>');
		}

		$this->c_list(isset($a_item['c_list']) ? $a_item['c_list'] : null, 'qa-a-item');
		$this->c_form(isset($a_item['c_form']) ? $a_item['c_form'] : null);

		$this->output('</div> <!-- END qa-a-item-main -->');
	}
	
	// Remove comment voting here	 
	public function c_list_item($c_item)
	{
		$extraclass = @$c_item['classes'] . (@$c_item['hidden'] ? ' qa-c-item-hidden' : '');

		$this->output('<div class="qa-c-list-item ' . $extraclass . '" ' . @$c_item['tags'] . '>');

		$this->c_item_main($c_item);
		$this->c_item_clear();

		$this->output('</div> <!-- END qa-c-item -->');
	}
	
	// Move user whometa to top in comment, add comment voting back in	 
	public function c_item_main($c_item)
	{
		$this->post_avatar_meta($c_item, 'qa-c-item');

		if (isset($c_item['error']))
			$this->error($c_item['error']);

		if (isset($c_item['main_form_tags'])) {
			$this->output('<form ' . $c_item['main_form_tags'] . '>'); // form for comment voting buttons
		}

		$this->voting($c_item);

		if (isset($c_item['main_form_tags'])) {
			$this->form_hidden_elements(@$c_item['voting_form_hidden']);
			$this->output('</form>');
		}

		if (isset($c_item['main_form_tags'])) {
			$this->output('<form ' . $c_item['main_form_tags'] . '>'); // form for buttons on comment
		}

		if (isset($c_item['expand_tags']))
			$this->c_item_expand($c_item);
		elseif (isset($c_item['url']))
			$this->c_item_link($c_item);
		else
			$this->c_item_content($c_item);

		$this->output('<div class="qa-c-item-footer">');
		$this->c_item_buttons($c_item);
		$this->output('</div>');

		if (isset($c_item['main_form_tags'])) {
			$this->form_hidden_elements(@$c_item['buttons_form_hidden']);
			$this->output('</form>');
		}
	}
	
	public function attribution()
	{
	}

	// User account navigation item. This will return based on login information.
	// If user is logged in, it will populate user avatar and account links.
	// If user is guest, it will populate login form and registration link.
	private function qam_user_account()
	{
		if (qa_is_logged_in()) {
			// get logged-in user avatar
			$handle = qa_get_logged_in_user_field('handle');
			$toggleClass = 'qam-logged-in';

			if (FINAL_EXTERNAL_USERS)
				$tobar_avatar = qa_get_external_avatar_html(qa_get_logged_in_user_field('userid'), $this->nav_bar_avatar_size, true);
			else {
				$tobar_avatar = qa_get_user_avatar_html(
					qa_get_logged_in_user_field('flags'),
					qa_get_logged_in_user_field('email'),
					$handle,
					qa_get_logged_in_user_field('avatarblobid'),
					qa_get_logged_in_user_field('avatarwidth'),
					qa_get_logged_in_user_field('avatarheight'),
					$this->nav_bar_avatar_size,
					false
				);
			}

			$avatar = strip_tags($tobar_avatar, '<img>');
			if (!empty($avatar))
				$handle = '';
		}
		else {
			// display login icon and label
			$handle = $this->content['navigation']['user']['login']['label'];
			$toggleClass = 'qam-logged-out';
			$avatar = '<i class="icon-key qam-auth-key"></i>';
		}

		// finally output avatar with div tag
		$handleBlock = empty($handle) ? '' : '<div class="qam-account-handle">' . qa_html($handle) . '</div>';
		$this->output(
			'<div id="qam-account-toggle" class="' . $toggleClass . '">',
			$avatar,
			$handleBlock,
			'</div>'
		);
	}
	
	// Add search-box wrapper with extra class for color scheme
	private function qam_search($addon_class = null, $ids = null)
	{
		$id = isset($ids) ? ' id="' . $ids . '"' : '';

		$this->output('<div class="qam-search ' . $this->ask_search_box_class . ' ' . $addon_class . '"' . $id . '>');
		$this->search();
		$this->output('</div>');
	}

	
	// Dynamic <code>CSS</code> based on options and other interaction.
	private function head_inline_css()
	{
		$css = array('<style>');

		if (!qa_is_logged_in())
			$css[] = '.qa-nav-user { margin: 0 !important; }';

		if (qa_request_part(1) !== qa_get_logged_in_handle()) {
			$css[] = '@media (max-width: 979px) {';
			$css[] = ' body.qa-template-user.fixed, body[class*="qa-template-user-"].fixed { padding-top: 118px !important; }';
			$css[] = ' body.qa-template-users.fixed { padding-top: 95px !important; }';
			$css[] = '}';
			$css[] = '@media (min-width: 980px) {';
			$css[] = ' body.qa-template-users.fixed { padding-top: 105px !important;}';
			$css[] = '}';
		}

		$css[] = '</style>';

		$this->output_array($css);
	}
	
	// Custom ask button for medium and small screen
	private function ask_button()
	{
		return
			'<div class="qam-ask-search-box">' .
			'<div class="qam-ask-mobile">' .
			'<a href="' . qa_path('ask', null, qa_path_to_root()) . '" class="' . $this->ask_search_box_class . '">' .
			qa_lang_html('main/nav_ask') .
			'</a>' .
			'</div>' .
			'<div class="qam-search-mobile ' . $this->ask_search_box_class . '" id="qam-search-mobile">' .
			'</div>' .
			'</div>';
	}
}
