use spectros;
SET FOREIGN_KEY_CHECKS=0;
SET @@sql_mode := REPLACE(@@sql_mode, 'NO_ZERO_DATE', '');

DROP TABLE IF EXISTS `action__info`;
CREATE TABLE `action__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `action_events`;
CREATE TABLE `action_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `date_event` datetime NOT NULL,
  `user_id` int NOT NULL,
  `task_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_TASK_EVENT_DATE` (`date_event`),
  KEY `IDX_TASK_EVENT_TASK` (`task_id`),
  KEY `FK_TASK_EVENT_REQUEST_ID` (`request_id`),
  KEY `FK_TASK_EVENT_USER_ID` (`user_id`),
  CONSTRAINT `action_events_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `action_requests` (`id`),
  CONSTRAINT `action_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `action_requests`;
CREATE TABLE `action_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `date_request` datetime DEFAULT NULL,
  `status` enum('NEW','CANCELLED','ASSIGNED','OPEN','PENDING CUSTOMER','PENDING VENDOR','COMPLETE','CLOSED') NOT NULL DEFAULT 'NEW',
  `user_requested` int NOT NULL,
  `user_assigned` int DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_ACTION_REQUEST_CODE` (`code`),
  KEY `IDX_ACTION_USER_ASSIGNED` (`user_assigned`),
  KEY `FK_USER_REQUESTED` (`user_requested`),
  CONSTRAINT `action_requests_ibfk_1` FOREIGN KEY (`user_requested`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `action_task_set_items`;
CREATE TABLE `action_task_set_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `set_id` int NOT NULL,
  `sort_position` int NOT NULL DEFAULT '50',
  `type_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_SORTED_ITEMS` (`set_id`,`sort_position`),
  KEY `FK_TASK_TYPE_ID` (`type_id`),
  CONSTRAINT `action_task_set_items_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `action_task_types` (`id`),
  CONSTRAINT `action_task_set_items_ibfk_2` FOREIGN KEY (`set_id`) REFERENCES `action_task_sets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `action_task_sets`;
CREATE TABLE `action_task_sets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_TASK_SET_CODE` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `action_task_types`;
CREATE TABLE `action_task_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `parameters` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_TASK_TYPE_CODE` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `action_tasks`;
CREATE TABLE `action_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_id` int NOT NULL,
  `request_id` int NOT NULL,
  `date_request` datetime NOT NULL,
  `user_requested` int NOT NULL,
  `user_assigned` int NOT NULL,
  `asset_id` int NOT NULL,
  `status` enum('NEW','CANCELLED','ASSIGNED','OPEN','PENDING CUSTOMER','PENDING VENDOR','COMPLETE') NOT NULL DEFAULT 'NEW',
  `description` text,
  PRIMARY KEY (`id`),
  KEY `IDX_TASK_USER_ASSIGNED` (`user_assigned`),
  KEY `FK_TASK_TYPE` (`type_id`),
  KEY `FK_TASK_REQUEST_ID` (`request_id`),
  KEY `FK_TASK_REQUEST_USER` (`user_requested`),
  CONSTRAINT `action_tasks_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `action_task_types` (`id`),
  CONSTRAINT `action_tasks_ibfk_2` FOREIGN KEY (`request_id`) REFERENCES `action_requests` (`id`),
  CONSTRAINT `action_tasks_ibfk_3` FOREIGN KEY (`user_requested`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `alert__info`;
CREATE TABLE `alert__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `alert_actions`;
CREATE TABLE `alert_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `escalation_id` int NOT NULL,
  `status` enum('OK','ERROR','CRITICAL','EMERGENCY') NOT NULL DEFAULT 'OK',
  PRIMARY KEY (`id`),
  KEY `fk_alert_trigger_escalation` (`escalation_id`),
  CONSTRAINT `alert_actions_ibfk_1` FOREIGN KEY (`escalation_id`) REFERENCES `alert_trigger_escalation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `alert_profiles`;
CREATE TABLE `alert_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `public` tinyint(1) NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `organization_id` int DEFAULT NULL,
  `profile_settings_data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `alert_threshold`;
CREATE TABLE `alert_threshold` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sensor_id` int NOT NULL,
  `operator` enum('<','>','=','!=') NOT NULL DEFAULT '<',
  `value` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_monitor_sensor` (`sensor_id`),
  CONSTRAINT `alert_threshold_ibfk_1` FOREIGN KEY (`sensor_id`) REFERENCES `monitor_sensors` (`sensor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `alert_trigger`;
CREATE TABLE `alert_trigger` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `alert_trigger_ibfk_1` FOREIGN KEY (`id`) REFERENCES `alert_trigger_threshold` (`trigger_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `alert_trigger_escalation`;
CREATE TABLE `alert_trigger_escalation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trigger_id` int NOT NULL,
  `type` varchar(250) DEFAULT NULL,
  `parameters` text,
  PRIMARY KEY (`id`),
  KEY `fk_alert_trigger` (`trigger_id`),
  CONSTRAINT `alert_trigger_escalation_ibfk_1` FOREIGN KEY (`trigger_id`) REFERENCES `alert_trigger` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `alert_trigger_threshold`;
CREATE TABLE `alert_trigger_threshold` (
  `trigger_id` int NOT NULL AUTO_INCREMENT,
  `threshold_id` int NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`trigger_id`),
  KEY `fk_alert_threshold` (`threshold_id`),
  CONSTRAINT `alert_trigger_threshold_ibfk_1` FOREIGN KEY (`threshold_id`) REFERENCES `alert_threshold` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `build__info`;
CREATE TABLE `build__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `build_commits`;
CREATE TABLE `build_commits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `repository_id` int NOT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_repo` (`repository_id`),
  CONSTRAINT `build_commits_ibfk_1` FOREIGN KEY (`repository_id`) REFERENCES `build_repositories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `build_products`;
CREATE TABLE `build_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `architecture` varchar(255) DEFAULT NULL,
  `description` text,
  `workspace` varchar(255) NOT NULL,
  `major_version` int DEFAULT NULL,
  `minor_version` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_workspace` (`workspace`),
  UNIQUE KEY `uk_name_arch` (`name`,`architecture`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `build_repositories`;
CREATE TABLE `build_repositories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `build_version_commits`;
CREATE TABLE `build_version_commits` (
  `version_id` int NOT NULL,
  `commit_id` int NOT NULL,
  PRIMARY KEY (`version_id`,`commit_id`),
  KEY `fk_commit` (`commit_id`),
  CONSTRAINT `build_version_commits_ibfk_1` FOREIGN KEY (`version_id`) REFERENCES `build_versions` (`id`),
  CONSTRAINT `build_version_commits_ibfk_2` FOREIGN KEY (`commit_id`) REFERENCES `build_commits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `build_versions`;
CREATE TABLE `build_versions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `major_number` int NOT NULL DEFAULT '0',
  `minor_number` int NOT NULL DEFAULT '0',
  `number` int DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `status` enum('NEW','FAILED','ACTIVE') NOT NULL DEFAULT 'NEW',
  `tarball` varchar(255) DEFAULT NULL,
  `message` text,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_number` (`product_id`,`number`),
  KEY `fk_user` (`user_id`),
  CONSTRAINT `build_versions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `build_products` (`id`),
  CONSTRAINT `build_versions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `company__info`;
CREATE TABLE `company__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `company_companies`;
CREATE TABLE `company_companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `login` varchar(50) NOT NULL DEFAULT '',
  `primary_domain` int NOT NULL DEFAULT '0',
  `status` int DEFAULT '1',
  `deleted` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `company_department_users`;
CREATE TABLE `company_department_users` (
  `department_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  PRIMARY KEY (`department_id`,`user_id`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `company_department_users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `company_departments` (`id`),
  CONSTRAINT `company_department_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `company_departments`;
CREATE TABLE `company_departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `manager_id` int NOT NULL DEFAULT '0',
  `status` enum('ACTIVE','DELETED') NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `company_domains`;
CREATE TABLE `company_domains` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` int NOT NULL DEFAULT '0',
  `comments` varchar(100) NOT NULL DEFAULT '',
  `location_id` int NOT NULL DEFAULT '0',
  `domain_name` varchar(100) NOT NULL DEFAULT '',
  `date_registered` date NOT NULL DEFAULT '0000-00-00',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_expires` date NOT NULL DEFAULT '0000-00-00',
  `registration_period` int NOT NULL DEFAULT '0',
  `register` varchar(100) NOT NULL DEFAULT '',
  `company_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_domain` (`domain_name`),
  KEY `fk_company_id` (`company_id`),
  CONSTRAINT `company_domains_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company_companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `company_locations`;
CREATE TABLE `company_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL DEFAULT '0',
  `code` varchar(100) NOT NULL DEFAULT '',
  `address_1` varchar(255) NOT NULL DEFAULT '',
  `address_2` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `state_id` int NOT NULL DEFAULT '0',
  `zip_code` int NOT NULL DEFAULT '0',
  `zip_ext` int NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `order_number_sequence` int NOT NULL DEFAULT '0',
  `area_code` int NOT NULL DEFAULT '0',
  `phone_pre` int NOT NULL DEFAULT '0',
  `phone_post` int NOT NULL DEFAULT '0',
  `phone_ext` int NOT NULL DEFAULT '0',
  `fax_code` int NOT NULL DEFAULT '0',
  `fax_pre` int NOT NULL DEFAULT '0',
  `fax_post` int NOT NULL DEFAULT '0',
  `active` int NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `service_contact` int NOT NULL DEFAULT '0',
  `sales_contact` int NOT NULL DEFAULT '0',
  `domain_id` int unsigned NOT NULL DEFAULT '0',
  `host` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `location_key` (`company_id`,`code`),
  CONSTRAINT `company_locations_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company_companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `contact__info`;
CREATE TABLE `contact__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `contact_events`;
CREATE TABLE `contact_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_event` datetime NOT NULL,
  `content` text NOT NULL,
  `status` enum('NEW','OPEN','CLOSED') DEFAULT 'NEW',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `content__info`;
CREATE TABLE `content__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `content_messages`;
CREATE TABLE `content_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL DEFAULT '0',
  `target` varchar(255) NOT NULL DEFAULT '',
  `view_order` int NOT NULL DEFAULT '500',
  `active` int NOT NULL DEFAULT '1',
  `deleted` int NOT NULL DEFAULT '0',
  `title` varchar(80) NOT NULL DEFAULT '',
  `menu_id` int NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `date_modified` datetime NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_target` (`company_id`,`target`),
  KEY `idx_main` (`company_id`,`target`,`deleted`),
  CONSTRAINT `content_messages_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company_companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `email__info`;
CREATE TABLE `email__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `email_history`;
CREATE TABLE `email_history` (
  `email_id` int NOT NULL,
  `date_event` datetime NOT NULL,
  `new_status` enum('QUEUED','SENDING','ERROR','CANCELLED','SENT','FAILED') NOT NULL DEFAULT 'QUEUED',
  `response_code` int DEFAULT NULL,
  `host` varchar(255) DEFAULT NULL,
  `result` text,
  KEY `idx_id` (`email_id`),
  KEY `idx_date` (`date_event`),
  KEY `idx_host` (`host`),
  KEY `idx_code` (`response_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `email_messages`;
CREATE TABLE `email_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_created` datetime NOT NULL,
  `date_tried` datetime DEFAULT NULL,
  `tries` int NOT NULL DEFAULT '0',
  `status` enum('QUEUED','SENDING','ERROR','CANCELLED','SENT','FAILED') NOT NULL DEFAULT 'QUEUED',
  `to` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text,
  `html` int NOT NULL DEFAULT '1',
  `process_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`,`date_created`),
  KEY `idx_date` (`date_created`),
  KEY `idx_to` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering__info`;
CREATE TABLE `engineering__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering_events`;
CREATE TABLE `engineering_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `person_id` int NOT NULL,
  `description` text,
  `date_event` datetime NOT NULL,
  `hours_worked` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `fk_task_id` (`task_id`),
  KEY `fk_person_id` (`person_id`),
  CONSTRAINT `engineering_events_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `engineering_tasks` (`id`),
  CONSTRAINT `engineering_events_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering_products`;
CREATE TABLE `engineering_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering_projects`;
CREATE TABLE `engineering_projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `manager_id` int DEFAULT NULL,
  `status` enum('NEW','OPEN','HOLD','CANCELLED','COMPLETE') NOT NULL DEFAULT 'NEW',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering_releases`;
CREATE TABLE `engineering_releases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `status` enum('NEW','TESTING','RELEASED') NOT NULL DEFAULT 'NEW',
  `date_released` datetime DEFAULT NULL,
  `date_scheduled` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering_task_comments`;
CREATE TABLE `engineering_task_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_comment` datetime DEFAULT NULL,
  `content` text,
  `code` varchar(100) NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `engineering_tasks_comments_ibfk_1` (`code`),
  KEY `engineering_tasks_comments_ibfk_2` (`user_id`),
  CONSTRAINT `engineering_tasks_comments_ibfk_1` FOREIGN KEY (`code`) REFERENCES `engineering_tasks` (`code`),
  CONSTRAINT `engineering_tasks_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering_task_hours`;
CREATE TABLE `engineering_task_hours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_worked` datetime DEFAULT NULL,
  `number_of_hours` decimal(5,2) DEFAULT '0.00',
  `code` varchar(100) NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `engineering_task_hours_ibfk_1` (`code`),
  KEY `engineering_task_hours_ibfk_2` (`user_id`),
  CONSTRAINT `engineering_task_hours_ibfk_1` FOREIGN KEY (`code`) REFERENCES `engineering_tasks` (`code`),
  CONSTRAINT `engineering_task_hours_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `engineering_tasks`;
CREATE TABLE `engineering_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date_added` datetime DEFAULT NULL,
  `description` text,
  `status` enum('NEW','HOLD','ACTIVE','CANCELLED','TESTING','COMPLETE') DEFAULT 'NEW',
  `type` enum('BUG','FEATURE','TEST') NOT NULL DEFAULT 'BUG',
  `estimate` decimal(6,2) NOT NULL DEFAULT '0.00',
  `location` varchar(255) DEFAULT NULL,
  `release_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `requested_id` int NOT NULL,
  `assigned_id` int NOT NULL DEFAULT '0',
  `date_due` datetime DEFAULT NULL,
  `priority` enum('NORMAL','IMPORTANT','URGENT','CRITICAL') NOT NULL DEFAULT 'NORMAL',
  `project_id` int DEFAULT NULL,
  `prerequisite_id` varchar(11) DEFAULT NULL,
  `testing_details` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_CODE` (`code`),
  KEY `fk_product_id` (`product_id`),
  KEY `fk_person_id` (`requested_id`),
  CONSTRAINT `engineering_tasks_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `engineering_products` (`id`),
  CONSTRAINT `engineering_tasks_ibfk_2` FOREIGN KEY (`requested_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `event__info`;
CREATE TABLE `event__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `geography__info`;
CREATE TABLE `geography__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `geography_countries`;
CREATE TABLE `geography_countries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(100) DEFAULT NULL,
  `view_order` int NOT NULL DEFAULT '500',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`),
  UNIQUE KEY `uk_abbrev` (`abbreviation`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `geography_provinces`;
CREATE TABLE `geography_provinces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `country_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(100) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  UNIQUE KEY `uk_name` (`country_id`,`name`),
  CONSTRAINT `geography_provinces_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `geography_countries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `issue__info`;
CREATE TABLE `issue__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `issue_events`;
CREATE TABLE `issue_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `issue_id` int NOT NULL,
  `person_id` int NOT NULL,
  `date_event` datetime NOT NULL,
  `description` text,
  `status_previous` enum('NEW','HOLD','OPEN','CANCELLED','COMPLETE','APPROVED') DEFAULT NULL,
  `status_new` enum('NEW','HOLD','ACTIVE','CANCELLED','COMPLETE','APPROVED') DEFAULT NULL,
  `internal` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_issue_idx` (`issue_id`),
  KEY `fk_person_idx` (`person_id`),
  CONSTRAINT `issue_events_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `issue_issues` (`id`),
  CONSTRAINT `issue_events_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `issue_issues`;
CREATE TABLE `issue_issues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `product_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('NEW','HOLD','OPEN','CANCELLED','COMPLETE','REOPENED','APPROVED') DEFAULT NULL,
  `description` text,
  `date_reported` datetime DEFAULT NULL,
  `user_reported_id` int NOT NULL,
  `date_assigned` datetime DEFAULT NULL,
  `user_assigned_id` int DEFAULT NULL,
  `date_completed` datetime DEFAULT NULL,
  `date_approved` datetime DEFAULT NULL,
  `user_approved_id` int DEFAULT NULL,
  `priority` enum('NORMAL','IMPORTANT','CRITICAL','EMERGENCY') NOT NULL DEFAULT 'NORMAL',
  `internal` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`),
  KEY `fk_product_idx` (`product_id`),
  KEY `fk_user_reported_idx` (`user_reported_id`),
  CONSTRAINT `issue_issues_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `issue_products` (`id`),
  CONSTRAINT `issue_issues_ibfk_2` FOREIGN KEY (`user_reported_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `issue_product_metadata`;
CREATE TABLE `issue_product_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `label` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_label` (`product_id`,`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `issue_products`;
CREATE TABLE `issue_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `status` enum('ACTIVE','DEPRECATED','UNSUPPORTED') DEFAULT NULL,
  `owner_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_code` (`code`),
  KEY `fk_owner_id` (`owner_id`),
  CONSTRAINT `issue_products_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `media__info`;
CREATE TABLE `media__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `media_files`;
CREATE TABLE `media_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `code` varchar(100) NOT NULL,
  `index` varchar(100) NOT NULL DEFAULT '',
  `size` int NOT NULL DEFAULT '0',
  `timestamp` timestamp NULL DEFAULT NULL,
  `mime_type` varchar(100) NOT NULL DEFAULT 'text/plain',
  `original_file` varchar(100) DEFAULT '',
  `date_uploaded` datetime DEFAULT NULL,
  `owner_id` int NOT NULL,
  `disposition` enum('inline','attachment','form-data','signal','alert','icon','render','notification') DEFAULT 'inline',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_ITEM_INDEX` (`item_id`,`index`),
  UNIQUE KEY `UK_CODE` (`code`),
  KEY `FK_OWNER_ID` (`owner_id`),
  CONSTRAINT `media_files_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `media_items` (`id`),
  CONSTRAINT `media_files_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `media_items`;
CREATE TABLE `media_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('raw','audio','video','document','image') NOT NULL DEFAULT 'raw',
  `date_created` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  `owner_id` int DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `deleted` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_item_code` (`code`),
  KEY `FK_OWNER_ID` (`owner_id`),
  CONSTRAINT `media_items_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `media_metadata`;
CREATE TABLE `media_metadata` (
  `item_id` int NOT NULL,
  `label` varchar(100) NOT NULL,
  `value` text,
  UNIQUE KEY `UK_ID_LABEL` (`item_id`,`label`),
  KEY `IDX_LABEL_VALUE` (`label`,`value`(32)),
  CONSTRAINT `media_metadata_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `media_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `media_privileges`;
CREATE TABLE `media_privileges` (
  `item_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `organization_id` int DEFAULT NULL,
  `read` int DEFAULT '0',
  `write` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `metadata_states`;
CREATE TABLE `metadata_states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `abbrev` char(20) NOT NULL DEFAULT '',
  `name` char(50) NOT NULL DEFAULT '',
  `tax_rate` decimal(5,3) NOT NULL DEFAULT '0.000',
  `country_id` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `by_abbrev` (`abbrev`),
  KEY `country_abbrev` (`country_id`,`abbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor__info`;
CREATE TABLE `monitor__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_asset_metadata`;
CREATE TABLE `monitor_asset_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_ASSET_KEY` (`asset_id`,`key`),
  CONSTRAINT `monitor_asset_metadata_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `monitor_assets` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_assets`;
CREATE TABLE `monitor_assets` (
  `asset_id` int NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(45) NOT NULL,
  `company_id` int NOT NULL,
  `asset_name` varchar(45) DEFAULT NULL,
  `organization_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `distributor_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`asset_id`),
  UNIQUE KEY `uk_product_code` (`product_id`,`asset_code`),
  CONSTRAINT `monitor_assets_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_boolean`;
CREATE TABLE `monitor_boolean` (
  `sensor_id` int NOT NULL,
  `organization_id` int NOT NULL,
  `value` tinyint(1) NOT NULL,
  `timestamp` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`sensor_id`,`timestamp`),
  KEY `idx_bool_organization` (`organization_id`),
  CONSTRAINT `fk_bool_organization` FOREIGN KEY (`organization_id`) REFERENCES `register_organizations` (`id`),
  CONSTRAINT `fk_bool_sensor` FOREIGN KEY (`sensor_id`) REFERENCES `monitor_sensors` (`sensor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_calibration_metadata`;
CREATE TABLE `monitor_calibration_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `calibration_id` int NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_calibration_metadata` (`calibration_id`,`key`),
  CONSTRAINT `monitor_calibration_metadata_ibfk_1` FOREIGN KEY (`calibration_id`) REFERENCES `monitor_calibrations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_calibrations`;
CREATE TABLE `monitor_calibrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `date_request` datetime DEFAULT NULL,
  `code` varchar(100) NOT NULL,
  `date_confirm` datetime NOT NULL,
  `date_expires` datetime NOT NULL,
  `void` int NOT NULL DEFAULT '0',
  `status` enum('INIT','READY','ERROR','CONFIRMED') NOT NULL DEFAULT 'CONFIRMED',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `IDX_ASSET_CUSTOMER` (`asset_id`,`customer_id`),
  KEY `FK_CUSTOMER_ID` (`customer_id`),
  CONSTRAINT `monitor_calibrations_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `monitor_assets` (`asset_id`),
  CONSTRAINT `monitor_calibrations_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_collection_metadata`;
CREATE TABLE `monitor_collection_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `collection_id` int NOT NULL,
  `label` varchar(100) NOT NULL,
  `value` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_COLLECTION_LABEL` (`collection_id`,`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_collection_sensors`;
CREATE TABLE `monitor_collection_sensors` (
  `collection_id` int NOT NULL,
  `sensor_id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  PRIMARY KEY (`collection_id`,`sensor_id`),
  KEY `FK_SENSOR` (`sensor_id`),
  CONSTRAINT `monitor_collection_sensors_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `monitor_collections` (`collection_id`),
  CONSTRAINT `monitor_collection_sensors_ibfk_2` FOREIGN KEY (`sensor_id`) REFERENCES `monitor_sensors` (`sensor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_collections`;
CREATE TABLE `monitor_collections` (
  `collection_id` int NOT NULL AUTO_INCREMENT,
  `collection_code` varchar(100) NOT NULL,
  `organization_id` int NOT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `custom_1` varchar(100) DEFAULT NULL,
  `custom_2` varchar(100) DEFAULT NULL,
  `custom_3` varchar(100) DEFAULT NULL,
  `custom_4` varchar(100) DEFAULT NULL,
  `custom_5` varchar(100) DEFAULT NULL,
  `custom_6` varchar(100) DEFAULT NULL,
  `custom_7` varchar(100) DEFAULT NULL,
  `custom_8` varchar(100) DEFAULT NULL,
  `custom_9` varchar(100) DEFAULT NULL,
  `status` enum('NEW','ACTIVE','COMPLETE','DELETED') NOT NULL DEFAULT 'NEW',
  `date_created` datetime NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT 'America/New_York',
  `timestamp_start` int DEFAULT '0',
  `timestamp_end` int DEFAULT '0',
  `report_code` varchar(255) DEFAULT NULL,
  `type` varchar(100) NOT NULL DEFAULT 'time span',
  PRIMARY KEY (`collection_id`),
  UNIQUE KEY `UK_CODE` (`collection_code`),
  UNIQUE KEY `UK_NAME` (`name`,`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_communications`;
CREATE TABLE `monitor_communications` (
  `session_id` int NOT NULL,
  `timestamp` int NOT NULL,
  `request` text,
  `response` text,
  PRIMARY KEY (`session_id`),
  KEY `idx_timestamp` (`timestamp`,`session_id`),
  CONSTRAINT `monitor_communications_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `session_sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_dashboard_metadata`;
CREATE TABLE `monitor_dashboard_metadata` (
  `dashboard_id` int NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `type` enum('SCALAR','OBJECT') NOT NULL DEFAULT 'SCALAR',
  PRIMARY KEY (`dashboard_id`,`key`),
  CONSTRAINT `monitor_dashboard_metadata_ibfk_1` FOREIGN KEY (`dashboard_id`) REFERENCES `monitor_dashboards` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_dashboards`;
CREATE TABLE `monitor_dashboards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `status` enum('NEW','HIDDEN','TEST','PUBLISHED') NOT NULL DEFAULT 'NEW',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_messages`;
CREATE TABLE `monitor_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `organization_id` int NOT NULL,
  `collection_id` int DEFAULT NULL,
  `asset_id` int NOT NULL,
  `sensor_id` int DEFAULT NULL,
  `date_recorded` datetime DEFAULT NULL,
  `level` enum('DEBUG','INFO','NOTICE','WARN','ERROR','CRITICAL') NOT NULL DEFAULT 'INFO',
  `message` text,
  PRIMARY KEY (`id`),
  KEY `fk_user_id` (`user_id`),
  KEY `fk_asset_id` (`asset_id`),
  KEY `fk_organization_id` (`organization_id`),
  KEY `idx_date` (`date_recorded`,`asset_id`,`sensor_id`),
  KEY `idx_level` (`date_recorded`,`level`,`organization_id`),
  CONSTRAINT `monitor_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`),
  CONSTRAINT `monitor_messages_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `monitor_assets` (`asset_id`),
  CONSTRAINT `monitor_messages_ibfk_3` FOREIGN KEY (`organization_id`) REFERENCES `register_organizations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_readings`;
CREATE TABLE `monitor_readings` (
  `sensor_id` int NOT NULL,
  `organization_id` int NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `timestamp` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`sensor_id`,`timestamp`),
  KEY `FK_ORGANIZATION` (`organization_id`),
  CONSTRAINT `monitor_readings_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `register_organizations` (`id`),
  CONSTRAINT `monitor_readings_ibfk_3` FOREIGN KEY (`sensor_id`) REFERENCES `monitor_sensors` (`sensor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_sensor_models`;
CREATE TABLE `monitor_sensor_models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `units` varchar(255) DEFAULT NULL,
  `data_type` enum('decimal','integer','boolean') NOT NULL DEFAULT 'decimal',
  `minumum_value` decimal(10,2) DEFAULT NULL,
  `maximum_value` decimal(10,2) DEFAULT NULL,
  `calculation_parameters` varchar(255) NOT NULL DEFAULT '{type: ''linear'',offset: 0,multiplier: 1}',
  `measures` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sensor_model` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_sensors`;
CREATE TABLE `monitor_sensors` (
  `sensor_id` int NOT NULL AUTO_INCREMENT,
  `sensor_code` varchar(45) NOT NULL,
  `sensor_name` varchar(45) DEFAULT NULL,
  `asset_id` int NOT NULL,
  `sensor_value` decimal(10,2) DEFAULT NULL COMMENT 'Last raw value collected, used for deltas',
  `sensor_units` varchar(45) DEFAULT NULL,
  `date_value` datetime DEFAULT NULL,
  `type` enum('integer','decimal','boolean') NOT NULL DEFAULT 'decimal',
  `model_id` int NOT NULL DEFAULT '0',
  `calibration_offset` decimal(10,2) NOT NULL DEFAULT '0.00',
  `calibration_multiplier` decimal(10,2) NOT NULL DEFAULT '1.00',
  `system` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`sensor_id`),
  UNIQUE KEY `idx_code` (`asset_id`,`sensor_code`),
  KEY `model_id` (`model_id`),
  CONSTRAINT `monitor_sensors_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `monitor_assets` (`asset_id`),
  CONSTRAINT `monitor_sensors_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `monitor_sensor_models` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `monitor_site_boundaries`;
CREATE TABLE `monitor_site_boundaries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `collection_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_boundary_collection` (`collection_id`),
  CONSTRAINT `monitor_site_boundaries_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `monitor_collections` (`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `monitor_site_boundary_vertices`;
CREATE TABLE `monitor_site_boundary_vertices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `boundary_id` int NOT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `marker` varchar(256) DEFAULT NULL,
  `view_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_vertice_order` (`boundary_id`,`view_order`),
  CONSTRAINT `monitor_site_boundary_vertices_ibfk_1` FOREIGN KEY (`boundary_id`) REFERENCES `monitor_site_boundaries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `navigation__info`;
CREATE TABLE `navigation__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `navigation_menu_items`;
CREATE TABLE `navigation_menu_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `menu_id` int NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `target` varchar(200) NOT NULL DEFAULT '',
  `view_order` int DEFAULT NULL,
  `alt` varchar(255) DEFAULT NULL,
  `description` text,
  `parent_id` int NOT NULL DEFAULT '0',
  `external` int NOT NULL DEFAULT '0',
  `ssl` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `view_order` (`view_order`),
  KEY `fk_menu_id` (`menu_id`),
  CONSTRAINT `navigation_menu_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `navigation_menus` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `navigation_menus`;
CREATE TABLE `navigation_menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `package__info`;
CREATE TABLE `package__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `package_packages`;
CREATE TABLE `package_packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `license` text,
  `platform` varchar(255) DEFAULT NULL,
  `owner_id` int NOT NULL,
  `status` enum('TEST','ACTIVE','HIDDEN') NOT NULL DEFAULT 'TEST',
  `repository_id` int NOT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_package_code` (`code`),
  KEY `fk_owner_id` (`owner_id`),
  KEY `fk_repo_id` (`repository_id`),
  CONSTRAINT `package_packages_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `register_users` (`id`),
  CONSTRAINT `package_packages_ibfk_2` FOREIGN KEY (`repository_id`) REFERENCES `storage_repositories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `package_versions`;
CREATE TABLE `package_versions` (
  `id` int NOT NULL,
  `package_id` int NOT NULL,
  `major` int NOT NULL,
  `minor` int NOT NULL,
  `build` varchar(10) NOT NULL,
  `status` enum('NEW','PUBLISHED','HIDDEN') DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_published` datetime DEFAULT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_PACKAGE_ID` (`package_id`),
  KEY `FK_USER_ID` (`user_id`),
  CONSTRAINT `package_versions_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `package_packages` (`id`),
  CONSTRAINT `package_versions_ibfk_2` FOREIGN KEY (`id`) REFERENCES `storage_files` (`id`),
  CONSTRAINT `package_versions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `page__info`;
CREATE TABLE `page__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `page_metadata`;
CREATE TABLE `page_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_id` int NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_PAGE_METADATA_PAGE_KEY` (`page_id`,`key`),
  CONSTRAINT `FK_PAGE_METADATA_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `page_pages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `page_pages`;
CREATE TABLE `page_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL,
  `view` varchar(100) NOT NULL,
  `index` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_page_views` (`module`,`view`,`index`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `page_widget_types`;
CREATE TABLE `page_widget_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `page_widgets`;
CREATE TABLE `page_widgets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_view_id` int NOT NULL,
  `type_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_page_view` (`page_view_id`),
  KEY `fk_widget_type` (`type_id`),
  CONSTRAINT `page_widgets_ibfk_1` FOREIGN KEY (`page_view_id`) REFERENCES `page_pages` (`id`),
  CONSTRAINT `page_widgets_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `page_widget_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product__info`;
CREATE TABLE `product__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `product_id` int NOT NULL,
  `image_id` int NOT NULL,
  `label` varchar(100) NOT NULL,
  PRIMARY KEY (`product_id`,`image_id`),
  KEY `FK_IMAGE_ID` (`image_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`),
  CONSTRAINT `product_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `media_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_metadata`;
CREATE TABLE `product_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`key`),
  CONSTRAINT `product_metadata_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_products`;
CREATE TABLE `product_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `type` enum('group','kit','inventory','unique') DEFAULT 'inventory',
  `status` enum('ACTIVE','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
  `on_hand` decimal(10,2) NOT NULL DEFAULT '0.00',
  `default_vendor` int DEFAULT NULL,
  `min_quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_quantity` decimal(10,2) DEFAULT NULL,
  `total_purchased` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_registration_queue`;
CREATE TABLE `product_registration_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `status` enum('PENDING','APPROVED','DENIED') DEFAULT 'PENDING',
  `date_created` datetime DEFAULT NULL,
  `date_purchased` datetime NOT NULL,
  `distributor_name` varchar(255) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `FK_CUSTOMER_ID` (`customer_id`),
  KEY `idx_serial` (`product_id`,`serial_number`),
  CONSTRAINT `product_registration_queue_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`),
  CONSTRAINT `product_registration_queue_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_relations`;
CREATE TABLE `product_relations` (
  `product_id` int NOT NULL,
  `parent_id` int NOT NULL,
  `view_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`parent_id`),
  CONSTRAINT `product_relations_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_vendor_items`;
CREATE TABLE `product_vendor_items` (
  `vendor_id` int NOT NULL,
  `product_id` int NOT NULL,
  `cost` decimal(8,2) NOT NULL DEFAULT '0.00',
  `delivery_time` int NOT NULL DEFAULT '0',
  `minimum_order` int NOT NULL DEFAULT '0',
  `vendor_sku` varchar(255) DEFAULT NULL,
  UNIQUE KEY `UK_VENDOR_ITEM` (`vendor_id`,`product_id`),
  KEY `IDX_PRODUCT` (`product_id`),
  CONSTRAINT `product_vendor_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`),
  CONSTRAINT `product_vendor_items_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `product_vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_vendor_locations`;
CREATE TABLE `product_vendor_locations` (
  `vendor_id` int NOT NULL,
  `location_id` int NOT NULL,
  PRIMARY KEY (`vendor_id`,`location_id`),
  KEY `fk_location_id` (`location_id`),
  CONSTRAINT `product_vendor_locations_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `product_vendors` (`id`),
  CONSTRAINT `product_vendor_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `register_locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `product_vendors`;
CREATE TABLE `product_vendors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `status` enum('ACTIVE','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_vendor_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register__info`;
CREATE TABLE `register__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_contacts`;
CREATE TABLE `register_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `person_id` int NOT NULL,
  `type` enum('phone','email','sms','facebook') NOT NULL DEFAULT 'email',
  `description` varchar(100) DEFAULT NULL,
  `notify` tinyint(1) NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_person_id` (`person_id`),
  KEY `fk_type` (`type`),
  CONSTRAINT `register_contact_listing_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_departments`;
CREATE TABLE `register_departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `manager_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_CODE` (`name`),
  KEY `IDX_PARENT` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_info`;
CREATE TABLE `register_info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_locations`;
CREATE TABLE `register_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `province_id` int NOT NULL,
  `country_id` int NOT NULL,
  `zip_code` varchar(12) NOT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `fk_country_id` (`country_id`),
  KEY `fk_province_id` (`province_id`),
  CONSTRAINT `register_locations_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `geography_provinces` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_organization_locations`;
CREATE TABLE `register_organization_locations` (
  `organization_id` int NOT NULL,
  `location_id` int NOT NULL,
  PRIMARY KEY (`organization_id`,`location_id`),
  KEY `register_organization_locations_location_id` (`location_id`),
  CONSTRAINT `register_organization_locations_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `register_organizations` (`id`),
  CONSTRAINT `register_organization_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `register_locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_organization_products`;
CREATE TABLE `register_organization_products` (
  `organization_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` decimal(9,2) NOT NULL,
  `date_expires` datetime DEFAULT '9999-12-31 23:59:59',
  PRIMARY KEY (`organization_id`,`product_id`),
  KEY `fk_product` (`product_id`),
  CONSTRAINT `register_organization_products_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `register_organizations` (`id`),
  CONSTRAINT `register_organization_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_organizations`;
CREATE TABLE `register_organizations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_reseller` int DEFAULT '0',
  `assigned_reseller_id` int DEFAULT '0',
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_CODE` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_password_tokens`;
CREATE TABLE `register_password_tokens` (
  `person_id` int NOT NULL,
  `code` varchar(255) NOT NULL,
  `date_expires` datetime DEFAULT '1990-01-01 00:00:00',
  `client_ip` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`person_id`),
  CONSTRAINT `register_password_tokens_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_person_metadata`;
CREATE TABLE `register_person_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `person_id` int NOT NULL,
  `key` varchar(32) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `person_id` (`person_id`,`key`),
  CONSTRAINT `person_metadata_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_privileges`;
CREATE TABLE `register_privileges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_privilege_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_queue`;
CREATE TABLE `register_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(200) NOT NULL DEFAULT '',
  `city` varchar(200) NOT NULL DEFAULT '',
  `state` varchar(200) NOT NULL DEFAULT '',
  `zip` varchar(200) NOT NULL DEFAULT '',
  `phone` varchar(200) NOT NULL DEFAULT '',
  `cell` varchar(200) NOT NULL DEFAULT '',
  `code` varchar(100) NOT NULL,
  `status` enum('VERIFYING','PENDING','APPROVED','DENIED') DEFAULT 'VERIFYING',
  `date_created` datetime DEFAULT NULL,
  `is_reseller` int DEFAULT '0',
  `assigned_reseller_id` int DEFAULT '0',
  `notes` text,
  `product_id` int DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `register_user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_CODE` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_relations`;
CREATE TABLE `register_relations` (
  `parent_id` int NOT NULL,
  `person_id` int NOT NULL,
  PRIMARY KEY (`parent_id`,`person_id`),
  KEY `fk_person_id` (`person_id`),
  CONSTRAINT `register_relations_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `register_users` (`id`),
  CONSTRAINT `register_relations_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_roles`;
CREATE TABLE `register_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_roles_privileges`;
CREATE TABLE `register_roles_privileges` (
  `role_id` int NOT NULL,
  `privilege_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`privilege_id`),
  KEY `fk_privilege_id` (`privilege_id`),
  CONSTRAINT `register_roles_privileges_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `register_roles` (`id`),
  CONSTRAINT `register_roles_privileges_ibfk_2` FOREIGN KEY (`privilege_id`) REFERENCES `register_privileges` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_user_locations`;
CREATE TABLE `register_user_locations` (
  `user_id` int NOT NULL,
  `location_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`location_id`),
  KEY `fk_loc_id` (`location_id`),
  CONSTRAINT `register_user_locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`),
  CONSTRAINT `register_user_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `register_locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_users`;
CREATE TABLE `register_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` enum('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED') NOT NULL DEFAULT 'ACTIVE',
  `last_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `login` varchar(45) NOT NULL,
  `password` varchar(64) NOT NULL DEFAULT '',
  `title` varchar(100) DEFAULT '',
  `department_id` int NOT NULL DEFAULT '0',
  `organization_id` int DEFAULT '0',
  `opt_in` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_expires` datetime NOT NULL,
  `auth_method` varchar(100) DEFAULT 'local',
  `unsubscribe_key` varchar(50) NOT NULL DEFAULT '',
  `validation_key` varchar(45) DEFAULT NULL,
  `custom_metadata` text,
  `timezone` varchar(32) NOT NULL DEFAULT 'America/New_York',
  `automation` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_login` (`login`),
  KEY `idx_organization` (`organization_id`),
  KEY `idx_unsubscribe_key` (`unsubscribe_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `register_users_roles`;
CREATE TABLE `register_users_roles` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `register_users_roles_ibfk_2` (`role_id`),
  CONSTRAINT `register_users_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`),
  CONSTRAINT `register_users_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `register_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `session__info`;
CREATE TABLE `session__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `session_hits`;
CREATE TABLE `session_hits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL DEFAULT '0',
  `server_id` int NOT NULL DEFAULT '0',
  `hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `remote_ip` varchar(20) DEFAULT NULL,
  `secure` int NOT NULL DEFAULT '0',
  `script` varchar(100) NOT NULL DEFAULT '',
  `query_string` text,
  `order_id` int NOT NULL DEFAULT '0',
  `module_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `session_sessions`;
CREATE TABLE `session_sessions` (
  `active` int NOT NULL DEFAULT '1',
  `id` int NOT NULL AUTO_INCREMENT,
  `code` char(64) NOT NULL DEFAULT '',
  `user_id` int DEFAULT NULL,
  `last_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `first_hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `browser` varchar(255) DEFAULT NULL,
  `company_id` int NOT NULL DEFAULT '0',
  `c_id` int DEFAULT NULL,
  `e_id` int DEFAULT NULL,
  `prev_session` varchar(100) NOT NULL DEFAULT '',
  `refer_url` text,
  `timezone` varchar(32) NOT NULL DEFAULT 'America/New_York',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `end_time` (`last_hit_date`),
  KEY `idx_active` (`company_id`,`active`,`id`,`user_id`),
  KEY `idx_last_hit` (`company_id`,`user_id`,`last_hit_date`),
  CONSTRAINT `session_sessions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company_companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `shipping__info`;
CREATE TABLE `shipping__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `shipping_items`;
CREATE TABLE `shipping_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `package_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `condition` enum('OK','DAMAGED') DEFAULT NULL,
  `quantity` int NOT NULL,
  `description` text,
  `shipment_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product` (`product_id`),
  KEY `fk_shipping_items_shipment` (`shipment_id`),
  CONSTRAINT `shipping_items_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product_products` (`id`),
  CONSTRAINT `shipping_items_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipping_shipments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `shipping_packages`;
CREATE TABLE `shipping_packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shipment_id` int NOT NULL,
  `number` int NOT NULL,
  `tracking_code` varchar(255) DEFAULT NULL,
  `status` enum('READY','SHIPPED','RECEIVED','RETURNED') NOT NULL DEFAULT 'READY',
  `condition` enum('OK','DAMAGED') DEFAULT NULL,
  `height` decimal(6,2) NOT NULL DEFAULT '0.00',
  `width` decimal(6,2) NOT NULL DEFAULT '0.00',
  `depth` decimal(6,2) NOT NULL DEFAULT '0.00',
  `weight` decimal(6,2) NOT NULL DEFAULT '0.00',
  `shipping_cost` decimal(6,2) NOT NULL DEFAULT '0.00',
  `date_received` datetime DEFAULT NULL,
  `user_received_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_line` (`shipment_id`,`number`),
  KEY `idx_tracking_code` (`shipment_id`,`tracking_code`),
  CONSTRAINT `shipping_packages_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipping_shipments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `shipping_shipments`;
CREATE TABLE `shipping_shipments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `document_number` varchar(255) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_shipped` datetime DEFAULT NULL,
  `status` enum('NEW','SHIPPED','LOST','RECEIVED','RETURNED') NOT NULL DEFAULT 'NEW',
  `send_contact_id` int NOT NULL,
  `send_location_id` int NOT NULL,
  `rec_contact_id` int NOT NULL,
  `rec_location_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `instructions` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_document` (`document_number`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `fk_sender` (`send_contact_id`),
  KEY `fk_receiver` (`rec_contact_id`),
  KEY `fk_send_from` (`send_location_id`),
  KEY `fk_send_to` (`rec_location_id`),
  KEY `idx_date` (`date_entered`),
  KEY `idx_status` (`status`,`date_entered`),
  CONSTRAINT `shipping_shipments_ibfk_1` FOREIGN KEY (`send_contact_id`) REFERENCES `register_users` (`id`),
  CONSTRAINT `shipping_shipments_ibfk_2` FOREIGN KEY (`rec_contact_id`) REFERENCES `register_users` (`id`),
  CONSTRAINT `shipping_shipments_ibfk_3` FOREIGN KEY (`send_location_id`) REFERENCES `register_locations` (`id`),
  CONSTRAINT `shipping_shipments_ibfk_4` FOREIGN KEY (`rec_location_id`) REFERENCES `register_locations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `shipping_vendors`;
CREATE TABLE `shipping_vendors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_account_number` (`account_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `site_configurations`;
CREATE TABLE `site_configurations` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `storage__info`;
CREATE TABLE `storage__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `storage_file_metadata`;
CREATE TABLE `storage_file_metadata` (
  `file_id` int NOT NULL,
  `key` varchar(45) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`file_id`,`key`),
  CONSTRAINT `storage_file_metadata_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `storage_files` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `storage_file_roles`;
CREATE TABLE `storage_file_roles` (
  `file_id` int NOT NULL,
  `role_id` int NOT NULL,
  `read` int NOT NULL DEFAULT '0',
  `write` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`,`role_id`),
  KEY `fk_role` (`role_id`),
  CONSTRAINT `storage_file_roles_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `storage_files` (`id`),
  CONSTRAINT `storage_file_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `register_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `storage_files`;
CREATE TABLE `storage_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `repository_id` int NOT NULL,
  `path` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `size` int NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `user_id` int NOT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `read_protect` enum('NONE','AUTH','ROLE','ORGANIZATION','USER') NOT NULL DEFAULT 'NONE',
  `write_protect` enum('NONE','AUTH','ROLE','ORGANIZATION','USER') NOT NULL DEFAULT 'NONE',
  `display_name` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  UNIQUE KEY `uk_file_name` (`repository_id`,`path`,`name`),
  KEY `fk_user_id` (`user_id`),
  CONSTRAINT `storage_files_ibfk_1` FOREIGN KEY (`repository_id`) REFERENCES `storage_repositories` (`id`),
  CONSTRAINT `storage_files_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `storage_repositories`;
CREATE TABLE `storage_repositories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `status` enum('NEW','ACTIVE','DISABLED') NOT NULL DEFAULT 'NEW',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_storage_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `storage_repository_metadata`;
CREATE TABLE `storage_repository_metadata` (
  `repository_id` int NOT NULL,
  `key` varchar(45) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`repository_id`,`key`),
  CONSTRAINT `storage_repository_metadata_ibfk_1` FOREIGN KEY (`repository_id`) REFERENCES `storage_repositories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support__info`;
CREATE TABLE `support__info` (
  `label` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_action_events`;
CREATE TABLE `support_action_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action_id` int NOT NULL,
  `type` enum('BUILD','SHIP','RETURN','REPAIR') NOT NULL,
  `user_id` int NOT NULL,
  `date_event` datetime DEFAULT NULL,
  `description` text,
  `hours` decimal(5,1) NOT NULL DEFAULT '0.0',
  `status` enum('NEW','ASSIGNED','ACTIVE','PENDING CUSTOMER','PENDING VENDOR','CANCELLED','COMPLETE') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_action` (`action_id`),
  KEY `fk_user` (`user_id`),
  CONSTRAINT `support_action_events_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `support_item_actions` (`id`),
  CONSTRAINT `support_action_events_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_item_actions`;
CREATE TABLE `support_item_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `entered_id` int NOT NULL,
  `date_requested` datetime DEFAULT NULL,
  `requested_id` int NOT NULL,
  `date_assigned` datetime DEFAULT NULL,
  `assigned_id` int DEFAULT NULL,
  `date_completed` datetime DEFAULT NULL,
  `status` enum('NEW','ASSIGNED','ACTIVE','PENDING CUSTOMER','PENDING VENDOR','CANCELLED','COMPLETE') DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `fk_item` (`item_id`),
  KEY `fk_requested` (`requested_id`),
  KEY `idx_date_request` (`date_requested`,`requested_id`),
  CONSTRAINT `support_item_actions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `support_request_items` (`id`),
  CONSTRAINT `support_item_actions_ibfk_2` FOREIGN KEY (`requested_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_item_comments`;
CREATE TABLE `support_item_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_id` int NOT NULL,
  `author_id` int NOT NULL,
  `date_comment` datetime DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `fk_item` (`item_id`),
  KEY `fk_author` (`author_id`),
  KEY `idx_date` (`date_comment`),
  CONSTRAINT `support_item_comments_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `support_request_items` (`id`),
  CONSTRAINT `support_item_comments_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_parts`;
CREATE TABLE `support_parts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `description` text,
  PRIMARY KEY (`id`),
  KEY `fk_event` (`event_id`),
  KEY `fk_user` (`user_id`),
  CONSTRAINT `support_parts_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `support_action_events` (`id`),
  CONSTRAINT `support_parts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_request_items`;
CREATE TABLE `support_request_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` int NOT NULL,
  `line` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `description` text,
  `status` enum('NEW','ACTIVE','PENDING_VENDOR','PENDING_CUSTOMER','COMPLETE','CLOSED') NOT NULL DEFAULT 'NEW',
  `assigned_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_line` (`request_id`,`line`),
  KEY `idx_serial` (`product_id`,`serial_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_requests`;
CREATE TABLE `support_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `customer_id` int NOT NULL,
  `organization_id` int NOT NULL,
  `date_request` datetime DEFAULT NULL,
  `type` enum('ORDER','SERVICE') NOT NULL,
  `status` enum('NEW','CANCELLED','OPEN','COMPLETE','CLOSED') NOT NULL DEFAULT 'NEW',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `fk_customer` (`customer_id`),
  KEY `idx_date` (`date_request`),
  KEY `idx_status` (`status`,`date_request`),
  CONSTRAINT `support_requests_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_rmas`;
CREATE TABLE `support_rmas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `item_id` int NOT NULL,
  `approved_id` int NOT NULL,
  `date_approved` datetime DEFAULT NULL,
  `shipment_id` int DEFAULT NULL,
  `status` enum('NEW','ACCEPTED','PRINTED','CLOSED') DEFAULT NULL,
  `document_id` int DEFAULT NULL,
  `billing_contact_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_item` (`item_id`),
  KEY `fk_approver` (`approved_id`),
  KEY `idx_date` (`date_approved`),
  KEY `idx_status` (`status`,`date_approved`),
  CONSTRAINT `support_rmas_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `support_request_items` (`id`),
  CONSTRAINT `support_rmas_ibfk_2` FOREIGN KEY (`approved_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_shipment_items`;
CREATE TABLE `support_shipment_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `action_id` int NOT NULL,
  `shipment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_action` (`action_id`),
  KEY `fk_user` (`user_id`),
  CONSTRAINT `support_shipment_items_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `support_item_actions` (`id`),
  CONSTRAINT `support_shipment_items_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_shipments`;
CREATE TABLE `support_shipments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `date_shipped` datetime DEFAULT NULL,
  `date_received` datetime DEFAULT NULL,
  `shipper` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `status` enum('READY','SHIPPED','RECEIVED') NOT NULL DEFAULT 'READY',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_date` (`date_shipped`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `support_task_hours`;
CREATE TABLE `support_task_hours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_worked` datetime DEFAULT NULL,
  `number_of_hours` decimal(5,2) DEFAULT '0.00',
  `code` varchar(100) NOT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_task_hours_ibfk_1` (`code`),
  KEY `support_task_hours_ibfk_2` (`user_id`),
  CONSTRAINT `support_task_hours_ibfk_1` FOREIGN KEY (`code`) REFERENCES `support_requests` (`code`),
  CONSTRAINT `support_task_hours_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `register_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


/** INSERT FIXTURE DATA TO START **/
INSERT INTO `company_companies` VALUES (1,'Spectros Instruments','',1,1,0);
INSERT INTO `company_locations` VALUES (1,1,'localhost','','','',0,0,0,'',0,0,0,0,0,0,0,0,0,'',0,0,1,'localhost');
INSERT INTO `company_domains` VALUES (1,1,'',1,'localhost','0000-00-00','0000-00-00 00:00:00','0000-00-00',0,'',1);

INSERT INTO `register_users` VALUES (1,'ACTIVE','Spectros User',NULL,'Spectros User','spectros','*A8D4C1EB4499988FAB79F9C0991FD568FBC6054E','',0,1,0,'2014-10-01 01:04:24','2020-08-22 19:52:06','0000-00-00 00:00:00','local','','d02fe6499e2e7b6edf5a1f88036d9e84',NULL,'America/New_York',0);
INSERT INTO `register_organizations` VALUES (1, 'Spectros Instruments','12345','ACTIVE','0000-00-00 00:00:00','0','0','');
INSERT INTO `register_roles` VALUES ('1','register manager','Manage accounts and roles'),('2','register reporter','c'),('3','content operator','Can add/edit pages'),('4','content developer','c'),('5','product manager','c'),('6','product reporter','c'),('11','media manager','c'),('12','media reporter','c'),('13','media developer','c'),('14','monitor manager','c'),('15','monitor reporter','c'),('16','monitor admin','c'),('17','support manager','c'),('18','support reporter','c'),('19','email manager','c'),('20','contact admin','c'),('87','action manager','c'),('88','action user','c'),('89','monitor asset','c'),('90','storage manager','c'),('91','storage upload','c'),('92','package manager','c'),('93','issue admin','c'),('94','engineering manager','c'),('95','engineering user','c'),('96','administrator','c'),('97','support user','See and Update Customer Support Requests'),('100','developer','c'),('101','operator','c'),('102','manager','c'),('103','engineering reporter','c'),('104','credit manager','c'),('105','build manager','b'),('106','build user','b'),('107','geography manager','geography manager'),('108','geography user','geography user'),('109','location manager','Can view and manage location entries'),('110','shipping manager','Can browse all shipments'),('111','alert manager','Can view/edit assets, sensors and collections'),('112','alert reporter','Can view assets, sensors and collections'),('113','alert admin','Full access to alert data'),('114','alert asset','Holding role for actual devices that can post data.');
INSERT INTO `register_roles_privileges` VALUES ('3','13'),('3','14'),('3','15');
INSERT INTO `register_users_roles` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),(1,87),(1,88),(1,90),(1,91),(1,92),(1,93),(1,95),(1,96),(1,97),(1,103);

INSERT INTO `monitor_sensors` VALUES (1,1,'Kevin Monitor Sensor',1,NULL,'celcius',NULL,'decimal',1,'0.00','1.00',0);
INSERT INTO `monitor_sensors` VALUES (2,2,'Kevin Monitor Sensor Humidity',1,NULL,'mg/L',NULL,'integer',1,'0.00','1.00',0);
INSERT INTO `monitor_sensor_models` VALUES ('1','Temperature Sensor','Temp Sensor','deg. celcius','decimal',NULL,NULL,'{type: \'linear\',offset: 0,multiplier: 1}',NULL,NULL);
INSERT INTO `monitor_sensor_models` VALUES ('2','Humidity Sensor','Humidity Sensor','mg/L','decimal',NULL,NULL,'{type: \'linear\',offset: 0,multiplier: 1}',NULL,NULL);
INSERT INTO `product_products` VALUES ('1','RPI-KEVIN-ZERO','Raspberry PI Zero','Small Temperature Monitor','unique','ACTIVE','0.00',NULL,'0.00',NULL,'0.00','0.00');
INSERT INTO `monitor_assets` VALUES ('1','KEV-RPI-TEMP','1','Raspberry Pi Zero W w/DHT 11','1','1','0');

INSERT INTO `monitor_collections` VALUES (1,'ABCD1234',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'ACTIVE','2021-01-01 00:00:00','DEV-COLLECTION-SITE','America/New_York',1609502400,1893499200,NULL,'time span');
INSERT INTO `monitor_collection_sensors` VALUES (1,1,'RPI Temp',NULL,NULL);
INSERT INTO `monitor_collection_sensors` VALUES (1,2,'RPI Humdity',NULL,NULL)


/** RESTORE ALL THE REFERENTIAL INTEGRITY **/
SET FOREIGN_KEY_CHECKS=1;

