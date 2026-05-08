<?php
	namespace Site\Navigation;

	/** @class MyAccountMenu
	 * Navigation Menu for MyAccount Widget
	 */
	class MyAccountMenu Extends \Site\Navigation\Menu {
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
					<img id="myAccountIcon" title="My Account">
					<div id="myAccntUserDiv" class="username">Sign In&nbsp;|</div>
					<div id="myAccntReg" class="username"><a href="/_register/new_customer">&nbsp;Register</a></div>
				</div>
				<div id="acct-menu" class="acct-menu"></div>
				';
				return $buffer;
			}
			$items = $this->items();

			$buffer = '
			<div id="acct-title" class="acct-title">
				<img id="myAccountIcon" title="My Account">
				<div id="myAccntUserDiv" class="username">';
			$buffer .= htmlspecialchars($customer->first_name);

			$buffer .= '</div>
			</div>
			<div id="acct-menu" class="acct-menu">
				<ul id="acct-menu-ul">
			';

			$buffer .= '
					<li id="mnuView Messages">
						<a href="/_site/messages/">View Messages</a>
					</li>';
			if (count($items) > 0) {
				foreach ($items as $item) {
					if (empty($item->target)) $buffer .= '
					<li id="mnu'.$item->title.'">'.$item->title.'</li>';
					else $buffer .= '
					<li id="mnu'.$item->title.'"><a href="'.$item->target.'">'.$item->title.'</a></li>';
				}
			}

			$buffer .= '
				</ul>
			</div>
			';
			return $buffer;
		}
	}