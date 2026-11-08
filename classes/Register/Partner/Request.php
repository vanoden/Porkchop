<?php
	/** @class Register\Partner\Request
	 * One-way partnership access requests.  A requesting organization asks another
	 * organization for the ability to receive shared data.  Approving creates a
	 * partner_entities row; it does not share any data by itself.
	 */
	namespace Register\Partner;

	class Request Extends \BaseModel {
		public const STATUS_ACTIVE = 'ACTIVE';
		public const STATUS_APPROVED = 'APPROVED';
		public const STATUS_DENIED = 'DENIED';
		public const STATUS_CANCELLED = 'CANCELLED';
		public const STATUS_EXPIRED = 'EXPIRED';
		public const MAX_ACTIVE = 3;
		public const EXPIRE_AFTER = '+1 week';

		public ?string $date_request = null;
		public ?int $user_id_request = null;
		public ?int $requesting_organization_id = null;
		public ?string $email = null;
		public ?string $token = null;
		public ?string $date_expires = null;
		public ?int $target_user_id = null;
		public ?int $target_organization_id = null;
		public ?int $user_id_approve = null;
		public ?string $status = null;
		public ?string $date_response = null;

		public function __construct($id = 0) {
			$this->_tableName = 'partner_requests';
			$this->_tableUKColumn = 'token';
			$this->_addFields(array(
				'id',
				'date_request',
				'user_id_request',
				'requesting_organization_id',
				'email',
				'token',
				'date_expires',
				'target_user_id',
				'target_organization_id',
				'user_id_approve',
				'status',
				'date_response'
			));
			parent::__construct($id);
		}

		/** @method public validStatus(string): bool */
		public function validStatus($status): bool {
			return in_array($status, array(
				self::STATUS_ACTIVE,
				self::STATUS_APPROVED,
				self::STATUS_DENIED,
				self::STATUS_CANCELLED,
				self::STATUS_EXPIRED
			), true);
		}

		/** @method public expireStale(?int $organization_id = null): int
		 * Mark ACTIVE requests past date_expires as EXPIRED.  Returns count updated.
		 */
		public function expireStale(?int $organization_id = null): int {
			$this->clearError();
			$database = new \Database\Service();
			$query = "
				UPDATE	partner_requests
				SET		status = ?,
						date_response = IFNULL(date_response, sysdate())
				WHERE	status = ?
				AND		date_expires < sysdate()
			";
			$database->AddParam(self::STATUS_EXPIRED);
			$database->AddParam(self::STATUS_ACTIVE);
			if (!empty($organization_id)) {
				$query .= " AND requesting_organization_id = ?";
				$database->AddParam($organization_id);
			}
			$database->Execute($query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return 0;
			}
			return (int)$database->affected_rows();
		}

		/** @method public countActive(int $organization_id): int */
		public function countActive(int $organization_id): int {
			$this->expireStale($organization_id);
			$database = new \Database\Service();
			$query = "
				SELECT	COUNT(*)
				FROM	partner_requests
				WHERE	requesting_organization_id = ?
				AND		status = ?
			";
			$database->AddParam($organization_id);
			$database->AddParam(self::STATUS_ACTIVE);
			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return 0;
			}
			list($count) = $rs->FetchRow();
			return (int)$count;
		}

		/** @method public createRequest(array $parameters): bool
		 * Create an ACTIVE request from the current user's organization.
		 * parameters: email (required)
		 */
		public function createRequest(array $parameters = []): bool {
			$this->clearError();

			$customer = $GLOBALS['_SESSION_']->customer();
			if (empty($customer) || empty($customer->id)) {
				$this->error("Login required to create a partnership request");
				return false;
			}
			$organization = $customer->organization();
			if (empty($organization) || empty($organization->id)) {
				$this->error("Organization required to create a partnership request");
				return false;
			}

			$email = trim($parameters['email'] ?? '');
			if (!valid_email($email)) {
				$this->error("A valid email address is required");
				return false;
			}

			if ($this->countActive($organization->id) >= self::MAX_ACTIVE) {
				$this->error("Only ".self::MAX_ACTIVE." active partnership requests are allowed. Approve, deny, or cancel an existing request first.");
				return false;
			}

			$target_user_id = null;
			$target_organization_id = null;
			$contact = new \Register\Contact();
			if ($contact->getSingleContact('email', $email) && !empty($contact->person) && !empty($contact->person->id)) {
				$target = new \Register\Customer($contact->person->id);
				if ($target->exists() && !empty($target->organization_id)) {
					$target_user_id = $target->id;
					$target_organization_id = $target->organization_id;
					if ((int)$target_organization_id === (int)$organization->id) {
						$this->error("You cannot request a partnership with your own organization");
						return false;
					}
				}
			}

			$token = bin2hex(random_bytes(16));
			$expires = date('Y-m-d H:i:s', strtotime(self::EXPIRE_AFTER));

			$ok = parent::add(array(
				'date_request' => date('Y-m-d H:i:s'),
				'user_id_request' => $customer->id,
				'requesting_organization_id' => $organization->id,
				'email' => $email,
				'token' => $token,
				'date_expires' => $expires,
				'target_user_id' => $target_user_id,
				'target_organization_id' => $target_organization_id,
				'status' => self::STATUS_ACTIVE
			));
			if (!$ok) {
				return false;
			}

			$this->email_delivered = false;
			if (!empty($target_user_id)) {
				$this->email_delivered = $this->sendRequestEmail();
			}
			return true;
		}

		/** @property set by createRequest when email was attempted */
		public bool $email_delivered = false;

		/** @method public sendRequestEmail(): bool */
		public function sendRequestEmail(): bool {
			$this->clearError();
			if (empty($this->id) || empty($this->token) || empty($this->email)) {
				$this->error("Request is incomplete for email delivery");
				return false;
			}

			$config = $GLOBALS['_config']->register->partner_request ?? null;
			$template_path = $config->template ?? (TEMPLATES.'/partner_request.html');
			if (!file_exists($template_path)) {
				$this->error("Partner request email template not found");
				app_log("Partner request template missing: ".$template_path, 'error', __FILE__, __LINE__);
				return false;
			}

			$scheme = !empty($GLOBALS['_config']->site->https) ? 'https' : 'http';
			$approve_url = $scheme.'://'.$GLOBALS['_config']->site->hostname.'/_register/partner_request_respond?token='.$this->token;

			$requester = new \Register\Customer($this->user_id_request);
			$requester_org = new \Register\Organization($this->requesting_organization_id);

			try {
				$notice_content = file_get_contents($template_path);
			}
			catch (\Exception $e) {
				$this->error("Unable to load partner request email template");
				return false;
			}

			$notice_template = new \Content\Template\Shell();
			$notice_template->content($notice_content);
			$notice_template->addParam('COMPANY.NAME', $GLOBALS['_SESSION_']->company->name ?? ($GLOBALS['_config']->site->hostname ?? ''));
			$notice_template->addParam('REQUESTER.NAME', trim(($requester->first_name ?? '').' '.($requester->last_name ?? '')) ?: ($requester->code ?? 'A user'));
			$notice_template->addParam('REQUESTER.ORGANIZATION', $requester_org->name ?? 'an organization');
			$notice_template->addParam('PARTNER.APPROVE.URL', $approve_url);

			$message = new \Email\Message();
			$message->html(true);
			$message->to($this->email);
			$message->from($config->from ?? ('no-reply@'.($GLOBALS['_config']->site->hostname ?? 'localhost')));
			$message->subject($config->subject ?? 'Partnership Access Request');
			$message->body($notice_template->output());

			$transportFactory = new \Email\Transport();
			$provider = $GLOBALS['_config']->email->provider ?? null;
			$transport = $transportFactory->Create(array('provider' => $provider));
			if (!$transport || $transport->error()) {
				$this->error("Unable to initialize email transport");
				app_log("Partner request email transport error: ".($transport ? $transport->error() : 'null'), 'error', __FILE__, __LINE__);
				return false;
			}
			if (!empty($GLOBALS['_config']->email->hostname)) $transport->hostname($GLOBALS['_config']->email->hostname);
			if (!empty($GLOBALS['_config']->email->token)) $transport->token($GLOBALS['_config']->email->token);

			if ($transport->deliver($message)) {
				app_log("Partner request email sent for request ".$this->id." to ".$this->email, 'info', __FILE__, __LINE__);
				return true;
			}
			$this->error("Failed to deliver partnership request email");
			app_log("Partner request email failed for request ".$this->id.": ".$transport->error(), 'error', __FILE__, __LINE__);
			return false;
		}

		/** @method public getByToken(string $token): bool */
		public function getByToken(string $token): bool {
			$this->clearError();
			if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
				$this->error("Invalid request token");
				return false;
			}
			return $this->get($token);
		}

		/** @method public cancel(): bool */
		public function cancel(): bool {
			$this->clearError();
			if ($this->status !== self::STATUS_ACTIVE) {
				$this->error("Only active requests can be cancelled");
				return false;
			}
			return $this->update(array(
				'status' => self::STATUS_CANCELLED,
				'date_response' => date('Y-m-d H:i:s'),
				'user_id_approve' => $GLOBALS['_SESSION_']->customer()->id ?? null
			));
		}

		/** @method public respond(string $decision): bool
		 * Approve or deny.  Approve creates partner_entities:
		 * organization_id = approving org (shares data), partner_id = requesting org (receives access).
		 */
		public function respond(string $decision): bool {
			$this->clearError();
			$this->expireStale($this->requesting_organization_id);

			if (!$this->exists() || empty($this->id)) {
				$this->error("Partnership request not found");
				return false;
			}

			// Reload in case expiry just flipped status
			$this->details();
			if ($this->status === self::STATUS_EXPIRED || $this->isExpired()) {
				if ($this->status === self::STATUS_ACTIVE) {
					$this->update(array('status' => self::STATUS_EXPIRED, 'date_response' => date('Y-m-d H:i:s')));
				}
				$this->error("This partnership request has expired");
				return false;
			}
			if ($this->status !== self::STATUS_ACTIVE) {
				$this->error("This partnership request is no longer active");
				return false;
			}

			$customer = $GLOBALS['_SESSION_']->customer();
			if (empty($customer) || empty($customer->id)) {
				$this->error("Login required to respond to a partnership request");
				return false;
			}
			$approver_org = $customer->organization();
			if (empty($approver_org) || empty($approver_org->id)) {
				$this->error("Organization membership is required to respond");
				return false;
			}
			if ((int)$approver_org->id === (int)$this->requesting_organization_id) {
				$this->error("The requesting organization cannot approve its own request");
				return false;
			}

			// Approver must match the invited email's organization, or the email itself
			if (!empty($this->target_organization_id) && (int)$this->target_organization_id !== (int)$approver_org->id) {
				$this->error("Only members of the invited organization may respond to this request");
				return false;
			}
			if (empty($this->target_organization_id)) {
				$contacts = $customer->contacts(array('type' => 'email'));
				$matched = false;
				foreach ($contacts as $contact) {
					if (strcasecmp($contact->value ?? '', $this->email) === 0) {
						$matched = true;
						break;
					}
				}
				if (!$matched) {
					$this->error("Only the invited email address may respond to this request");
					return false;
				}
			}

			$decision = strtoupper($decision);
			if ($decision === 'APPROVE' || $decision === self::STATUS_APPROVED) {
				$entity = new Entity();
				if (!$entity->createPartnership((int)$approver_org->id, (int)$this->requesting_organization_id, (int)$this->id)) {
					$this->error($entity->error() ?: "Unable to create partnership");
					return false;
				}
				return $this->update(array(
					'status' => self::STATUS_APPROVED,
					'date_response' => date('Y-m-d H:i:s'),
					'user_id_approve' => $customer->id,
					'target_organization_id' => $approver_org->id,
					'target_user_id' => $this->target_user_id ?: $customer->id
				));
			}
			elseif ($decision === 'DENY' || $decision === self::STATUS_DENIED) {
				return $this->update(array(
					'status' => self::STATUS_DENIED,
					'date_response' => date('Y-m-d H:i:s'),
					'user_id_approve' => $customer->id,
					'target_organization_id' => $approver_org->id,
					'target_user_id' => $this->target_user_id ?: $customer->id
				));
			}

			$this->error("Decision must be approve or deny");
			return false;
		}

		/** @method public isExpired(): bool */
		public function isExpired(): bool {
			return !empty($this->date_expires) && strtotime($this->date_expires) < time();
		}

		/** @method public requestingOrganization(): \Register\Organization */
		public function requestingOrganization() {
			return new \Register\Organization($this->requesting_organization_id);
		}

		/** @method public requester(): \Register\Customer */
		public function requester() {
			return new \Register\Customer($this->user_id_request);
		}
	}
