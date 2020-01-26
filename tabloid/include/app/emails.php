<?php

/*
	Wrapper functions for sending email notifications to users
*/

require_once INCLUDE_DIR . 'app/options.php';


/**
 * Suspend the sending of all email notifications via send_notification(...) if $suspend is true, otherwise
 * reinstate it. A counter is kept to allow multiple calls.
 * @param bool $suspend
 */
function suspend_notifications($suspend = true)
{
	global $notifications_suspended;

	$notifications_suspended += ($suspend ? 1 : -1);
}


/**
 * Send email to person with $userid and/or $email and/or $handle (null/invalid values are ignored or retrieved from
 * user database as appropriate). Email uses $subject and $body, after substituting each key in $subs with its
 * corresponding value, plus applying some standard substitutions such as ^site_title, ^site_url, ^handle and ^email.
 * @param $userid
 * @param $email
 * @param $handle
 * @param $subject
 * @param $body
 * @param $subs
 * @param bool $html
 * @return bool
 */
function send_notification($userid, $email, $handle, $subject, $body, $subs, $html = false)
{
	//if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }

	global $notifications_suspended;

	if ($notifications_suspended > 0)
		return false;

	require_once INCLUDE_DIR . 'db/selects.php';
	require_once INCLUDE_DIR . 'util/string.php';

	if (isset($userid)) {
		$needemail = !email_validate(@$email); // take from user if invalid, e.g. @ used in practice
		$needhandle = empty($handle);

		if ($needemail || $needhandle) {
			if (FINAL_EXTERNAL_USERS) {
				if ($needhandle) {
					$handles = qa_get_public_from_userids(array($userid));
					$handle = @$handles[$userid];
				}

				if ($needemail)
					$email = qa_get_user_email($userid);

			} else {
				$useraccount = qa_db_select_with_pending(
					array(
						'columns' => array('email', 'handle'),
						'source' => '^users WHERE userid = #',
						'arguments' => array($userid),
						'single' => true,
					)
				);

				if ($needhandle)
					$handle = @$useraccount['handle'];

				if ($needemail)
					$email = @$useraccount['email'];
			}
		}
	}

	if (isset($email) && email_validate($email)) {

		$subs['^site_title'] = qa_opt('site_title');
		$subs['^site_url'] = qa_opt('site_url');
		$subs['^handle'] = $handle;
		$subs['^email'] = $email;
		$subs['^open'] = "\n";
		$subs['^close'] = "\n";

		//return qa_send_email(array(
		return send_email([
			'fromemail' => qa_opt('from_email'),
			'fromname'  => qa_opt('site_title'),
			'toemail'   => $email,
			'toname'    => $handle,
			'subject'   => strtr($subject, $subs),
			'body'      => (empty($handle) ? '' : qa_lang_sub('emails/to_handle_prefix', $handle)) . strtr($body, $subs),
			'html'      => $html,
		]);
	}

	return false;
}


/**
 * Send the email based on the $params array - the following keys are required (some can be empty): fromemail,
 * fromname, toemail, toname, subject, body, html
 * @param $params
 * @return bool
 */
function send_email($params)
{
	//var_dump($params);
	//if (qa_to_override(__FUNCTION__)) { $args=func_get_args(); return qa_call_override(__FUNCTION__, $args); }

	// @error_log(print_r($params, true));

	require_once INCLUDE_DIR . 'vendor/PHPMailer/PHPMailerAutoload.php';

	$mailer = new PHPMailer();
	$mailer->CharSet = 'utf-8';

	$mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
	$mailer->Debugoutput = 'echo';

	$mailer->From = $params['fromemail'];
	//$mailer->Sender = $params['fromemail'];
	//$mailer->FromName = $params['fromname'];
	$mailer->FromName = '';
	//$mailer->addAddress($params['toemail'], $params['toname']);
	$mailer->addAddress($params['toemail'], '');
	if (!empty($params['replytoemail'])) {
		//$mailer->addReplyTo($params['replytoemail'], $params['replytoname']);
		$mailer->addReplyTo($params['replytoemail'], '');
	}
	$mailer->Subject = $params['subject'];
	$mailer->Body = $params['body'];

	if ($params['html'])
		$mailer->isHTML(true);

	if (USE_SMTP) {
		$mailer->isSMTP();
		//$mailer->Host = qa_opt('smtp_address');
		$mailer->Host = SMTP_HOST;
		//$mailer->Port = qa_opt('smtp_port');
		$mailer->Port = SMTP_PORT;

		//if (qa_opt('smtp_secure')) {
		if (SMTP_SECURE) {	
			$mailer->SMTPSecure = SMTP_SECURE;
		} else {
			$mailer->SMTPOptions = [
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				],
			];
		}

		//if (qa_opt('smtp_authenticate')) {
			$mailer->SMTPAuth = true;
			//$mailer->Username = qa_opt('smtp_username');
			$mailer->Username = SMTP_USER;
			//$mailer->Password = qa_opt('smtp_password');
			$mailer->Password = SMTP_PASS;
		//}
	}

	$send_status = $mailer->send();

	if (!$send_status) {
		@error_log('Tabloid email send error: ' . $mailer->ErrorInfo);
	}
	return $send_status;
}
