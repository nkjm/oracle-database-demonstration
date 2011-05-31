<?php
class Flash {
    public $err_msg = array();

    public function fetch_db_flash_cache_detail($conn_db) {
        $sql = "select name, value from v\$parameter where name like 'db_flash_cache_%'";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
        while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
            if ($row['NAME'] == 'db_flash_cache_file') {
                if (isset($row['VALUE'])) { 
                    $db_flash_cache_file = $row['VALUE'];
                } else {
                    $db_flash_cache_file = '';
                }
            }
            if ($row['NAME'] == 'db_flash_cache_size') {
                if (isset($row['VALUE'])) { 
                    $db_flash_cache_size = $row['VALUE'];
                } else {
                    $db_flash_cache_size = '0';
                }
            }
        }
        if (!empty($db_flash_cache_file)) {
            $db_flash_cache_enable = TRUE;
        } else {
            $db_flash_cache_enable = FALSE;
        }
        $array_db_flash_cache_detail = array('enable' => $db_flash_cache_enable, 'file' => $db_flash_cache_file, 'size' => $db_flash_cache_size);
        return($array_db_flash_cache_detail);
    }

    public function update_db_flash_cache_size($conn_db, $db_flash_cache_size) {
        $sql = "alter system set db_flash_cache_size=" . $db_flash_cache_size . "G scope=spfile";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result == FALSE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
    }
}
?>
