<?php
	namespace Site\Navigation;

	/** @class MyAccountMenu
	 * Navigation Menu for MyAccount Widget
	 */
	class MyAccountMenu Extends \Site\Navigation\Menu {
		private function viewMessagesLabel(int $unreadCount): string {
			return 'View Messages (' . $unreadCount . ')';
		}

		private function isViewMessagesItem($item): bool {
			if (strcasecmp((string) $item->title, 'View Messages') === 0) return true;
			return !empty($item->target) && stripos((string) $item->target, '/messages') !== false;
		}

		/** @method public asHTML($parameters = array())
		 * Render navigation menu as HTML - MyAccount Widget Version
		 * @param $parameters, array of parameters for rendering
		 * @return string HTML of navigation menu
		*/
		public function asHTML($parameters = array()) {
			$items = $this->items();
			$customer = $GLOBALS['_SESSION_']->customer();
			if ($customer === null || !$customer->exists()) {
				$buffer = '
				<div id="acct-title" class="acct-title">
					<img id="myAccntIcon" title="My Account" src="/img/_global/icon_myaccount.svg">
					<div id="myAccntUserDiv" class="username">Sign In&nbsp;|</div>
					<div id="myAccntReg" class="username"><a href="/_register/new_customer">&nbsp;Register</a></div>
				</div>
				<div id="acct-menu" class="acct-menu"></div>
				';
				return $buffer;
			}
			$items = $this->items();

			$unreadCount = 0;
			$siteMessagesList = new \Site\SiteMessagesList();
			$siteMessagesList->find(array(
				'recipient_id' => $customer->id,
				'acknowledged' => 'unread',
			));
			if (!$siteMessagesList->error()) {
				$unreadCount = $siteMessagesList->count();
			}

			$buffer = '
			<div id="acct-title" class="acct-title">
				<img id="myAccntIcon" title="My Account" src="/img/_global/icon_myaccount.svg">
				<div id="myAccntUserDiv" class="username">';
			$buffer .= htmlspecialchars($customer->first_name);

			$buffer .= '</div>';
			if ($unreadCount > 0) {
				$buffer .= '<span id="myAccntPndTltSpan">(' . (int) $unreadCount . ')</span>';
			}

			$buffer .= '
			</div>
			<div id="acct-menu" class="acct-menu">
				<ul id="myAccntUL">
			';

			if (count($items) > 0) {
				foreach ($items as $item) {
					$title = $item->title;
					if ($this->isViewMessagesItem($item)) {
						$title = $this->viewMessagesLabel($unreadCount);
					}
					if (empty($item->target)) $buffer .= '
					<li id="mnu'.$item->title.'">'.htmlspecialchars($title).'</li>';
					else $buffer .= '
					<li id="mnu'.$item->title.'"><a href="'.$item->target.'">'.htmlspecialchars($title).'</a></li>';
				}
			}

			$buffer .= '
				</ul>
			</div>
			';
			return $buffer;
		}
	}