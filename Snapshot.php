<?php
class Snapshot {
    public function fetch_retention($conn_db) {
        global $error;

        $sql = "select value from v\$parameter where name = 'undo_retention'";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed to get undo retention value. Failed SQL = '$sql'");
            return(ERROR);
        }
        $row = oci_fetch_array($state_id);
        return($row['VALUE']);
    }

    public function set_retention($conn_db, $retention) {
        global $error;

        $sql = "alter system set undo_retention=" . $retention . " scope=both";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed to set undo retention. Failed SQL = '$sql'");
            return(ERROR);
        }
        sleep(1);
        return(TRUE);
    }
}
?>
