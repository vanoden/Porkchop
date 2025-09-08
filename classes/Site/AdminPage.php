<?php
    /** @file AdminPage.php
     * @brief Admin page class
     * Extends the base Page class to provide common functionality for all admin pages
     */

    namespace Site;

    /** @class AdminPage
     * @brief Admin page class
     * Extends the base Page class to provide common functionality for all admin pages
     * @see \Site\Page
     */
    class AdminPage extends \Site\Page {
        /** @method public __construct
         * @brief Constructor
        */
        public function __construct() {
            parent::__construct();
            //$this->title("Admin");
            //$this->template("admin.tpl");
            //$this->addBreadcrumb("Admin", "/admin");
        }
    }