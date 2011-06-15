<?php

/*
 * This software cannot be modified, embedded or redistributed without the author's permision.
 * Copyright Kazuki Nakajima <nkjm.kzk@gmail.com>
 */

set_time_limit(300);
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

// In case SS enabled
if (SS == TRUE) {
    $ss_conn_db_main = oci_connect(DB_USER, DB_PASSWORD, '//' . SS_MAIN_DB_HOSTNAME . '/' . SS_MAIN_DB_SERVICE, '', OCI_SYSDBA);
    if ($ss_conn_db_main == FALSE) {
        $error->set_msg("Failed to connect to Main Database.");
    }
    $error->check();

    $ss_conn_db_backup = oci_connect(DB_USER, DB_PASSWORD, '//' . SS_BACKUP_DB_HOSTNAME . '/' . SS_BACKUP_DB_SERVICE, '', OCI_SYSDBA);
    if ($ss_conn_db_backup == FALSE) {
        $error->set_msg("Failed to connect to Backup Database.");
    }
    $error->check();

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
    $conn_db = oci_connect(DB_USER, DB_PASSWORD, '//' . DB_HOSTNAME . '/' . DB_SERVICE, '', OCI_SYSDBA);
    if ($conn_db == FALSE) {
        $error->set_msg("Failed to connect to Database.");
    }
    $error->check();
}


/***
**** Connect to ASM
***/

if (SS == TRUE) {
    $ss_conn_asm_main = oci_connect(ASM_USER, ASM_PASSWORD, '//' . SS_MAIN_ASM_HOSTNAME . '/' . SS_MAIN_ASM_SERVICE, '', OCI_SYSASM);
    if ($ss_conn_asm_main == FALSE) {
        $error->set_msg("Failed to connect to Main ASM.");
    }
    $error->check();
    $ss_conn_asm_backup = oci_connect(ASM_USER, ASM_PASSWORD, '//' . SS_BACKUP_ASM_HOSTNAME . '/' . SS_BACKUP_ASM_SERVICE, '', OCI_SYSASM);
    if ($ss_conn_asm_backup == FALSE) {
        $error->set_msg("Failed to connect to Backup ASM.");
    }
    $error->check();
    if ($ss_active_sitename == SS_MAIN_SITENAME) {
        $conn_asm = $ss_conn_asm_main;
    } elseif ($ss_active_sitename == SS_BACKUP_SITENAME) {
        $conn_asm = $ss_conn_asm_backup;
    }
} else {
    $conn_asm = oci_connect(ASM_USER, ASM_PASSWORD, '//' . ASM_HOSTNAME . '/' . ASM_SERVICE, '', OCI_SYSASM);
    if ($conn_asm == FALSE) {
        $error->set_msg("Failed to connect to ASM.");
    }
    $error->check();
}


/***
**** Check if Database has been setup for Oracle Database Demonstration
***/

$role = new Role();
$resource = new Resource();
$snapshot = new Snapshot();

// Check if Role has been created.
$flag_role_required = FALSE;
$result = $role->exist($conn_db, CLOUD_USER);
if ($result == FALSE) {
    $flag_role_required = TRUE;
} 

// Check if Consumer Groups have been created.
$flag_consumer_group_required = FALSE;
foreach ($array_consumer_group as $consumer_group) {
    $result = $resource->exist_consumer_group($conn_db, $consumer_group);
    if ($result == FALSE) {
        $flag_consumer_group_required = TRUE;
    }
}

// Check if Plan has been created.
$flag_resource_plan_required = FALSE;
$result = $resource->exist_resource_plan($conn_db, RESOURCE_PLAN);
if ($result == FALSE) {
    $flag_resource_plan_required = TRUE;
}

// Check if Plan has been enabled.
$flag_resource_plan_disabled = FALSE;
$result = $resource->status_resource_plan($conn_db, RESOURCE_PLAN);
if ($result == 'DISABLED') {
    $flag_resource_plan_disabled = TRUE;
}

// Check if Undo Retention has been configured.
$flag_snapshot_retention_dirty = FALSE;
$result = $snapshot->fetch_retention($conn_db);
if ($result != SNAPSHOT_RETENTION) {
    $flag_snapshot_retention_dirty = TRUE;
}


/***
 *** Sanitize
 ***/

if (isset($_REQUEST["customer_id"])) {
    $customer_id = $parse->id($_REQUEST["customer_id"]);
    $error->check();
}
if (isset($_REQUEST["customer_name"])) {
    $customer_name = $parse->id($_REQUEST["customer_name"]);
    $error->check();
}
if (isset($_REQUEST["customer_password"])) {
    $customer_password = $parse->password($_REQUEST["customer_password"]);
    $error->check();
}
if (isset($_REQUEST["consumer_group"])) {
    $consumer_group = $parse->select($_REQUEST["consumer_group"], $array_consumer_group);
    $error->check();
}
if (isset($_REQUEST["max_gbytes"])) {
    $max_gbytes = $parse->max_gbytes($_REQUEST["max_gbytes"]);
    $error->check();
}
if (isset($_REQUEST["compression"])) {
    $compression = $parse->select($_REQUEST["compression"], $array_compression);
    $error->check();
}
if (isset($_REQUEST["disk_path"])) {
    $disk_path = $parse->disk_path($_REQUEST["disk_path"]);
    $error->check();
}
if (isset($_REQUEST["ss_protection_mode"])) {
    $ss_protection_mode = $parse->select($_REQUEST["ss_protection_mode"], $array_ss_protection_mode);
    $error->check();
}
if (isset($_REQUEST["ss_compression"])) {
    $ss_compression = $parse->select($_REQUEST["ss_compression"], $array_ss_compression);
    $error->check();
}
if (isset($_REQUEST["fc_db_flash_cache_size"])) {
    $fc_db_flash_cache_size = $parse->fc_db_flash_cache_size($_REQUEST["fc_db_flash_cache_size"]);
    $error->check();
}
if (isset($_REQUEST["timestamp"])) {
    $timestamp = $parse->timestamp($_REQUEST["timestamp"]);
    $error->check();
}
if (isset($_REQUEST["op"])) {
    $op = $_REQUEST["op"];
} else {
    $op = null;
}


/***
 *** Operation
 ***/

switch ($op) {
    case 'initialize':
        $role = new Role();
        $resource = new Resource();

        // Create Role.
        if ($flag_role_required == TRUE) {
            $result = $role->create($conn_db, CLOUD_USER);
            $error->check();
        }
         
        // Create Consumer Groups.
        if ($flag_consumer_group_required == TRUE) {
            foreach ($array_consumer_group as $consumer_group) {
                $result = $resource->create_consumer_group($conn_db, $consumer_group);
                $error->check();
            }
        }
        
        // Create Resource Plan.
        if ($flag_resource_plan_required == TRUE) {
            $result = $resource->create_resource_plan($conn_db, RESOURCE_PLAN, $array_consumer_group, $array_cpu_utilization_limit);
            $error->check();
        }

        // Enable Resource Plan.
        if ($flag_resource_plan_disabled == TRUE) {
            $result = $resource->enable_resource_plan($conn_db, RESOURCE_PLAN);
            $error->check();
        }

        // Configure Undo Retention for Snapshots..
        if ($flag_snapshot_retention_dirty == TRUE) {
            $result = $snapshot->set_retention($conn_db, SNAPSHOT_RETENTION);
            $error->check();
        }
        break;
    case 'add_storage':
        $drive = new Drive();
        $result = $drive->add_disk($conn_asm, DEFAULT_DG, $disk_path);
        $error->check();
        break;
    case 'remove_storage':
        $drive = new Drive();
        $result = $drive->delete_disk($conn_asm, DEFAULT_DG, $disk_path);
        $error->check();
        break;
    case 'fc_update_db_flash_cache_size':
        $flash = new Flash();
        $result = $flash->update_db_flash_cache_size($conn_db, $fc_db_flash_cache_size);
        $error->check();
        break;
    case 'ss_switchover':
        oci_close($ss_conn_db_main);
        oci_close($ss_conn_db_backup);
        oci_close($ss_conn_asm_main);
        oci_close($ss_conn_asm_backup);
        $result = $site->switchover(SS_ORACLE_HOME, DB_USER, DB_PASSWORD, $ss_active_db_hostname, $ss_inactive_db_hostname, $ss_active_db_service, $ss_inactive_db_service, $ss_active_db_unique_name, $ss_inactive_db_unique_name, SS_LOG_ARCHIVE_DEST_ID);
        $error->check();
        break;
    case 'ss_update_protection_mode':
        $ss_compression = $site->fetch_compression($conn_db, SS_LOG_ARCHIVE_DEST_ID);
        $result = $site->update_protection_mode($conn_db, $ss_protection_mode, $ss_inactive_db_unique_name, $ss_inactive_db_unique_name, $ss_compression, SS_LOG_ARCHIVE_DEST_ID);
        $error->check();
        break;
    case 'ss_update_compression':
        $ss_protection_mode = $site->fetch_protection_mode($conn_db);
        $result = $site->update_protection_mode($conn_db, $ss_protection_mode, $ss_inactive_db_unique_name, $ss_inactive_db_unique_name, $ss_compression, SS_LOG_ARCHIVE_DEST_ID);
        $error->check();
        break;
    case 'create_customer':
        $customer = new Customer();
        $customer_id = CUSTOMER_PREFIX . $customer_name;
        $result = $customer->create($conn_asm, $conn_db, $customer_id, $customer_password);
        $error->check();
        break;
    case 'exist_customer':
        $customer = new Customer();
        $result = $customer->check($conn_asm, $conn_db, $customer_id);
        $error->check();
        break;
    case 'delete_customer':
        $customer = new Customer();
        $result = $customer->delete($conn_asm, $conn_db, $customer_id);
        $error->check();
        break;
    case 'update_consumer_group':
        $customer = new Customer();
        $result = $customer->update_consumer_group($conn_db, $customer_id, $consumer_group);
        $error->check();
        break;
    case 'update_storage_quota':
        $drive = new Drive();
        $result = $drive->update_storage_quota($conn_db, $customer_id, $max_gbytes);
        $error->check();
        break;
    case 'update_compression':
        $drive = new Drive();
        $result = $drive->update_compression($conn_db, $customer_id, $compression);
        $error->check();
        break;
    case 'rollback_customer':
        $customer = new Customer();
        $result = $customer->rollback($conn_db, $customer_id, $timestamp);
        $error->check();
        break;
}
$error->flush();
?>

