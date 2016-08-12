<?
	class EmailMessage
	{
		public $error;
		public $mail;

		public function __construct()
		{
			$init = new EmailInit();
			if ($init->error)
			{
				$this->error = $init->error;
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

		public function set($key,$value)
		{
			$this->mail[$key] = $value;
		}

		public function send($parameters = array())
		{
			if ($this->mail['provider'] == 'pear')
			{
				$this->sendPearMail($parameters);
			}
			elseif ($this->mail['provider'] == 'phpmailer')
			{
				$this->sendPHPMailer($parameters);
			}
			elseif ($this->mail['provider'])
			{
				$this->error = "Invalid mail provider";
				return null;
			}
			else
			{
				$this->error = "No mail provider";
				return null;
			}
		}

		public function sendPHPMailer($parameters = array())
		{
			$this->error = "Plugin not yet supported";
			return null;
		}

		public function sendPearMail($parameters = array())
		{
			require_once("Mail.php");
			#require_once('Mail/mime.php');
			$connection = array(
				'host'		=> $this->mail['hostname'],
				'port'		=> $this->mail['port'],
				'auth'		=> $this->mail['auth'],
				'username'	=> $this->mail['username'],
				'password'	=> $this->mail['password'],
			);
			if ($this->mail['secure'])
			{
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

			if (PEAR::isError($mail)) 
			{
				$this->error = "Error sending email: ".$mail->getMessage();
				return null;
			} else {
				return 1;
			}
		}
	}
	
	class EmailCampaign
	{
		
	}

	class EmailInit
	{
		public $error;
		public $errno;

		public function __construct()
		{
			# See if Schema is Available
			$schema_list = $GLOBALS['_database']->MetaTables();

			if (! in_array("email__info",$schema_list))
			{
				# Create company__info table
				$create_table_query = "
					CREATE TABLE email__info (
						label	varchar(100) not null primary key,
						value	varchar(255)
					)
				";
				$GLOBALS['_database']->Execute($create_table_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error creating info table in EmailInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}

			# Check Current Schema Version
			$get_version_query = "
				SELECT	value
				FROM	email__info
				WHERE	label = 'schema_version'
			";

			$rs = $GLOBALS['_database']->Execute($get_version_query);
			if (! $rs)
			{
				$this->error = "SQL Error in EmailInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			list($current_schema_version) = $rs->FetchRow();

			if ($current_schema_version < 1)
			{
				app_log("Upgrading schema to version 1",'notice',__FILE__,__LINE__);

				$add_roles_query = "
					INSERT
					INTO	register_roles
					VALUES	(null,'email manager','Can trigger emails via api')
				";
				$GLOBALS['_database']->Execute($add_roles_query);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error adding monitor roles in EmailInit::__construct: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}

				$current_schema_version = 1;
				$update_schema_version = "
					INSERT
					INTO	email__info
					VALUES	('schema_version',$current_schema_version)
					ON DUPLICATE KEY UPDATE
						value = $current_schema_version
				";
				$GLOBALS['_database']->Execute($update_schema_version);
				if ($GLOBALS['_database']->ErrorMsg())
				{
					$this->error = "SQL Error in EmailInit::schema_manager: ".$GLOBALS['_database']->ErrorMsg();
					app_log($this->error,'error',__FILE__,__LINE__);
					$GLOBALS['_database']->RollbackTrans();
					return 0;
				}
				$GLOBALS['_database']->CommitTrans();
			}
		}
	}
?>
