<?php
namespace Site;

class SiteMessage extends \BaseModel {

    public $user_created;
    public $recipient_id;
    public $date_created;
    public $important;
    public $subject;
    public $content;
    public $parent_id;

    public function __construct($id = 0) {
            $this->_tableName = 'site_messages';
            $this->_addFields(array('id','user_created','recipient_id','date_created','important','subject','content','parent_id'));
			$this->_metaTableName = 'site_messages_metadata';
			$this->_tableMetaFKColumn = 'item_id';
			$this->_tableMetaKeyColumn = 'label';
            parent::__construct($id);
    }

    /** Display name stored with an email copy. Empty when this row has no from_name label. */
    public function senderLabel(): string {
        if (empty($this->id)) return '';
        try {
            $database = new \Database\Service();
            $database->AddParam($this->id);
            $database->AddParam('from_name');
            $rs = $database->Execute(
                "SELECT `value` FROM `site_messages_metadata` WHERE `item_id` = ? AND `label` = ?"
            );
            if (!$rs) return '';
            $row = $rs->FetchRow();
            if (!is_array($row) || !isset($row[0]) || $row[0] === null || $row[0] === '') return '';
            return (string) $row[0];
        }
        catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Write one site_messages row per registered user among the email recipients.
     * A failure here is logged and does not affect the email that was already sent.
     */
    public static function copyFromEmail($email): void {
        try {
            if (!is_object($email) || !method_exists($email, 'recipients')) return;

            $userIds = array();
            foreach (self::emailAddresses($email->recipients()) as $address) {
                foreach (self::userIdsForAddress($address) as $userId) {
                    $userIds[$userId] = $userId;
                }
            }
            if (empty($userIds)) return;

            $senderId = self::senderUserId();
            if (empty($senderId)) {
                app_log('Site message copy skipped: no sender user', 'error', __FILE__, __LINE__);
                return;
            }

            $fromAddress = method_exists($email, 'from') ? (string) $email->from() : '';
            $subject = method_exists($email, 'subject') ? (string) $email->subject() : '';
            $body = method_exists($email, 'body') ? (string) $email->body() : '';
            if (self::isTransientCopy($subject)) return;

            foreach ($userIds as $recipientId) {
                $siteMessage = new self();
                $added = $siteMessage->add(array(
                    'user_created' => $senderId,
                    'recipient_id' => $recipientId,
                    'date_created' => date('Y-m-d H:i:s'),
                    'important' => 0,
                    'subject' => $subject,
                    'content' => $body,
                ));
                if (!$added) {
                    app_log('Site message copy failed: ' . $siteMessage->error(), 'error', __FILE__, __LINE__);
                    continue;
                }
                self::storeSenderMetadata($siteMessage->id, $fromAddress);
            }
        }
        catch (\Throwable $e) {
            app_log('Site message copy failed: ' . $e->getMessage(), 'error', __FILE__, __LINE__);
        }
    }

    /**
     * Subjects whose only purpose is a short-lived link. These stay in email and are not shown in Messages.
     */
    public static function transientSubjects(): array {
        $subjects = array('Two-Factor Authentication Recovery');
        $register = $GLOBALS['_config']->register ?? null;
        if (is_object($register)) {
            foreach (array('verify_email', 'forgot_password') as $key) {
                if (!empty($register->$key->subject)) $subjects[] = (string) $register->$key->subject;
            }
        }
        $unique = array();
        foreach ($subjects as $subject) {
            $subject = trim($subject);
            if ($subject !== '') $unique[$subject] = $subject;
        }
        return array_values($unique);
    }

    public static function isTransientCopy(string $subject): bool {
        $subject = trim($subject);
        foreach (self::transientSubjects() as $known) {
            if (strcasecmp($known, $subject) === 0) return true;
        }
        return false;
    }

    private static function storeSenderMetadata($messageId, string $fromAddress): void {
        if (empty($messageId)) return;
        $labels = array(
            'from_name' => 'Spectros Instruments',
            'from_address' => $fromAddress,
        );
        foreach ($labels as $label => $value) {
            $database = new \Database\Service();
            $database->AddParam($messageId);
            $database->AddParam($label);
            $database->AddParam($value);
            $database->Execute(
                "INSERT INTO `site_messages_metadata` (`item_id`, `label`, `value`) VALUES (?, ?, ?)"
            );
            if ($database->ErrorMsg()) {
                app_log('Site message metadata failed: ' . $database->ErrorMsg(), 'error', __FILE__, __LINE__);
            }
        }
    }

    private static function emailAddresses($recipients): array {
        $pending = is_array($recipients) ? $recipients : array($recipients);
        $found = array();
        while (!empty($pending)) {
            $item = array_pop($pending);
            if (is_array($item)) {
                foreach ($item as $child) $pending[] = $child;
                continue;
            }
            if (!is_string($item)) continue;
            $address = trim($item);
            if (preg_match('/<([^>]+)>/', $address, $matches)) $address = trim($matches[1]);
            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) continue;
            $found[strtolower($address)] = $address;
        }
        return array_values($found);
    }

    private static function userIdsForAddress(string $address): array {
        $ids = array();

        $contacts = new \Database\Service();
        $contacts->AddParam(strtolower($address));
        $rs = $contacts->Execute(
            "SELECT DISTINCT `person_id` FROM `register_contacts` WHERE `type` = 'email' AND LOWER(`value`) = ?"
        );
        if ($rs) {
            while ($row = $rs->FetchRow()) {
                if (!empty($row[0]) && is_numeric($row[0])) $ids[(int) $row[0]] = (int) $row[0];
            }
        }

        $logins = new \Database\Service();
        $logins->AddParam(strtolower($address));
        $rs = $logins->Execute(
            "SELECT `id` FROM `register_users` WHERE LOWER(`login`) = ? AND `status` = 'ACTIVE'"
        );
        if ($rs) {
            while ($row = $rs->FetchRow()) {
                if (!empty($row[0]) && is_numeric($row[0])) $ids[(int) $row[0]] = (int) $row[0];
            }
        }

        return array_values($ids);
    }

    private static function senderUserId(): int {
        if (!empty($GLOBALS['_SESSION_']->customer->id) && is_numeric($GLOBALS['_SESSION_']->customer->id)) {
            return (int) $GLOBALS['_SESSION_']->customer->id;
        }

        $administrators = new \Database\Service();
        $rs = $administrators->Execute(
            "SELECT rur.user_id
             FROM register_roles rr
             JOIN register_users_roles rur ON rur.role_id = rr.id
             JOIN register_users ru ON ru.id = rur.user_id
             WHERE rr.name = 'Administrator' AND ru.status = 'ACTIVE'
             ORDER BY rur.user_id
             LIMIT 1"
        );
        if ($rs) {
            $row = $rs->FetchRow();
            if (is_array($row) && !empty($row[0]) && is_numeric($row[0])) return (int) $row[0];
        }

        $fallback = new \Database\Service();
        $rs = $fallback->Execute("SELECT `id` FROM `register_users` WHERE `id` = 1 AND `status` = 'ACTIVE'");
        if ($rs) {
            $row = $rs->FetchRow();
            if (is_array($row) && !empty($row[0]) && is_numeric($row[0])) return (int) $row[0];
        }

        return 0;
    }
}
