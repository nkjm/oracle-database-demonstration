<?php

/***
**** Essential Configuration
***/

// DB Login Information
define("DB_HOSTNAME", '');
define("DB_SCANNAME", '');
define("DB_USER", '');
define("DB_PASSWORD", '');
define("DB_SERVICE", '');

// ASM Login Information
define("ASM_HOSTNAME", '');
define("ASM_USER", '');
define("ASM_PASSWORD", '');
define("ASM_SERVICE", '');

// Default Diskgroup
define("DEFAULT_DG", 'DATA');

// Default Storage Quota
define("DEFAULT_QUOTA", "10G");

// User's Prefix
define("CUSTOMER_PREFIX", "CLOUD_");

// Roll to be applied for User
define("CLOUD_USER", "CLOUD_USER");

// CPU Speed (Consumer Group)
define("RESOURCE_PLAN", "CLOUD");
define("DEFAULT_CONSUMER_GROUP", "MID");
$array_consumer_group = array("HIGH", "MID", "LOW");
$array_cpu_utilization_limit = array("HIGH" => "100", "MID" => "50", "LOW" => "10");
$array_compression = array('on', 'off');

// Undo Retention for Snapshots
define("SNAPSHOT_RETENTION", '604800');


/***
**** Pluggable Features
***/

// Site Syncronization
define("SS", FALSE); // TRUE or FALSE
if (SS == TRUE) {
define("SS_MAIN_SITENAME", "");
define("SS_MAIN_DB_HOSTNAME", "");
define("SS_MAIN_DB_SERVICE", "");
define("SS_MAIN_DB_UNIQUE_NAME", "");
define("SS_MAIN_ASM_HOSTNAME", "");
define("SS_MAIN_ASM_SERVICE", "");

define("SS_BACKUP_SITENAME", "");
define("SS_BACKUP_DB_HOSTNAME", "");
define("SS_BACKUP_DB_SERVICE", "");
define("SS_BACKUP_DB_UNIQUE_NAME", "");
define("SS_BACKUP_ASM_HOSTNAME", "");
define("SS_BACKUP_ASM_SERVICE", "");

define("SS_LOG_ARCHIVE_DEST_ID", '2');
$array_ss_protection_mode = array('MAXIMUM AVAILABILITY', 'MAXIMUM PERFORMANCE');
$array_ss_compression = array('ENABLE', 'DISABLE');
}

define("SS_ORACLE_HOME", '/u01/base/db');


// Error
define("ERROR", "ERROR");
