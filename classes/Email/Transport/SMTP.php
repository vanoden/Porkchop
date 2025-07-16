<?php
	namespace Email\Transport;

	class SMTP Extends Base {

		/** @method protected _deliver(email)
		 * Sends the email using the SMTP transport.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		protected function _deliver($email) {
			$smtp_to = $email->to();
			$smtp_from = $email->from();
			$smtp_subject = $email->subject();
			$smtp_body = $email->body();

			// In case any of our lines are larger than 70 characters, we should use wordwrap()
			$smtp_body = wordwrap($smtp_body, 70, "\r\n");
			$smtp_headers = "From: $smtp_from\r\n";
			$smtp_headers .= "Reply-To: $smtp_to\r\n";
			if (mail($smtp_to, $smtp_subject, $smtp_body, $smtp_headers)) {
				$this->_result = 'Email sent via SMTP';
				app_log("SMTP Email sent to $smtp_to from $smtp_from with subject '$smtp_subject'", 'info');
				return true; // Return true on successful delivery
			}
			else {
				$this->error(error_get_last()['message']);
				return false; // Return false on failure
			}
		}
	}
