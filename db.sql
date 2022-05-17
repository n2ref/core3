CREATE TABLE `core_controls` (
    `table_name` varchar(60) NOT NULL,
    `field_name` varchar(60) NOT NULL,
    `field_value` varchar(60) NOT NULL,
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_enum` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `parent_id` int(11) unsigned DEFAULT NULL,
    `global_name` varchar(255) DEFAULT NULL,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `custom_fields` json DEFAULT NULL,
    `seq` int(11) NOT NULL DEFAULT '0',
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `parent_id_name` (`parent_id`,`name`),
    UNIQUE KEY `global_name` (`global_name`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `fk1_core_enum` FOREIGN KEY (`parent_id`) REFERENCES `core_enum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_modules` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `dependencies` text,
    `access_default` text,
    `access_add` text,
    `uninstall` text,
    `files_hash` text,
    `version` varchar(10) NOT NULL DEFAULT '1.0.0',
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `is_visible_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `is_system_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `is_home_page_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `seq` int(11) DEFAULT NULL,
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `title` (`title`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_modules_available` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(60) NOT NULL,
    `title` varchar(60) DEFAULT NULL,
    `version` varchar(10) NOT NULL DEFAULT '1.0.0',
    `description` varchar(128) DEFAULT NULL,
    `install_info` text,
    `readme` text,
    `data` longblob,
    `files_hash` text,
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `last_user_id` (`last_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_modules_submodules` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `module_id` int(11) unsigned NOT NULL,
    `name` varchar(20) NOT NULL,
    `title` varchar(128) NOT NULL,
    `access_default` text,
    `access_add` text,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `seq` int(11) NOT NULL,
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `module_id_name` (`module_id`,`name`),
    CONSTRAINT `fk1_core_modules_actions` FOREIGN KEY (`module_id`) REFERENCES `core_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_roles` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `access` text,
    `access_add` text,
    `author` varchar(60) NOT NULL DEFAULT '',
    `position` int(11) NOT NULL,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_settings` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `code` varchar(255) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `value` text,
    `data_type` varchar(20) NOT NULL DEFAULT 'text',
    `data_group` enum('system','extra','personal') NOT NULL DEFAULT 'extra',
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `seq` int(11) DEFAULT NULL,
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_users` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `role_id` int(11) unsigned DEFAULT NULL,
    `login` varchar(255) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `pass` varchar(36) DEFAULT NULL,
    `pass_reset_token` varchar(255) DEFAULT NULL,
    `pass_reset_date` timestamp NULL DEFAULT NULL,
    `fname` varchar(255) DEFAULT '',
    `lname` varchar(255) DEFAULT '',
    `mname` varchar(255) DEFAULT '',
    `certificate` text,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `is_email_wrong_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `is_pass_changed_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `is_admin_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `last_user_id` int(11) unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `login` (`login`),
    UNIQUE KEY `email` (`email`),
    KEY `role_id` (`role_id`),
    CONSTRAINT `fk1_core_users` FOREIGN KEY (`role_id`) REFERENCES `core_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



CREATE TABLE `core_users_sessions` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(11) unsigned NOT NULL,
    `refresh_token` text NOT NULL,
    `client_ip` varchar(60) NULL DEFAULT NULL,
    `agent_name` varchar(500) NULL DEFAULT NULL,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `date_expired` timestamp NOT NULL,
    `date_last_activity` timestamp NULL DEFAULT NULL,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `fk1_core_users_sessions` FOREIGN KEY (`user_id`) REFERENCES `core_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
