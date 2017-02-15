<?
	namespace Email;

	class Message {
		public $error;
		public $mail;

		public function __construct() {
			$schema = new Schema();
			if ($schema->error) {
				$this->error = $schema->error;
				return null;
			}

			# Set Defaults from Config
			$this->mail = array();
			$this->mail['provider'] = $GLOBALS['_config']->email->provider;
			$this->mail['hostname'] = $GLOBALS['_config']->email->hostname;
			$this->mail['username'] = $GLOBALS['_config']->email->username;
			$this->mail['password'] = $GLOBALS['_config']->email->password;
			$this->mail['secure'] = $GLOBALS['_config']->email->secure;
			if ($GLOBALS['_config']->email->port) $this->mail['port'] = $GLOBALS['_config']->email->port;
			else $this->mail['port'] = 25;
			if ($GLOBALS['_config']->email->username) $this->mail['auth'] = true;
			else $this->mail['auth'] = false;
		}

		public function set($key,$value) {
			$this->mail[$key] = $value;
		}

		public function send($parameters = array()) {
			if ($this->mail['provider'] == 'pear') {
				$this->sendPearMail($parameters);
			}
			elseif ($this->mail['provider'] == 'phpmailer') {
				$this->sendPHPMailer($parameters);
			}
			elseif ($this->mail['provider']) {
				$this->error = "Invalid mail provider";
				return null;
			}
			else {
				$this->error = "No mail provider";
				return null;
			}
		}

		public function sendPHPMailer($parameters = array()) {
			$this->error = "Plugin not yet supported";
			return null;
		}

		public function sendPearMail($parameters = array()) {
			require_once("Mail.php");
			#require_once('Mail/mime.php');
			$connection = array(
				'host'		=> $this->mail['hostname'],
				'port'		=> $this->mail['port'],
				'auth'		=> $this->mail['auth'],
				'username'	=> $this->mail['username'],
				'password'	=> $this->mail['password'],
			);
			if ($this->mail['secure']) {
				$connection['host'] = $this->mail['secure']."://".$connection['host'];
			}

			$headers = array(
				'From'			=> $parameters['from'],
				'Subject'		=> $parameters['subject'],
				'Content-type'	=> 'text/html'
			);

			$smtp = Mail::factory(
				'smtp',
				$connection
			);

			$mail = $smtp->send(
				$parameters['to'],
				$headers,
				$parameters['body']
			);

			if (PEAR::isError($mail)) {
				$this->error = "Error sending email: ".$mail->getMessage();
				return null;
			} else {
				return 1;
			}
		}
	}
	
?>