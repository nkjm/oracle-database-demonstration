<?php
class Snapshot {
    public $err_msg = array();

    public function fetch_retention($conn_db) {
        $sql = "select value from v\$parameter where name = 'undo_retention'";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
        $row = oci_fetch_array($state_id);
        return($row['VALUE']);
    }

    public function set_retention($conn_db, $retention) {
        $sql = "alter system set undo_retention=" . $retention . " scope=both";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            array_push($this->err_msg, "Failed SQL = '$sql'");
            return(FALSE);
        }
        sleep(1);
        return(TRUE);
    }
}
?>
