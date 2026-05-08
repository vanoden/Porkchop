<?php
	namespace Email\Transport;

	class SES Extends Base {
		/** @method protected _deliver(email)
		 * Sends the email using the Amazon SES transport.
		 * @param \Email\Message $email The email message to send.
		 * @return bool Returns true on success, false on failure.
		 */
		protected function _deliver($email) {
			$client = new SesClient([
				'profile'	=> 'default',
				'version'	=> $GLOBALS['_config']->aws->ses->version,
				'region'	=> $GLOBALS['_config']->aws->region
			]);

			try {
				$result = $SesClient->sendEmail([
					'Destination' => [
						'ToAddresses'	=> [$email->to()]
					],
					'ReplyToAddresses'	=> [$email->from()],
					'Source'	=> $email->from(),
					'Message'	=> [
						'Body'		=> [
							'Html'	=> [
								'Data'	=> $email->body(),
								'Charset'	=> 'UTF-8'
							]
						],
						'Subject'	=> [
							'Charset'	=> 'UTF-8',
							'Data'		=> $email->subject
						]
					]
				]);

				$messageId = $result['MessageId'];
				app_log("Sent message $messageId",'info');
				return true;
			} catch (AwsException $e) {
				$this->error("Email delivery error: ".$e->getAwsErrorMessage());
				return false;
			}
		}
	}
