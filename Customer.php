<?php
Class Customer {
    public function create($conn_asm, $conn_db, $id, $password) {
        global $error;

        $dg = DEFAULT_DG;
        $tablespace = $id;

        $drive = new Drive();
        // Create Tablespace
        $result = $drive->create_tablespace($conn_db, $tablespace, $dg);
        if ($result === ERROR) {
            $error->set_msg("Failed to create Tablespace.");
            return(ERROR);
        }
        
        // Create User
        $result = self::exist_user($conn_db, $id);
        if ($result == TRUE) {
            $error->set_msg("User already exists.");
            return(ERROR);
        }
        $sql =  "create user $id identified by $password default tablespace $tablespace quota " . DEFAULT_QUOTA . " on $tablespace";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            $error->set_msg("Failed to create User. Failed SQL = '$sql'");
            // Rollback
            $drive->delete_tablespace($conn_db, $tablespace);
            return(ERROR);
        }

        // Grant Role to User
        $sql = "grant " . CLOUD_USER  . " to $id";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            $error->set_msg("Failed to assign Role to User. Failed SQL = '$sql'");
            // Rollback
            self::delete($conn_asm, $conn_db, $id);
            return(ERROR);
        }

        // Assign consumer group object privileges to Allow user to use this consumer group
        global $array_consumer_group;
        foreach ($array_consumer_group as $consumer_group) {
            $sql = "BEGIN dbms_resource_manager_privs.grant_switch_consumer_group(grantee_name => '$id',consumer_group => '$consumer_group',grant_option => FALSE);END;";
            $state_id = oci_parse($conn_db, $sql);
            $result = oci_execute($state_id);
            if ($result == FALSE) {
                $error->set_msg("Failed to assign Consumer Group object privileges to User. Failed SQL = '$sql'");
                // Rollback
                self::delete($conn_asm, $conn_db, $id);
                return(ERROR);
            }
        }

        // Assign default consumer group to user
        $result = self::update_consumer_group($conn_db, $id, DEFAULT_CONSUMER_GROUP);
        if ($result === ERROR) {
            $error->set_msg("Failed to assigne default Consumer Group to User");
            // Rollback
            self::delete($conn_asm, $conn_db, $id);
            return(ERROR);
        }
        return(TRUE);
    }

    public function delete($conn_asm, $conn_db, $id) {
        global $error;
        $tablespace = $id;
        $dg = DEFAULT_DG;

        $drive = new Drive();

        // Delete Consumer Group Mapping
        $result = self::update_consumer_group($conn_db, $id, 'NULL');
        if ($result === ERROR) {
            $error->set_msg("Failed to delete Consumer Group mapping");
        }

        // Delete User
        $result = self::exist_user($conn_db, $id);
        if ($result == TRUE) {
            $sql = "drop user $id cascade";
            $state_id = oci_parse($conn_db, $sql);
            $result = oci_execute($state_id);
            if ($result == FALSE) {
                $error->set_msg("Failed to delete User");
            }
        }

        // Delete Tablespace
        $result = $drive->delete_tablespace($conn_db, $tablespace);
        if ($result == FALSE) {
            $error->set_msg("Failed to delete Tablespace.");
            return(ERROR);
        }
        return(TRUE);
    }

    public function update_consumer_group($conn_db, $id, $consumer_group) {
        global $error;

        if ($consumer_group != 'NULL') {
            $consumer_group = "'" . $consumer_group . "'";
        }
        $sql = "BEGIN DBMS_RESOURCE_MANAGER.CREATE_PENDING_AREA(); DBMS_RESOURCE_MANAGER.SET_CONSUMER_GROUP_MAPPING (DBMS_RESOURCE_MANAGER.ORACLE_USER, '$id', $consumer_group); DBMS_RESOURCE_MANAGER.VALIDATE_PENDING_AREA(); DBMS_RESOURCE_MANAGER.SUBMIT_PENDING_AREA(); END;";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed to update Consumer Group mapping. Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

    // Rollback 
    public function rollback($conn_db, $id, $timestamp) {
        global $error;

        $sql = "select table_name from dba_tables where owner = '" . $id . "'";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
            $sql_x = "alter table " . $id . "." . $row['TABLE_NAME'] . " enable row movement";
            $state_id_x = oci_parse($conn_db, $sql_x);
            $result_x = oci_execute($state_id_x);
            if ($result_x != TRUE) {
                $error->set_msg("Failed SQL = '$sql'");
                return(ERROR);
            }
            $sql_x = "flashback table " . $id . "." . $row['TABLE_NAME'] . " to timestamp to_timestamp('" . $timestamp . "', 'YYYY-MM-DD HH24:MI:SS')";
            $state_id_x = oci_parse($conn_db, $sql_x);
            $result_x = oci_execute($state_id_x);
            if ($result_x != TRUE) {
                $error->set_msg("Failed SQL = '$sql'");
                return(ERROR);
            }
        }
        return(TRUE);
    }

    public function exist_user($conn_db, $username) {
        global $error;

        $sql = "select count(*) from dba_users where username = '" . $username . "'";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        $row = oci_fetch_array($state_id);
        if ($row[0] == '1') {
            return(TRUE);
        } else {
            return(FALSE);
        }
    }
}
?>
