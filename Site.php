<?php
class Site {
    public $err_msg = array();

    public function is_active_site($conn_db) {
        $sql = 'select database_role from v$database';
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        $row = oci_fetch_array($state_id, OCI_BOTH);
        if ($row['DATABASE_ROLE'] == 'PRIMARY'){
            return(TRUE);
        } else {
            return(FALSE);
        }
    }

    public function fetch_protection_mode($conn_db) {
        $sql = 'select protection_mode from v$database';
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        $row = oci_fetch_array($state_id, OCI_BOTH);
        return($row['PROTECTION_MODE']); // MAXIMUM AVAILABILITY or MAXIMUM PERFORMANCE
    }

    public function fetch_compression($conn_db, $log_archive_dest_id) {
        $sql = "select compression from v\$archive_dest where dest_id = " . $log_archive_dest_id;
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        $row = oci_fetch_array($state_id, OCI_BOTH);
        return($row['COMPRESSION']); // ENABLE or DISABLE
    }

    public function switchover($oracle_home, $db_user, $db_password, $active_db_hostname, $inactive_db_hostname, $active_db_service, $inactive_db_service, $active_db_unique_name, $inactive_db_unique_name, $log_archive_dest_id) {
        // X-ACTIVE: Fetch all instance in case of RAC
        $active_db_conn = oci_new_connect($db_user, $db_password, '//' . $active_db_hostname . '/' . $active_db_service, '', OCI_SYSDBA);
        if ($active_db_conn == FALSE) {
            array_push($this->err_msg, "Failed to connect to Database.");
            return(FALSE);
        }
        $sql = 'select inst_name from v$active_instances';
        $state_id = oci_parse($active_db_conn, $sql);
        $result = oci_execute($state_id);
        $array_active_db_nodes = array();
        $array_active_db_instances = array();
        while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
            $array_tmp = explode(':', $row['INST_NAME']);
            array_push($array_active_db_nodes, trim($array_tmp[0]));
            array_push($array_active_db_instances, trim($array_tmp[1]));
        }
        oci_close($active_db_conn);

        // X-INACTIVE: Fetch all instance in case of RAC
        $inactive_db_conn = oci_new_connect($db_user, $db_password, '//' . $inactive_db_hostname . '/' . $inactive_db_service, '', OCI_SYSDBA);
        if ($inactive_db_conn == FALSE) {
            array_push($this->err_msg, "Failed to connect to Database.");
            return(FALSE);
        }
        $sql = 'select inst_name from v$active_instances';
        $state_id = oci_parse($inactive_db_conn, $sql);
        $result = oci_execute($state_id);
        $array_inactive_db_nodes = array();
        $array_inactive_db_instances = array();
        while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
            $array_tmp = explode(':', $row['INST_NAME']);
            array_push($array_inactive_db_nodes, trim($array_tmp[0]));
            array_push($array_inactive_db_instances, trim($array_tmp[1]));
        }
        oci_close($inactive_db_conn);

        // Check there is no redo log gap
        $active_db_conn = oci_new_connect($db_user, $db_password, '//' . $active_db_hostname . '/' . $active_db_service, '', OCI_SYSDBA);
        if ($active_db_conn == FALSE) {
            array_push($this->err_msg, "Failed to connect to Database.");
            return(FALSE);
        }
        $sql = 'select status, gap_status from v$archive_dest_status where dest_id = ' . $log_archive_dest_id;
        $state_id = oci_parse($active_db_conn, $sql);
        $result = oci_execute($state_id);
        $row = oci_fetch_array($state_id, OCI_BOTH);
        if ($row['STATUS'] != 'VALID' || $row['GAP_STATUS'] != 'NO GAP') {
            array_push($this->err_msg, "There is Redo log GAP.");
            return(FALSE);
        }
        oci_close($active_db_conn);

        // Now X-Active Database is "TO STANDBY"
        if (self::check_switchover_status($db_user, $db_password, $active_db_hostname, $active_db_service, "TO STANDBY", 1, 1) == FALSE) {
            return(FALSE);
        }
        // Now X-InActive Database is "NOT ALLOWED"
        if (self::check_switchover_status($db_user, $db_password, $inactive_db_hostname, $inactive_db_service, "NOT ALLOWED", 1, 1) == FALSE) {
            return(FALSE);
        }


        // X-ACTIVE: In case of RAC, shutdown all instances except #1 instance
        if (count($array_active_db_nodes) > 1) { 
            echo "Shutting down all instances except #1.<br />\n";
            foreach ($array_active_db_nodes as $k => $active_db_node) {
                if ($k == 0) {
                    continue;
                }
                $cmd = 'ORACLE_HOME=' . $oracle_home . ' LD_LIBRARY_PATH=' . $oracle_home . '/lib python/shutdown.py ' . $db_user . " " . $db_password . " " . $active_db_node . " " . $active_db_service;
                system($cmd, $result);
                if ($result == FALSE) {
                    array_push($this->err_msg, "Failed Command = " . $cmd);
                    return(FALSE);
                }
            }
        }

        // X-ACTIVE: Transition to Standby
        echo "Primary Database is trasitioning to Standby.<br />\n";
        $active_db_conn = oci_new_connect($db_user, $db_password, '//' . $active_db_hostname . '/' . $active_db_service, '', OCI_SYSDBA);
        if ($active_db_conn == FALSE) {
            array_push($this->err_msg, "Failed to connect to Database.");
            return(FALSE);
        }
        $sql = "alter database commit to switchover to physical standby with session shutdown";
        $state_id = oci_parse($active_db_conn, $sql);
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "Failed SQL = " . $sql);
            return(FALSE);
        }
        oci_close($active_db_conn);

/*
        // NOW X-Active Database is "RECOVERY NEEDED"
        if (self::check_switchover_status($db_user, $db_password, $active_db_hostname, $active_db_service, "RECOVERY NEEDED", 1, 30) == FALSE) {
            return(FALSE);
        }
        // NOW X-InActive Database is "TO PRIMARY"
        if (self::check_switchover_status($db_user, $db_password, $inactive_db_hostname, $inactive_db_service, "TO PRIMARY", 1, 60) == FALSE) {
            return(FALSE);
        }
*/
            
        // X-ACTIVE: restart x-active database
        echo "Restarting x-Active Database.<br />\n";
        if (count($array_active_db_nodes) > 1) { 
            // Shutdown #1 instance
            $cmd = 'ORACLE_HOME=' . $oracle_home . ' LD_LIBRARY_PATH=' . $oracle_home . '/lib python/shutdown.py ' . $db_user . " " . $db_password . " " . $active_db_hostname . " " . $active_db_service;
            system($cmd, $result);
            // Start all instance
            foreach ($array_active_db_nodes as $k => $active_db_node) {
                $cmd = 'ORACLE_HOME=' . $oracle_home . ' LD_LIBRARY_PATH=' . $oracle_home . '/lib python/start.py ' . $db_user . " " . $db_password . " " . $active_db_node . " " . $active_db_service;
                system($cmd, $result);
                if ($result == FALSE) {
                    array_push($this->err_msg, "Failed Command = " . $cmd);
                    return(FALSE);
                }
            }
        } else {
            // Restart
            $cmd = 'ORACLE_HOME=' . $oracle_home . ' LD_LIBRARY_PATH=' . $oracle_home . '/lib python/restart.py ' . $db_user . " " . $db_password . " " . $active_db_hostname . " " . $active_db_service;
            system($cmd, $result);
            if ($result == FALSE) {
                array_push($this->err_msg, "Failed Command = " . $cmd);
                return(FALSE);
            }
        }

        // NOW X-Active Database is "TO PRIMARY"
        if (self::check_switchover_status($db_user, $db_password, $active_db_hostname, $active_db_service, "TO PRIMARY", 1, 30) == FALSE) {
            return(FALSE);
        }
        // NOW X-InActive Database is "TO PRIMARY"
        // Skip checking as it is same to previous state.

        // X-INACTIVE: Transition to Primary
        echo "Standby Database is transitioning to Primary.<br />\n";
        $inactive_db_conn = oci_new_connect($db_user, $db_password, '//' . $inactive_db_hostname . '/' . $inactive_db_service, '', OCI_SYSDBA);
        if ($inactive_db_conn == FALSE) {
            array_push($this->err_msg, "Failed to connect to Database.");
            return(FALSE);
        }
        $sql = "alter database commit to switchover to primary with session shutdown";
        $state_id = oci_parse($inactive_db_conn, $sql);
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "Failed SQL = " . $sql);
            return(FALSE);
        }
        oci_close($inactive_db_conn);

/*
        // NOW X-Active Database is "TO PRIMARY"
        // Skip checking as it is same to previous state.

        // NOW X-InActive Database is "NOT ALLOWED"
        if (self::check_switchover_status($db_user, $db_password, $inactive_db_hostname, $inactive_db_service, "NOT ALLOWED", 1, 30) == FALSE) {
            return(FALSE);
        }
*/

        // X-INACTIVE: Transition to open
        echo "New Primary Database is transitioning its OPEN MODE from MOUNT to OPEN.<br />\n";
        if (count($array_inactive_db_nodes) > 1) { 
            foreach ($array_inactive_db_nodes as $k => $inactive_db_node) {
                $inactive_db_conn = oci_new_connect($db_user, $db_password, '//' . $inactive_db_node . '/' . $inactive_db_service, '', OCI_SYSDBA);
                if ($inactive_db_conn == FALSE) {
                    array_push($this->err_msg, "Failed to connect to Database.");
                    return(FALSE);
                }
                $sql = "alter database open";
                $state_id = oci_parse($inactive_db_conn, $sql);
                $result = oci_execute($state_id);
                if ($result == FALSE) {
                    array_push($this->err_msg, "Failed SQL = " . $sql);
                    return(FALSE);
                }
                oci_close($inactive_db_conn);
            }
        } else {
            $inactive_db_conn = oci_new_connect($db_user, $db_password, '//' . $inactive_db_hostname . '/' . $inactive_db_service, '', OCI_SYSDBA);
            if ($inactive_db_conn == FALSE) {
                array_push($this->err_msg, "Failed to connect to Database.");
                return(FALSE);
            }
            $sql = "alter database open";
            $state_id = oci_parse($inactive_db_conn, $sql);
            $result = oci_execute($state_id);
            if ($result == FALSE) {
                array_push($this->err_msg, "Failed SQL = " . $sql);
                return(FALSE);
            }
            oci_close($inactive_db_conn);
        }

/*
        // NOW X-Active Database is "RECOVERY NEEDED"
        if (self::check_switchover_status($db_user, $db_password, $active_db_hostname, $active_db_service, "RECOVERY NEEDED", 1, 30) == FALSE) {
            return(FALSE);
        }
        // NOW X-InActive Database is "TO STANDBY"
        if (self::check_switchover_status($db_user, $db_password, $inactive_db_hostname, $inactive_db_service, "TO STANDBY", 1, 30) == FALSE) {
            return(FALSE);
        }
*/

        // X-ACTIVE: Start Managed Recovery Mode
        echo "Replication is going to start.<br />\n";
        $active_db_conn = oci_new_connect($db_user, $db_password, '//' . $active_db_hostname . '/' . $active_db_service, '', OCI_SYSDBA);
        if ($active_db_conn == FALSE) {
            array_push($this->err_msg, "Failed to connect to Database.");
            return(FALSE);
        }
        $sql = "alter database recover managed standby database using current logfile disconnect";
        $state_id = oci_parse($active_db_conn, $sql);
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "Failed SQL = " . $sql);
            return(FALSE);
        }
        oci_close($active_db_conn);

        // NOW X-Active Database is "NOT ALLOWED"
        if (self::check_switchover_status($db_user, $db_password, $active_db_hostname, $active_db_service, "NOT ALLOWED", 1, 30) == FALSE) {
            return(FALSE);
        }
        // NOW X-InActive Database is "TO STANDBY"
        // Skip check as it is same to previous status.
    }

    public function update_protection_mode($conn_db, $protection_mode, $destination_service, $destination_unique_name, $compression, $log_archive_dest_id) {
        if ($protection_mode == 'MAXIMUM AVAILABILITY') {
            $sync = 'SYNC';
        } else {
            $sync = 'ASYNC';
        }
        $sql = "alter system set log_archive_dest_" . $log_archive_dest_id . "='service=\"" . $destination_service . "\" " . $sync . " VALID_FOR=(online_logfile,primary_role) db_unique_name=\"" . $destination_unique_name . "\" compression=" . $compression . "' scope=both";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);

        if ($protection_mode == 'MAXIMUM AVAILABILITY') {
            $protection_mode = 'MAXIMIZE AVAILABILITY';
        } else {
            $protection_mode = 'MAXIMIZE PERFORMANCE';
        }
        $sql = "alter database set standby database to " . $protection_mode;
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
    }

    public function update_compression($conn_db, $protection_mode, $destination_service, $destination_unique_name, $compression, $log_archive_dest_id) {
        if ($protection_mode == 'MAXIMUM AVAILABILITY') {
            $sync = 'SYNC';
        } else {
            $sync = 'ASYNC';
        }
        $sql = "alter system set log_archive_dest_" . $log_archive_dest_id . "='service=\"" . $destination_service . "\" " . $sync . " VALID_FOR=(online_logfile,primary_role) db_unique_name=\"" . $destination_unique_name . "\" compression=" . $compression . "' scope=both";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
    }

    public function check_switchover_status($db_user, $db_password, $db_hostname, $db_service, $expected_status, $interval, $limit) {
        for ($count = 0; $count < $limit; $count++) {
            $conn_db = oci_new_connect($db_user, $db_password, '//' . $db_hostname . '/' . $db_service, '', OCI_SYSDBA);
            $sql = 'select switchover_status from v$database';
            $state_id = oci_parse($conn_db, $sql);
            $result = oci_execute($state_id);
            if ($result != TRUE) {
                oci_close($conn_db);
                array_push($this->err_msg, "Failed SQL = '$sql'");
                sleep($interval);
                continue;
            }
            $row = oci_fetch_array($state_id, OCI_BOTH);
            if ($row[0] == $expected_status) {
                oci_close($conn_db);
                return(TRUE);
            } else {
                oci_close($conn_db);
                sleep($interval);
            }
        }
        oci_close($conn_db);
        array_push($this->err_msg, "Switchover status is not appropriate. Now in " . $row[0] . ". Should be " . $expected_status . ".");
        return(FALSE);
    }
}
?>
