<?php

/*
	Widget module class for ask a question box
*/

class ask_box
{
	public function allow_template($template)
	{
		$allowed = array(
			'activity', 'categories', 'custom', 'feedback', 'qa', 'questions',
			'hot', 'search', 'tag', 'tags', 'unanswered',
		);
		return in_array($template, $allowed);
	}

	public function allow_region($region)
	{
		return in_array($region, array('main', 'side', 'full'));
	}

	public function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
	{
		if (isset($qa_content['categoryids']))
			$params = array('cat' => end($qa_content['categoryids']));
		else
			$params = null;

		?>
<div class="qa-ask-box">
	<form method="post" action="<?php echo qa_path_html('ask', $params); ?>">
		<table class="qa-form-tall-table" style="width:100%">
			<tr style="vertical-align:middle;">
				<td class="qa-form-tall-label" style="width: 1px; padding:8px; white-space:nowrap; <?php echo ($region=='side') ? 'padding-bottom:0;' : 'text-align:right;'?>">
					<?php echo strtr(qa_lang_html('question/ask_title'), array(' ' => '&nbsp;'))?>:
				</td>
		<?php if ($region=='side') : ?>
			</tr>
			<tr>
		<?php endif; ?>

				<td class="qa-form-tall-data" style="padding:8px;">
					<input name="title" type="text" class="qa-form-tall-text" style="width:95%;">
				</td>

                <?php // Special inputs for Editors, Admins and Super - but not for Moderator!
                    if (qa_get_logged_in_level() == USER_LEVEL_EDITOR || qa_get_logged_in_level() >= USER_LEVEL_ADMIN) : ?>
                    <td class="qa-form-tall-label" style="width: 1px; padding:8px; white-space:nowrap; <?php echo ($region=='side') ? 'padding-bottom:0;' : 'text-align:right;'?>">
    					<?php echo strtr(qa_lang_html('question/ask_title'), array(' ' => '&nbsp;'))?>:
    				</td>
                    <td class="qa-form-tall-data" style="padding:8px;">
                        <input name="type" type="text" class="qa-form-tall-text" style="width:95%;">
                    </td>
                <?php endif; ?>

			</tr>
		</table>
		<input type="hidden" name="doask1" value="1">
	</form>
</div>
		<?php
	}
}
