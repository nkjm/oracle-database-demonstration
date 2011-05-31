<?php
Class Customer {
    public $err_msg = array();

    public function create($conn_asm, $conn_db, $id, $password) {
        $dg = DEFAULT_DG;
        $tablespace = $id;

        $drive = new Drive();
        // Create Tablespace
        $result = $drive->create_tablespace($conn_db, $tablespace, $dg);
        if ($result == FALSE) {
            $this->err_msg = array_merge($this->err_msg, $drive->err_msg);
            array_push($this->err_msg, "新たな表領域を作成出来ませんでした。");
            // Rollback
            $drive->delete_dg($conn_asm, $dg);
            return(FALSE);
        }
        
        // Create User
        $state_id = oci_parse($conn_db, "create user $id identified by $password default tablespace $tablespace quota " . DEFAULT_QUOTA . " on $tablespace");
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "新たなユーザーを作成出来ませんでした。");
            // Rollback
            $drive->delete_tablespace($conn_db, $tablespace);
            $drive->delete_dg($conn_asm, $dg);
            return(FALSE);
        }

        // Grant Role to User
        $state_id = oci_parse($conn_db, "grant " . CLOUD_USER  . " to $id");
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "ユーザーにロールを割り当て出来ませんでした。");
            // Rollback
            self::delete($conn_asm, $conn_db, $id);
            return(FALSE);
        }

        // Assign consumer group object privileges to Allow user to use this consumer group
        global $array_consumer_group;
        foreach ($array_consumer_group as $consumer_group) {
            $state_id = oci_parse($conn_db, "BEGIN dbms_resource_manager_privs.grant_switch_consumer_group(grantee_name => '$id',consumer_group => '$consumer_group',grant_option => FALSE);END;");
            $result = oci_execute($state_id);
            if ($result == FALSE) {
                array_push($this->err_msg, "ユーザーにコンシューマーグループオブジェクト権限を割り当て出来ませんでした。");
                // Rollback
                self::delete($conn_asm, $conn_db, $id);
                return(FALSE);
            }
        }

        // Assign default consumer group to user
        $result = self::update_consumer_group($conn_db, $id, DEFAULT_CONSUMER_GROUP);
        if ($result == FALSE) {
            array_push($this->err_msg, "コンシューマーグループマッピングを作成出来ませんでした。");
            // Rollback
            self::delete($conn_asm, $conn_db, $id);
            return(FALSE);
        }
        return(TRUE);
    }

    public function delete($conn_asm, $conn_db, $id) {
        $tablespace = $id;
        $dg = DEFAULT_DG;

        $drive = new Drive();

        // Delete Consumer Group Mapping
        $result = self::update_consumer_group($conn_db, $id, 'NULL');
        if ($result == FALSE) {
            array_push($this->err_msg, "コンシューマーグループマッピングを削除出来ませんでした。");
        }
        $state_id = oci_parse($conn_db, "drop user $id cascade");

        // Delete User
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "ユーザーを削除出来ませんでした。");
        }

        // Delete Tablespace
        $result = $drive->delete_tablespace($conn_db, $tablespace);
        if ($result == FALSE) {
            $this->err_msg = array_merge($this->err_msg, $drive->err_msg);
            array_push($this->err_msg, "表領域を削除出来ませんでした。");
        }

        return($result);
    }

    public function update_consumer_group($conn_db, $id, $consumer_group) {
        if ($consumer_group != 'NULL') {
            $consumer_group = "'" . $consumer_group . "'";
        }
        $state_id = oci_parse($conn_db, "BEGIN DBMS_RESOURCE_MANAGER.CREATE_PENDING_AREA(); DBMS_RESOURCE_MANAGER.SET_CONSUMER_GROUP_MAPPING (DBMS_RESOURCE_MANAGER.ORACLE_USER, '$id', $consumer_group); DBMS_RESOURCE_MANAGER.VALIDATE_PENDING_AREA(); DBMS_RESOURCE_MANAGER.SUBMIT_PENDING_AREA(); END;");
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "コンシューマグループマッピングの変更に失敗しました。");
            return(FALSE);
        }
        return(TRUE);
    }

    // Rollback 
    public function rollback($conn_db, $id, $timestamp) {
        $sql = "select table_name from dba_tables where owner = '" . $id . "'";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL: '" . $sql . "'");
            return(FALSE);
        }
        while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
            $sql_x = "alter table " . $id . "." . $row['TABLE_NAME'] . " enable row movement";
            $state_id_x = oci_parse($conn_db, $sql_x);
            $result_x = oci_execute($state_id_x);
            if ($result_x != TRUE) {
                array_push($this->err_msg, "Failed SQL: '" . $sql_x . "'");
                return(FALSE);
            }
            $sql_x = "flashback table " . $id . "." . $row['TABLE_NAME'] . " to timestamp to_timestamp('" . $timestamp . "', 'YYYY-MM-DD HH24:MI:SS')";
            $state_id_x = oci_parse($conn_db, $sql_x);
            $result_x = oci_execute($state_id_x);
            if ($result_x != TRUE) {
                array_push($this->err_msg, "Failed SQL: '" . $sql_x . "'");
                return(FALSE);
            }
        }
    }
}
?>
