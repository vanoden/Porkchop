<?php
if (isset($GLOBALS['_SESSION_']->customer->id) && !empty($GLOBALS['_SESSION_']->customer->id)) {
echo <<<MENU
    <div id="menu">
        <a class="top_nav_button" href="/_monitor/assets">Monitors</a>
        <a class="top_nav_button" href="/_monitor/collections">Jobs</a>
        <a class="top_nav_button" href="/_register/account">Account</a>
        <a class="top_nav_button" href="/_register/logout">Logout</a>
        <a class="top_nav_button register-loggedin-admin-link" href="/_local/admin_home">Admin</a>
    </div>
MENU;
} else {
echo <<<MENU
    <div id="menu">
        <a class="top_nav_button" href="/_register/login">Login</a>
    </div>
MENU;
}
die();
