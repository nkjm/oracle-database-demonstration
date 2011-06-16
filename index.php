<?php

/*
 * This software cannot be modified, embedded or redistributed without the author's permision.
 * Copyright Kazuki Nakajima <nkjm.kzk@gmail.com>
 */

set_time_limit(300);
ini_set('display_errors', 'Off');
ini_set('date.timezone', 'Asia/Tokyo');
ini_set('oci8.privileged_connect', 'On');
ini_set('oci8.statement_cache_size', 0);
ini_set('oci8.old_oci_close_semantics', 'Off');
//putenv("TNS_ADMIN=/u01/base/database/network/admin");

require_once './Role.php';
require_once './Resource.php';
require_once './config.php';
require_once './Error.php';
require_once './Parse.php';
require_once './Drive.php';
require_once './Customer.php';
require_once './Site.php';
require_once './Flash.php';
require_once './Snapshot.php';
$error = new Error();
$parse = new Parse();


/***
**** Connect to Database
***/

if (SS == TRUE) {
    // In case of Site Syncronization enabled
    $ss_conn_db_main = oci_connect(DB_USER, DB_PASSWORD, '//' . SS_MAIN_DB_HOSTNAME . '/' . SS_MAIN_DB_SERVICE, '', OCI_SYSDBA);
    if ($ss_conn_db_main == FALSE) {
        $error->set_msg("Failed to connect to Main Database.");
        $error->skip = TRUE;
    }

    $ss_conn_db_backup = oci_connect(DB_USER, DB_PASSWORD, '//' . SS_BACKUP_DB_HOSTNAME . '/' . SS_BACKUP_DB_SERVICE, '', OCI_SYSDBA);
    if ($ss_conn_db_backup == FALSE) {
        $error->set_msg("Failed to connect to Backup Database.");
        $error->skip = TRUE;
    }

    $site = new Site();
    if ($site->is_active_site($ss_conn_db_main)) {
        $ss_active_sitename = SS_MAIN_SITENAME;
        $conn_db = $ss_conn_db_main;
        $conn_db_inactive = $ss_conn_db_backup;
    } elseif ($site->is_active_site($ss_conn_db_backup)) {
        $ss_active_sitename = SS_BACKUP_SITENAME;
        $conn_db = $ss_conn_db_backup;
        $conn_db_inactive = $ss_conn_db_main;
    }

    if ($ss_active_sitename == SS_MAIN_SITENAME) {
        $ss_active_db_hostname =    SS_MAIN_DB_HOSTNAME;
        $ss_active_db_service =    SS_MAIN_DB_SERVICE;
        $ss_active_db_unique_name =    SS_MAIN_DB_UNIQUE_NAME;
        $ss_inactive_db_hostname =    SS_BACKUP_DB_HOSTNAME;
        $ss_inactive_db_service =   SS_BACKUP_DB_SERVICE;
        $ss_inactive_db_unique_name =   SS_BACKUP_DB_UNIQUE_NAME;
    } elseif ($ss_active_sitename == SS_BACKUP_SITENAME) {
        $ss_active_db_hostname =    SS_BACKUP_DB_HOSTNAME;
        $ss_active_db_service =   SS_BACKUP_DB_SERVICE;
        $ss_active_db_unique_name =    SS_BACKUP_DB_UNIQUE_NAME;
        $ss_inactive_db_hostname =    SS_MAIN_DB_HOSTNAME;
        $ss_inactive_db_service =    SS_MAIN_DB_SERVICE;
        $ss_inactive_db_unique_name =   SS_MAIN_DB_UNIQUE_NAME;
    }
} else {
    // In case of Site Syncronization disabled
    $conn_db = oci_connect(DB_USER, DB_PASSWORD, '//' . DB_HOSTNAME . '/' . DB_SERVICE, '', OCI_SYSDBA);
    if ($conn_db == FALSE) {
        $error->set_msg("Failed to connect to Database.");
        $error->skip = TRUE;
    }
}

if ($error->skip == TRUE) {
    goto start_html;
}


/***
**** Connect to ASM
***/

if (SS == TRUE) {
    $ss_conn_asm_main = oci_connect(ASM_USER, ASM_PASSWORD, '//' . SS_MAIN_ASM_HOSTNAME . '/' . SS_MAIN_ASM_SERVICE, '', OCI_SYSASM);
    if ($ss_conn_asm_main == FALSE) {
        $error->set_msg("Failed to connect to Main ASM.");
        $error->skip = TRUE;
    }

    $ss_conn_asm_backup = oci_connect(ASM_USER, ASM_PASSWORD, '//' . SS_BACKUP_ASM_HOSTNAME . '/' . SS_BACKUP_ASM_SERVICE, '', OCI_SYSASM);
    if ($ss_conn_asm_backup == FALSE) {
        $error->set_msg("Failed to connect to Backup ASM.");
        $error->skip = TRUE;
    }

    if ($ss_active_sitename == SS_MAIN_SITENAME) {
        $conn_asm = $ss_conn_asm_main;
    } elseif ($ss_active_sitename == SS_BACKUP_SITENAME) {
        $conn_asm = $ss_conn_asm_backup;
    }
} else {
    $conn_asm = oci_connect(ASM_USER, ASM_PASSWORD, '//' . ASM_HOSTNAME . '/' . ASM_SERVICE, '', OCI_SYSASM);
    if ($conn_asm == FALSE) {
        $error->set_msg("Failed to connect to ASM.");
        $error->skip = TRUE;
    }
}

if ($error->skip == TRUE) {
    goto start_html;
}


/***
**** Check if Database has been setup for Oracle Database Demonstration
***/

$role = new Role();
$resource = new Resource();
$snapshot = new Snapshot();

// Check if Role has been created.
$result = $role->exist($conn_db, CLOUD_USER);
if ($result == FALSE) {
    echo "<div id='flag_role_required' style='display:none;'>TRUE</div>\n";
} 

// Check if Consumer Groups have been created.
foreach ($array_consumer_group as $consumer_group) {
    $result = $resource->exist_consumer_group($conn_db, $consumer_group);
    if ($result == FALSE) {
        echo "<div id='flag_consumer_group_required' style='display:none;'>TRUE</div>\n";
    }
}

// Check if Plan has been created.
$result = $resource->exist_resource_plan($conn_db, RESOURCE_PLAN);
if ($result == FALSE) {
    echo "<div id='flag_resource_plan_required' style='display:none;'>TRUE</div>\n";
}

// Check if Plan has been enabled.
$result = $resource->status_resource_plan($conn_db, RESOURCE_PLAN);
if ($result == 'DISABLED') {
    echo "<div id='flag_resource_plan_disabled' style='display:none;'>TRUE</div>\n";
}

// Check if Undo Retention has been configured.
$result = $snapshot->fetch_retention($conn_db);
if ($result != SNAPSHOT_RETENTION) {
    echo "<div id='flag_snapshot_retention_dirty' style='display:none;'>TRUE</div>\n";
}

$drive = new Drive();
$storage = $drive->fetch_dg_detail($conn_asm, DEFAULT_DG);

start_html:

$error->check(FALSE, 'html');
require_once 'html/index.php';
?>

