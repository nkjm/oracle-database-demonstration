<?php
class Drive {
    public $err_msg = array();

    public function create_tablespace($conn_db, $tablespace, $dg) {
        $sql = "CREATE BIGFILE TABLESPACE $tablespace DATAFILE '+$dg' SIZE 1M AUTOEXTEND ON NEXT 5M MAXSIZE UNLIMITED LOGGING EXTENT MANAGEMENT LOCAL SEGMENT SPACE MANAGEMENT AUTO DEFAULT COMPRESS FOR OLTP"; 
        $state_id = oci_parse($conn_db, $sql); 
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
        return(TRUE);
    }

    public function delete_tablespace($conn_db, $tablespace) {
        //$sql = "DROP TABLESPACE $tablespace INCLUDING CONTENTS AND DATAFILES";
        $sql = "DROP TABLESPACE $tablespace";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
        return(TRUE);
    }

    public function fetch_dg_detail($conn_asm, $dg) {
        $sql = "SELECT TOTAL_MB, FREE_MB FROM V\$ASM_DISKGROUP WHERE NAME = '" . $dg . "'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
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
        $sql = "ALTER USER " . $customer_id . " QUOTA " . $max_gbytes. "G on " . $customer_id;
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
    }

    public function update_compression($conn_db, $tablespace, $compression) {
        if ($compression == 'on') {
            $sql = "ALTER TABLESPACE " . $tablespace . " DEFAULT COMPRESS FOR OLTP";
        } elseif ($compression == 'off') {
            $sql = "ALTER TABLESPACE " . $tablespace . " DEFAULT NOCOMPRESS";
        } else {
            return(FALSE);
        }
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
    }

    public function fetch_member_disk($conn_asm, $dg) {
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
        $sql = "ALTER DISKGROUP $dg ADD DISK '" . $disk_path . "'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
        return(TRUE);
    }

    public function delete_disk($conn_asm, $dg, $disk_path) {
        $sql = "select v\$asm_disk.name from v\$asm_disk left outer join v\$asm_diskgroup on v\$asm_disk.group_number = v\$asm_diskgroup.group_number where v\$asm_diskgroup.name = '" . $dg . "' and v\$asm_disk.path = '" . $disk_path . "'";
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        $row = oci_fetch_array($state_id, OCI_BOTH);

        $sql = "ALTER DISKGROUP $dg DROP DISK " . $row['NAME'];
        $state_id = oci_parse($conn_asm, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
        return(TRUE);
    }
}
?>
