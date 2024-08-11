
CREATE TABLE `core_controls` (
     `id` int unsigned NOT NULL AUTO_INCREMENT,
     `table_name` varchar(255) NOT NULL,
     `row_id` varchar(60) NOT NULL,
     `version` int unsigned NOT NULL DEFAULT '1',
     `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`),
     KEY `table_name` (`table_name`),
     KEY `row_id` (`row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_modules` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `version` varchar(100) NOT NULL DEFAULT '1.0.0',
    `version_hash` varchar(255) DEFAULT NULL,
    `icon` varchar(255) DEFAULT NULL,
    `group_name` varchar(255) DEFAULT NULL,
    `description` text,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `is_visible` tinyint(1) NOT NULL DEFAULT '1',
    `is_visible_index` tinyint(1) NOT NULL DEFAULT '1',
    `seq` int unsigned DEFAULT NULL,
    `repositories` text,
    `branch_updates` enum('stable','dev') DEFAULT 'stable',
    `isset_updates` tinyint(1) DEFAULT '0',
    `date_update_check` timestamp NULL DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_modules_available` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `title` varchar(255) DEFAULT NULL,
    `version` varchar(11) NOT NULL DEFAULT '1.0.0',
    `description` varchar(255) DEFAULT NULL,
    `install_info` text,
    `readme` text,
    `data` longblob,
    `files_hash` json DEFAULT NULL,
    `last_user_id` int unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `last_user_id` (`last_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_modules_versions` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `module_id` int unsigned NOT NULL,
    `repository` varchar(500) NOT NULL,
    `version` varchar(255) NOT NULL,
    `hash` varchar(255) NOT NULL,
    `date_load` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `module_id` (`module_id`),
    CONSTRAINT `fk1_core_modules_versions` FOREIGN KEY (`module_id`) REFERENCES `core_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_modules_sections` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `module_id` int unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `seq` int unsigned NOT NULL DEFAULT '0',
    `last_user_id` int unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `module_id_name` (`module_id`,`name`),
    CONSTRAINT `fk1_core_modules_actions` FOREIGN KEY (`module_id`) REFERENCES `core_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_roles` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` varchar(255) DEFAULT NULL,
    `privileges` json DEFAULT NULL,
    `author` varchar(255) NOT NULL DEFAULT '',
    `last_user_id` int unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_settings` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `code` varchar(255) NOT NULL,
    `title` varchar(255) DEFAULT NULL,
    `value` text,
    `field_type` varchar(20) NOT NULL DEFAULT 'text',
    `type` enum('system','extra','personal') NOT NULL DEFAULT 'extra',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `seq` int DEFAULT NULL,
    `last_user_id` int unsigned DEFAULT NULL,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_users` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `role_id` int unsigned DEFAULT NULL,
    `login` varchar(255) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `pass` varchar(36) DEFAULT NULL,
    `pass_reset_token` varchar(255) DEFAULT NULL,
    `pass_reset_date` timestamp NULL DEFAULT NULL,
    `name` varchar(255) DEFAULT NULL,
    `fname` varchar(255) DEFAULT '',
    `lname` varchar(255) DEFAULT '',
    `mname` varchar(255) DEFAULT '',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `is_admin` tinyint(1) NOT NULL DEFAULT '0',
    `avatar_type` enum('none','generate','upload') NOT NULL DEFAULT 'none',
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `login` (`login`),
    UNIQUE KEY `email` (`email`),
    KEY `role_id` (`role_id`),
    CONSTRAINT `fk1_core_users` FOREIGN KEY (`role_id`) REFERENCES `core_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_users_data` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `value` text,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_users_files` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `ref_id` int unsigned NOT NULL,
    `file_name` varchar(255) NOT NULL,
    `file_size` int unsigned NOT NULL,
    `file_hash` varchar(128) NOT NULL,
    `file_type` varchar(255) DEFAULT NULL,
    `field_name` varchar(255) DEFAULT NULL,
    `thumb` longblob,
    `content` longblob,
    `date_modify` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `field_name` (`field_name`),
    KEY `ref_id` (`ref_id`),
    KEY `file_hash` (`file_hash`),
    CONSTRAINT `fk1_core_users_files` FOREIGN KEY (`ref_id`) REFERENCES `core_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `core_users_sessions` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL,
    `fingerprint` varchar(255) NOT NULL,
    `token_hash` varchar(100) DEFAULT NULL,
    `client_ip` varchar(60) DEFAULT NULL,
    `agent_name` varchar(500) DEFAULT NULL,
    `count_requests` int unsigned DEFAULT '0',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `date_expired` timestamp NULL DEFAULT NULL,
    `date_last_activity` timestamp NULL DEFAULT NULL,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `fk1_core_users_sessions` FOREIGN KEY (`user_id`) REFERENCES `core_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

