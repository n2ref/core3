-- core_controls: table
CREATE TABLE `core_controls` (
    `tbl` varchar(60) NOT NULL DEFAULT '',
    `keyfield` varchar(20) NOT NULL DEFAULT '',
    `val` varchar(20) NOT NULL DEFAULT '',
    `author` varchar(255) NOT NULL,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- core_enum: table
CREATE TABLE `core_enum` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `parent_id` int(11) unsigned DEFAULT NULL,
    `global_id` varchar(20) DEFAULT NULL,
    `name` varchar(128) NOT NULL DEFAULT '',
    `seq` int(11) NOT NULL DEFAULT '0',
    `custom_field` text,
    `author` varchar(255) DEFAULT NULL,
    `is_default_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `parent_id_name` (`parent_id`,`name`) USING BTREE,
    UNIQUE KEY `global_id` (`global_id`) USING BTREE,
    KEY `parent_id` (`parent_id`) USING BTREE,
    CONSTRAINT `fk1_core_enum` FOREIGN KEY (`parent_id`) REFERENCES `core_enum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- core_modules: table
CREATE TABLE `core_modules` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(60) NOT NULL DEFAULT '',
    `title` varchar(60) NOT NULL DEFAULT '',
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
    `author` varchar(255) DEFAULT NULL,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `title` (`title`) USING BTREE,
    UNIQUE KEY `name` (`name`) USING BTREE,
    KEY `is_visible_sw` (`is_visible_sw`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- core_modules_actions: table
CREATE TABLE `core_modules_actions` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `module_id` int(11) unsigned NOT NULL,
    `name` varchar(20) NOT NULL DEFAULT '',
    `title` varchar(128) NOT NULL DEFAULT '',
    `access_default` text,
    `access_add` text,
    `seq` int(11) NOT NULL,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `author` varchar(255) DEFAULT NULL,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `module_id_name` (`module_id`,`name`) USING BTREE,
    KEY `is_active_sw` (`is_active_sw`) USING BTREE,
    CONSTRAINT `fk1_core_modules_actions` FOREIGN KEY (`module_id`) REFERENCES `core_modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- core_modules_available: table
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
    `author` varchar(255) DEFAULT NULL,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `author` (`author`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `core_roles` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL DEFAULT '',
    `description` varchar(255) DEFAULT NULL,
    `access` text,
    `author` varchar(255) NULL DEFAULT NULL,
    `access_add` text,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `name` (`name`) USING BTREE,
    KEY `is_active_sw` (`is_active_sw`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- core_sessions: table
CREATE TABLE `core_sessions` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(11) unsigned NOT NULL,
    `refresh_token` varchar(255) NOT NULL,
    `ip` varchar(100) NOT NULL,
    `user_agent` varchar(255) NOT NULL,
    `count_requests` int(11) unsigned DEFAULT 0,
    `date_last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `user_id` (`user_id`) USING BTREE,
    KEY `refresh_token` (`refresh_token`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- core_settings: table
CREATE TABLE `core_settings` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `code` varchar(60) NOT NULL DEFAULT '',
    `description` varchar(255) DEFAULT NULL,
    `value` text,
    `data_type` varchar(20) NOT NULL DEFAULT 'text',
    `data_group` enum('system','extra','personal') NOT NULL DEFAULT 'extra',
    `seq` int(11) DEFAULT NULL,
    `author` varchar(255) DEFAULT NULL,
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- core_users: table
CREATE TABLE `core_users` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `role_id` int(11) unsigned NOT NULL,
    `login` varchar(120) NOT NULL DEFAULT '',
    `email` varchar(255) DEFAULT '',
    `pass` varchar(255) DEFAULT NULL,
    `pass_reset_token` varchar(255) DEFAULT NULL,
    `pass_reset_date` timestamp NULL DEFAULT NULL,
    `firstname` varchar(255) DEFAULT '',
    `lastname` varchar(255) DEFAULT '',
    `middlename` varchar(255) DEFAULT '',
    `certificate` text,
    `is_email_wrong_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `is_pass_changed_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `is_admin_sw` enum('Y','N') NOT NULL DEFAULT 'N',
    `is_active_sw` enum('Y','N') NOT NULL DEFAULT 'Y',
    `author` varchar(255) DEFAULT NULL,
    `date_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `login` (`login`) USING BTREE,
    UNIQUE KEY `email` (`email`) USING BTREE,
    KEY `is_active_sw` (`is_active_sw`) USING BTREE,
    KEY `role_id` (`role_id`) USING BTREE,
    CONSTRAINT `fk1_core_users` FOREIGN KEY (`role_id`) REFERENCES `core_roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


