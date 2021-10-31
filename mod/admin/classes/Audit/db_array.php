<?php
//DB Array Initilizing
$DB_ARRAY = array();

//TABLES contains information about tables
$DB_ARRAY['TABLES'] = array();

//TABLE: core_controls
$DB_ARRAY['TABLES']['core_controls'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_controls']['ENGINE']  = "InnoDB";
//Primary Key is not defined for core_controls
//Define array for columns
$DB_ARRAY['TABLES']['core_controls']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['tbl'] = array();
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['tbl']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['tbl']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['tbl']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['tbl']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['keyfield'] = array();
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['keyfield']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['keyfield']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['keyfield']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['keyfield']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['val'] = array();
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['val']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['val']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['val']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['val']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastupdate']['TYPE']    = "varchar(30)";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastupdate']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastupdate']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastuser']['TYPE']    = "int(11)";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastuser']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_controls']['COLUMNS']['lastuser']['EXTRA']   = "";


//TABLE: core_enum
$DB_ARRAY['TABLES']['core_enum'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_enum']['ENGINE']  = "InnoDB";
//Primary Key for core_enum
$DB_ARRAY['TABLES']['core_enum']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_enum']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['name'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['name']['TYPE']    = "varchar(128)";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['name']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['name']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['name']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['parent_id'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['parent_id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['parent_id']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['parent_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['parent_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_default_sw'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_default_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_default_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_default_sw']['DEFAULT'] = "N";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_default_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastuser']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastupdate']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastupdate']['DEFAULT'] = "CURRENT_TIMESTAMP";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['lastupdate']['EXTRA']   = "on update CURRENT_TIMESTAMP";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_active_sw'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_active_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_active_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_active_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['is_active_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['seq'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['seq']['TYPE']    = "int(11)";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['seq']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['seq']['DEFAULT'] = "0";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['seq']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['global_id'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['global_id']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['global_id']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['global_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['global_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['custom_field'] = array();
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['custom_field']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['custom_field']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['custom_field']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_enum']['COLUMNS']['custom_field']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_enum']['FK'] = array();

$DB_ARRAY['TABLES']['core_enum']['FK']['fk1_core_enum']['COL_NAME']   = "parent_id";
$DB_ARRAY['TABLES']['core_enum']['FK']['fk1_core_enum']['REF_TABLE']  = "core_enum";
$DB_ARRAY['TABLES']['core_enum']['FK']['fk1_core_enum']['REF_COLUMN'] = "id";
$DB_ARRAY['TABLES']['core_enum']['FK']['fk1_core_enum']['ON_DELETE']  = "CASCADE";
$DB_ARRAY['TABLES']['core_enum']['FK']['fk1_core_enum']['ON_UPDATE']  = "CASCADE";

//Table Key array
$DB_ARRAY['TABLES']['core_enum']['KEY'] = array();
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx1_core_enum'] = array();
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx1_core_enum']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx1_core_enum']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx1_core_enum']['COLUMNS']['parent_id'] = "0";
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx1_core_enum']['COLUMNS']['name']    = "1";

$DB_ARRAY['TABLES']['core_enum']['KEY']['idx2_core_enum'] = array();
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx2_core_enum']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx2_core_enum']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx2_core_enum']['COLUMNS']['global_id'] = "0";

$DB_ARRAY['TABLES']['core_enum']['KEY']['idx3_core_enum'] = array();
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx3_core_enum']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_enum']['KEY']['idx3_core_enum']['COLUMNS']['parent_id'] = "0";


//TABLE: core_log
$DB_ARRAY['TABLES']['core_log'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_log']['ENGINE']  = "MyISAM";
//Primary Key for core_log
$DB_ARRAY['TABLES']['core_log']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_log']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['user_id'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['user_id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['user_id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['user_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['user_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['sid'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['sid']['TYPE']    = "varchar(128)";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['sid']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['sid']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['sid']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['action'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['action']['TYPE']    = "longtext";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['action']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['action']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['action']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['lastupdate']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['lastupdate']['DEFAULT'] = "CURRENT_TIMESTAMP";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['lastupdate']['EXTRA']   = "on update CURRENT_TIMESTAMP";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['query'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['query']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['query']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['query']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['query']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['request_method'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['request_method']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['request_method']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['request_method']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['request_method']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['remote_port'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['remote_port']['TYPE']    = "mediumint(9)";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['remote_port']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['remote_port']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['remote_port']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_log']['COLUMNS']['ip'] = array();
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['ip']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['ip']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['ip']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_log']['COLUMNS']['ip']['EXTRA']   = "";


//Table Key array
$DB_ARRAY['TABLES']['core_log']['KEY'] = array();
$DB_ARRAY['TABLES']['core_log']['KEY']['idx1_core_log'] = array();
$DB_ARRAY['TABLES']['core_log']['KEY']['idx1_core_log']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_log']['KEY']['idx1_core_log']['COLUMNS']['user_id'] = "0";

$DB_ARRAY['TABLES']['core_log']['KEY']['idx2_core_log'] = array();
$DB_ARRAY['TABLES']['core_log']['KEY']['idx2_core_log']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_log']['KEY']['idx2_core_log']['COLUMNS']['sid']     = "0";


//TABLE: core_modules
$DB_ARRAY['TABLES']['core_modules'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_modules']['ENGINE']  = "InnoDB";
//Primary Key for core_modules
$DB_ARRAY['TABLES']['core_modules']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_modules']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['name'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['name']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['name']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['name']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['name']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['title'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['title']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['title']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['title']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['title']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['global_id'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['global_id']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['global_id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['global_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['global_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastuser']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['dependencies'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['dependencies']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['dependencies']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['dependencies']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['dependencies']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_default'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_default']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_default']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_default']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_default']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_add'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_add']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_add']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_add']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['access_add']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['uninstall'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['uninstall']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['uninstall']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['uninstall']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['uninstall']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['files_hash'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['files_hash']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['files_hash']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['files_hash']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['files_hash']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['version'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['version']['TYPE']    = "varchar(10)";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['version']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['version']['DEFAULT'] = "1.0.0";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['version']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_active_sw'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_active_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_active_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_active_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_active_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_visible_sw'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_visible_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_visible_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_visible_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_visible_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_system_sw'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_system_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_system_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_system_sw']['DEFAULT'] = "N";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_system_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_home_page_sw'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_home_page_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_home_page_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_home_page_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['is_home_page_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastupdate']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastupdate']['DEFAULT'] = "CURRENT_TIMESTAMP";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['lastupdate']['EXTRA']   = "on update CURRENT_TIMESTAMP";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['date_created'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['date_created']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['date_created']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['date_created']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['date_created']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['seq'] = array();
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['seq']['TYPE']    = "int(11)";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['seq']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['seq']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules']['COLUMNS']['seq']['EXTRA']   = "";


//Table Key array
$DB_ARRAY['TABLES']['core_modules']['KEY'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx1_core_modules'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx1_core_modules']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx1_core_modules']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx1_core_modules']['COLUMNS']['title']   = "0";

$DB_ARRAY['TABLES']['core_modules']['KEY']['idx2_core_modules'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx2_core_modules']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx2_core_modules']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx2_core_modules']['COLUMNS']['name']    = "0";

$DB_ARRAY['TABLES']['core_modules']['KEY']['idx3_core_modules'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx3_core_modules']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx3_core_modules']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['idx3_core_modules']['COLUMNS']['global_id'] = "0";

$DB_ARRAY['TABLES']['core_modules']['KEY']['core_modules_idx1'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['core_modules_idx1']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_modules']['KEY']['core_modules_idx1']['COLUMNS']['is_visible_sw'] = "0";


//TABLE: core_modules_actions
$DB_ARRAY['TABLES']['core_modules_actions'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_modules_actions']['ENGINE']  = "InnoDB";
//Primary Key for core_modules_actions
$DB_ARRAY['TABLES']['core_modules_actions']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['module_id'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['module_id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['module_id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['module_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['module_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['name'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['name']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['name']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['name']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['name']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['title'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['title']['TYPE']    = "varchar(128)";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['title']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['title']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['title']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_default'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_default']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_default']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_default']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_default']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_add'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_add']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_add']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_add']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['access_add']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['is_active_sw'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['is_active_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['is_active_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['is_active_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['is_active_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastuser']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastupdate']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastupdate']['DEFAULT'] = "CURRENT_TIMESTAMP";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['lastupdate']['EXTRA']   = "on update CURRENT_TIMESTAMP";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['date_created'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['date_created']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['date_created']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['date_created']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['date_created']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['seq'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['seq']['TYPE']    = "int(11)";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['seq']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['seq']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_actions']['COLUMNS']['seq']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_actions']['FK'] = array();

$DB_ARRAY['TABLES']['core_modules_actions']['FK']['fk1_core_modules_actions']['COL_NAME']   = "module_id";
$DB_ARRAY['TABLES']['core_modules_actions']['FK']['fk1_core_modules_actions']['REF_TABLE']  = "core_modules";
$DB_ARRAY['TABLES']['core_modules_actions']['FK']['fk1_core_modules_actions']['REF_COLUMN'] = "id";
$DB_ARRAY['TABLES']['core_modules_actions']['FK']['fk1_core_modules_actions']['ON_DELETE']  = "CASCADE";
$DB_ARRAY['TABLES']['core_modules_actions']['FK']['fk1_core_modules_actions']['ON_UPDATE']  = "CASCADE";

//Table Key array
$DB_ARRAY['TABLES']['core_modules_actions']['KEY'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['m_id'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['m_id']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['m_id']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['m_id']['COLUMNS']['module_id'] = "0";
$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['m_id']['COLUMNS']['name']    = "1";

$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['idx1_core_modules_actions'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['idx1_core_modules_actions']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_modules_actions']['KEY']['idx1_core_modules_actions']['COLUMNS']['is_active_sw'] = "0";


//TABLE: core_modules_available
$DB_ARRAY['TABLES']['core_modules_available'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_modules_available']['ENGINE']  = "InnoDB";
//Primary Key for core_modules_available
$DB_ARRAY['TABLES']['core_modules_available']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['name'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['name']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['name']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['name']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['name']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['title'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['title']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['title']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['title']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['title']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['version'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['version']['TYPE']    = "varchar(10)";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['version']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['version']['DEFAULT'] = "1.0.0";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['version']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['descriprion'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['descriprion']['TYPE']    = "varchar(128)";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['descriprion']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['descriprion']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['descriprion']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['install_info'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['install_info']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['install_info']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['install_info']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['install_info']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['readme'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['readme']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['readme']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['readme']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['readme']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['data'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['data']['TYPE']    = "longblob";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['data']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['data']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['data']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['files_hash'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['files_hash']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['files_hash']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['files_hash']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['files_hash']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_modules_available']['COLUMNS']['lastuser']['EXTRA']   = "";


//Table Key array
$DB_ARRAY['TABLES']['core_modules_available']['KEY'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['KEY']['idx1_core_modules_available'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['KEY']['idx1_core_modules_available']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_modules_available']['KEY']['idx1_core_modules_available']['COLUMNS']['lastuser'] = "0";


//TABLE: core_roles
$DB_ARRAY['TABLES']['core_roles'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_roles']['ENGINE']  = "InnoDB";
//Primary Key for core_roles
$DB_ARRAY['TABLES']['core_roles']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_roles']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['name'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['name']['TYPE']    = "varchar(255)";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['name']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['name']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['name']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['description'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['description']['TYPE']    = "varchar(255)";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['description']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['description']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['description']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastuser']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['author'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['author']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['author']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['author']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['author']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['position'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['position']['TYPE']    = "int(11)";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['position']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['position']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['position']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['is_active_sw'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['is_active_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['is_active_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['is_active_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['is_active_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastupdate']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastupdate']['DEFAULT'] = "CURRENT_TIMESTAMP";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['lastupdate']['EXTRA']   = "on update CURRENT_TIMESTAMP";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['date_created'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['date_created']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['date_created']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['date_created']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['date_created']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access_add'] = array();
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access_add']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access_add']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access_add']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_roles']['COLUMNS']['access_add']['EXTRA']   = "";


//Table Key array
$DB_ARRAY['TABLES']['core_roles']['KEY'] = array();
$DB_ARRAY['TABLES']['core_roles']['KEY']['idx1_core_roles'] = array();
$DB_ARRAY['TABLES']['core_roles']['KEY']['idx1_core_roles']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_roles']['KEY']['idx1_core_roles']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_roles']['KEY']['idx1_core_roles']['COLUMNS']['name']    = "0";

$DB_ARRAY['TABLES']['core_roles']['KEY']['idx2_core_roles'] = array();
$DB_ARRAY['TABLES']['core_roles']['KEY']['idx2_core_roles']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_roles']['KEY']['idx2_core_roles']['COLUMNS']['is_active_sw'] = "0";


//TABLE: core_session
$DB_ARRAY['TABLES']['core_session'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_session']['ENGINE']  = "MyISAM";
//Primary Key for core_session
$DB_ARRAY['TABLES']['core_session']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_session']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['sid'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['sid']['TYPE']    = "varchar(128)";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['sid']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['sid']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['sid']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['login_time'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['login_time']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['login_time']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['login_time']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['login_time']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['logout_time'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['logout_time']['TYPE']    = "datetime";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['logout_time']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['logout_time']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['logout_time']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['user_id'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['user_id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['user_id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['user_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['user_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['ip'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['ip']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['ip']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['ip']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['ip']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['is_expired_sw'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['is_expired_sw']['TYPE']    = "enum('N','Y')";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['is_expired_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['is_expired_sw']['DEFAULT'] = "N";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['is_expired_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['last_activity'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['last_activity']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['last_activity']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['last_activity']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['last_activity']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_session']['COLUMNS']['crypto_sw'] = array();
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['crypto_sw']['TYPE']    = "enum('N','Y')";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['crypto_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['crypto_sw']['DEFAULT'] = "N";
$DB_ARRAY['TABLES']['core_session']['COLUMNS']['crypto_sw']['EXTRA']   = "";


//Table Key array
$DB_ARRAY['TABLES']['core_session']['KEY'] = array();
$DB_ARRAY['TABLES']['core_session']['KEY']['idx1_core_session'] = array();
$DB_ARRAY['TABLES']['core_session']['KEY']['idx1_core_session']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_session']['KEY']['idx1_core_session']['COLUMNS']['user_id'] = "0";

$DB_ARRAY['TABLES']['core_session']['KEY']['idx2_core_session'] = array();
$DB_ARRAY['TABLES']['core_session']['KEY']['idx2_core_session']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_session']['KEY']['idx2_core_session']['COLUMNS']['sid']     = "0";


//TABLE: core_settings
$DB_ARRAY['TABLES']['core_settings'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_settings']['ENGINE']  = "InnoDB";
//Primary Key for core_settings
$DB_ARRAY['TABLES']['core_settings']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_settings']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['code'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['code']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['code']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['code']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['code']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['description'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['description']['TYPE']    = "varchar(255)";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['description']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['description']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['description']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['value'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['value']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['value']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['value']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['value']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_type'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_type']['TYPE']    = "varchar(20)";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_type']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_type']['DEFAULT'] = "text";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_type']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_group'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_group']['TYPE']    = "enum('system','extra','personal')";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_group']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_group']['DEFAULT'] = "extra";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['data_group']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['is_active_sw'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['is_active_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['is_active_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['is_active_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['is_active_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastuser']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastupdate']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastupdate']['DEFAULT'] = "CURRENT_TIMESTAMP";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['lastupdate']['EXTRA']   = "on update CURRENT_TIMESTAMP";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['date_created'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['date_created']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['date_created']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['date_created']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['date_created']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['seq'] = array();
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['seq']['TYPE']    = "int(11)";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['seq']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['seq']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_settings']['COLUMNS']['seq']['EXTRA']   = "";


//Table Key array
$DB_ARRAY['TABLES']['core_settings']['KEY'] = array();
$DB_ARRAY['TABLES']['core_settings']['KEY']['idx1_core_settings'] = array();
$DB_ARRAY['TABLES']['core_settings']['KEY']['idx1_core_settings']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_settings']['KEY']['idx1_core_settings']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_settings']['KEY']['idx1_core_settings']['COLUMNS']['code']    = "0";


//TABLE: core_users
$DB_ARRAY['TABLES']['core_users'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_users']['ENGINE']  = "InnoDB";
//Primary Key for core_users
$DB_ARRAY['TABLES']['core_users']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_users']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['role_id'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['role_id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['role_id']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['role_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['role_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['login'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['login']['TYPE']    = "varchar(120)";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['login']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['login']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['login']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['email'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['email']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['email']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['email']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['email']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass']['TYPE']    = "varchar(36)";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_token'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_token']['TYPE']    = "varchar(255)";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_token']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_token']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_token']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_date'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_date']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_date']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_date']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['pass_reset_date']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['firstname'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['firstname']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['firstname']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['firstname']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['firstname']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastname'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastname']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastname']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastname']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastname']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['middlename'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['middlename']['TYPE']    = "varchar(60)";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['middlename']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['middlename']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['middlename']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['certificate'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['certificate']['TYPE']    = "text";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['certificate']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['certificate']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['certificate']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_active_sw'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_active_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_active_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_active_sw']['DEFAULT'] = "Y";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_active_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_email_wrong_sw'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_email_wrong_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_email_wrong_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_email_wrong_sw']['DEFAULT'] = "N";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_email_wrong_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_pass_changed_sw'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_pass_changed_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_pass_changed_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_pass_changed_sw']['DEFAULT'] = "N";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_pass_changed_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_admin_sw'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_admin_sw']['TYPE']    = "enum('Y','N')";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_admin_sw']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_admin_sw']['DEFAULT'] = "N";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['is_admin_sw']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastuser']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastupdate'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastupdate']['TYPE']    = "timestamp";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastupdate']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastupdate']['DEFAULT'] = "CURRENT_TIMESTAMP";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['lastupdate']['EXTRA']   = "on update CURRENT_TIMESTAMP";

$DB_ARRAY['TABLES']['core_users']['COLUMNS']['date_created'] = array();
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['date_created']['TYPE']    = "datetime";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['date_created']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['date_created']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users']['COLUMNS']['date_created']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users']['FK'] = array();

$DB_ARRAY['TABLES']['core_users']['FK']['fk1_core_users']['COL_NAME']   = "role_id";
$DB_ARRAY['TABLES']['core_users']['FK']['fk1_core_users']['REF_TABLE']  = "core_roles";
$DB_ARRAY['TABLES']['core_users']['FK']['fk1_core_users']['REF_COLUMN'] = "id";
$DB_ARRAY['TABLES']['core_users']['FK']['fk1_core_users']['ON_DELETE']  = "SET NULL";
$DB_ARRAY['TABLES']['core_users']['FK']['fk1_core_users']['ON_UPDATE']  = "CASCADE";

//Table Key array
$DB_ARRAY['TABLES']['core_users']['KEY'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx1_core_users'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx1_core_users']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_users']['KEY']['idx1_core_users']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx1_core_users']['COLUMNS']['login']   = "0";

$DB_ARRAY['TABLES']['core_users']['KEY']['idx2_core_users'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx2_core_users']['TYPE']    = "UNIQ";
$DB_ARRAY['TABLES']['core_users']['KEY']['idx2_core_users']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx2_core_users']['COLUMNS']['email']   = "0";

$DB_ARRAY['TABLES']['core_users']['KEY']['idx3_core_users'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx3_core_users']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx3_core_users']['COLUMNS']['is_active_sw'] = "0";

$DB_ARRAY['TABLES']['core_users']['KEY']['idx4_core_users'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx4_core_users']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_users']['KEY']['idx4_core_users']['COLUMNS']['role_id'] = "0";


//TABLE: core_users_roles
$DB_ARRAY['TABLES']['core_users_roles'] = array();
//Table Enginge Definition
$DB_ARRAY['TABLES']['core_users_roles']['ENGINE']  = "InnoDB";
//Primary Key for core_users_roles
$DB_ARRAY['TABLES']['core_users_roles']['PRIMARY_KEY'] = "id";
//Define array for columns
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS'] = array();

$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['id'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['id']['EXTRA']   = "auto_increment";

$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['user_id'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['user_id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['user_id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['user_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['user_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['role_id'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['role_id']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['role_id']['NULL']    = "NO";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['role_id']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['role_id']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['lastuser'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['lastuser']['TYPE']    = "int(11) unsigned";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['lastuser']['NULL']    = "YES";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['lastuser']['DEFAULT'] = "";
$DB_ARRAY['TABLES']['core_users_roles']['COLUMNS']['lastuser']['EXTRA']   = "";

$DB_ARRAY['TABLES']['core_users_roles']['FK'] = array();

$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk2_core_users_roles']['COL_NAME']   = "role_id";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk2_core_users_roles']['REF_TABLE']  = "core_roles";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk2_core_users_roles']['REF_COLUMN'] = "id";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk2_core_users_roles']['ON_DELETE']  = "CASCADE";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk2_core_users_roles']['ON_UPDATE']  = "CASCADE";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk1_core_users_roles']['COL_NAME']   = "user_id";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk1_core_users_roles']['REF_TABLE']  = "core_users";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk1_core_users_roles']['REF_COLUMN'] = "id";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk1_core_users_roles']['ON_DELETE']  = "CASCADE";
$DB_ARRAY['TABLES']['core_users_roles']['FK']['fk1_core_users_roles']['ON_UPDATE']  = "CASCADE";

//Table Key array
$DB_ARRAY['TABLES']['core_users_roles']['KEY'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['KEY']['idx1_core_users_roles'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['KEY']['idx1_core_users_roles']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['KEY']['idx1_core_users_roles']['COLUMNS']['user_id'] = "0";

$DB_ARRAY['TABLES']['core_users_roles']['KEY']['idx2_core_users_roles'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['KEY']['idx2_core_users_roles']['COLUMNS'] = array();
$DB_ARRAY['TABLES']['core_users_roles']['KEY']['idx2_core_users_roles']['COLUMNS']['role_id'] = "0";