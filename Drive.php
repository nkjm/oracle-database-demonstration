<?php
class Drive {
    public function exist_tablespace($conn_db, $tablespace) {
        global $error;

        $sql = "select count(*) from dba_tablespaces where tablespace_name = '" . $tablespace . "'";
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

    public function create_tablespace($conn_db, $tablespace, $dg) {
        global $error;

        $result = self::exist_tablespace($conn_db, $tablespace);
        if ($result == TRUE) {
            $error->set_msg("Tablespace already exists.");
            return(ERROR);
        }
        $sql = "CREATE BIGFILE TABLESPACE $tablespace DATAFILE '+$dg' SIZE 1M AUTOEXTEND ON NEXT 5M MAXSIZE UNLIMITED LOGGING EXTENT MANAGEMENT LOCAL SEGMENT SPACE MANAGEMENT AUTO DEFAULT COMPRESS FOR OLTP"; 
        $state_id = oci_parse($conn_db, $sql); 
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

    public function delete_tablespace($conn_db, $tablespace) {
        global $error;

        //$sql = "DROP TABLESPACE $tablespace INCLUDING CONTENTS AND DATAFILES";
        $sql = "DROP TABLESPACE $tablespace";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

    public function fetch_dg_detail($conn_asm, $dg) {
        global $error;

        $sql = "SELECT TOTAL_MB, FREE_MB FROM V\$ASM_DISKGROUP WHERE NAME = '" . $dg . "'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        $row = oci_fetch_array($state_id, OCI_BOTH);
        $dg_detail = array();
        $dg_detail['space_total'] = $row['TOTAL_MB'];
        $dg_detail['space_free'] = $row['FREE_MB'];
        $dg_detail['space_used'] = $dg_detail['space_total'] - $dg_detail['space_free'];
        $dg_detail['space_usage'] = round(100 * $dg_detail['space_used'] / $dg_detail['space_total']);

        return($dg_detail);
    }

    public function update_storage_quota($conn_db, $customer_id, $max_gbytes) {
        global $error;

        $sql = "ALTER USER " . $customer_id . " QUOTA " . $max_gbytes. "G on " . $customer_id;
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

    public function update_compression($conn_db, $tablespace, $compression) {
        global $error;

        if ($compression == 'on') {
            $sql = "ALTER TABLESPACE " . $tablespace . " DEFAULT COMPRESS FOR OLTP";
        } elseif ($compression == 'off') {
            $sql = "ALTER TABLESPACE " . $tablespace . " DEFAULT NOCOMPRESS";
        } else {
            $error->set_msg("Invalid compression mode specified.");
            return(ERROR);
        }
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

    public function fetch_member_disk($conn_asm, $dg) {
        global $error;

        $sql = "select v\$asm_disk.path, v\$asm_disk.os_mb, v\$asm_disk.header_status, v\$asm_disk.state from v\$asm_disk left outer join v\$asm_diskgroup on v\$asm_disk.group_number = v\$asm_diskgroup.group_number where v\$asm_diskgroup.name = '" . $dg . "'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        $array_disk = array();
        while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
            array_push($array_disk, $row);
        }
        return($array_disk);
    }

    public function fetch_candidate_disk($conn_asm) {
        global $error;

        $sql = "select path, os_mb ,header_status from v\$asm_disk where header_status = 'CANDIDATE' or header_status = 'FORMER'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        $array_disk = array();
        while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
            array_push($array_disk, $row);
        }
        return($array_disk);
    }

    public function add_disk($conn_asm, $dg, $disk_path) {
        global $error;

        $sql = "ALTER DISKGROUP $dg ADD DISK '" . $disk_path . "'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

    public function delete_disk($conn_asm, $dg, $disk_path) {
        global $error;

        $sql = "select v\$asm_disk.name from v\$asm_disk left outer join v\$asm_diskgroup on v\$asm_disk.group_number = v\$asm_diskgroup.group_number where v\$asm_diskgroup.name = '" . $dg . "' and v\$asm_disk.path = '" . $disk_path . "'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        $row = oci_fetch_array($state_id, OCI_BOTH);

        $sql = "ALTER DISKGROUP $dg DROP DISK " . $row['NAME'];
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

}
?>
