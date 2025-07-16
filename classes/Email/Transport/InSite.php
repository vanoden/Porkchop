<?php
	namespace Email\Transport;

	class InSite extends Base {
		/** @method protected _deliver(email)
		 * Sends the email using the InSite transport.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		protected function _deliver($email) {
		    if (empty($email->from())) {
		        $this->error("No from user ID is set for InSite Message.");
		        return false;
		    }

		    if (empty($email->subject())) {
		        $this->error("No subject is set for InSite Message.");
		        return false;
		    }

		    if (empty($email->body())) {
		        $this->error("No body is set for InSite Message.");
		        return false;
		    }

			// Loop through each recipient and create a site message
		    foreach ($email->recipients() as $recipient) {
		        $siteMessage = new \Site\SiteMessage();
		        $siteMessage->add(array('user_created' => $email->from(), 'recipient_id' => $recipient, 'subject' => $email->subject(), 'content' => $email->body()));
		    }
		}
	}
