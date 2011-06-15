<?php
Class Role {
    public function create($conn_db, $name) {
        global $error;

        $result = self::exist($conn_db, $name);
        if ($result == TRUE) {
            $error->set_msg("Role already exists.");
            return(ERROR);
        }
        $sql = "CREATE ROLE $name";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed to create Role. Failed SQL = '$sql'");
            return(ERROR);
        }
        $sql = "GRANT connect to $name";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed to assign connect Role to new Role. Failed SQL = '$sql'");
            return(ERROR);
        }
        $sql = "GRANT resource to $name";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        if ($result != TRUE) {
            $error->set_msg("Failed to assign resource Role to new Role. Failed SQL = '$sql'");
            return(ERROR);
        }
        return(TRUE);
    }

    public function delete($conn_db, $name) {
        global $error;

        $result = self::exist($conn_db, $name);
        if ($result == TRUE) {
            $sql = "DROP ROLE '$name'";
            $state_id = oci_parse($conn_db, $sql);
            $result = oci_execute($state_id);
            if ($result != TRUE) {
                $error->set_msg("Failed to delete Role. Failed SQL = '$sql'");
                return(ERROR);
            }
        }
        return(TRUE);
    }

    public function exist($conn_db, $name) {
        global $error;

        $sql = "SELECT count(*) FROM user$ WHERE type# = 0 and name = '" . CLOUD_USER . "'";
        $state_id = oci_parse($conn_db, $sql);
        $result = oci_execute($state_id);
        $row = oci_fetch_array($state_id, OCI_BOTH);
        if ($row[0] == '1') {
            return(TRUE);
        } else {
            return(FALSE);
        }
    }
}
?>
