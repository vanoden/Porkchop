<?php
namespace Email\Transport;

class PearMail Extends Base {
	public $_auth;

	/** @method protected _deliver(email)
	 * Sends the email using the PearMail transport.
	 * @param \Email\Message $email The email message to send.
	 * @return bool Returns true on success, false on failure.
	 */
	protected function _deliver($email) {
		require_once("Mail.php");
		#require_once('Mail/mime.php');
		$connection = array(
			'host' => $this->_hostname,
			'port' => $this->_port,
			'auth' => $this->_auth,
			'username' => $this->_username,
			'password' => $this->_password,
		);
		if ($this->_secure) {
			$connection['host'] = $this->_secure . "://" . $connection['host'];
		}

		$headers = array(
			'From' => $email->from(),
			'Subject' => $email->subject(),
			'Content-type' => 'text/html'
		);

		$smtp = Mail::factory(
			'smtp',
			$connection
		);

		$mail = $smtp->send(
			$email->to(),
			$email->headers(),
			$body
		);

		if (PEAR::isError($mail)) {
			$this->error("Error sending email: " . $mail->getMessage());
			return false;
		}
		else {
			return true;
		}
	}
}
