<?php
$sql = "select DBA_USERS.USERNAME, DBA_USERS.INITIAL_RSRC_CONSUMER_GROUP, DBA_TABLESPACES.COMPRESS_FOR, DBA_TS_QUOTAS.max_bytes from dba_users left outer join dba_tablespaces on dba_users.username = dba_tablespaces.tablespace_name left outer join dba_ts_quotas on dba_users.username = dba_ts_quotas.tablespace_name where dba_users.username like '" . CUSTOMER_PREFIX . "%' order by dba_users.username";
$state_id = oci_parse($conn_db, $sql);
$result = oci_execute($state_id);
if ($result == FALSE) {
    echo "エラー: DBリストを取得できませんでした。";
} else {
    echo "<table style='margin: 0px auto 0px auto; width: 90%;'>\n";
    echo "<tr>\n";
    echo "<th>DB Name</th>";
    echo "<th>CPU Speed</th>";
    echo "<th>Storage Quota</th>";
    echo "<th>Compression</th>";
    echo "<th>Snapshots</th>";
    echo "<th style='border-color:#ffffff;'>&nbsp</th>\n";
    echo "</tr>\n";
    while ($row = oci_fetch_array($state_id, OCI_BOTH)) {
        echo "<tr class='db'>\n";

        // DB Name
        echo "<td class='db_name' style='cursor:pointer;' customer_id='" . $row['USERNAME'] . "' hostname='" . DB_SCANNAME . "' service='" . DB_SERVICE . "'>\n";
        echo str_replace(CUSTOMER_PREFIX, "", $row['USERNAME']);
        echo "</td>\n";

        // CPU Speed 
        echo "<td class='cpu_speed'>\n";
        foreach ($array_consumer_group as $consumer_group) {
            if ($row['INITIAL_RSRC_CONSUMER_GROUP'] == $consumer_group) {
                echo "<span class='switch selected'>$consumer_group</span>\n";
            } else {
                echo "<span class='switch unselected' customer_id='" . $row['USERNAME'] . "' consumer_group='" . $consumer_group . "'>$consumer_group</span>\n";
            }
        }
        echo "</td>\n";

        // Storage Quota
        echo "<td class='storage_quota'>\n";
        if (!isset($row['MAX_BYTES']) || $row['MAX_BYTES'] == -1){
            echo "<span class='current_value'>Unlimited</span>";
            echo "<div style='display:none;'>\n";
            echo "<input type=text name=max_gbytes size=4 value='0'></input>GB\n";
            echo "<input type=hidden name=customer_id value='" . $row['USERNAME'] . "'></input>\n";
            echo "<input type=submit class='button_yes' value='Save'></input>\n";
            echo "</div>\n";
        } else {
            echo "<span class='current_value'>" . round($row['MAX_BYTES'] / 1024 / 1024 / 1024) . " GB</span>\n";
            echo "<div style='display:none;'>\n";
            echo "<input type=text name=max_gbytes size=4 value='" . round($row['MAX_BYTES'] / 1024 / 1024 / 1024) . "'></input>GB\n";
            echo "<input type=hidden name=customer_id value='" . $row['USERNAME'] . "'></input>\n";
            echo "<input type=submit class='button_yes' value='Save'></input>\n";
            echo "</div>\n";
        }
        echo "</td>\n";

        // Compression
        echo "<td class='compression'>\n";
        if (isset($row['COMPRESS_FOR']) && $row['COMPRESS_FOR'] == 'OLTP'){
            echo "<span class='switch selected'>ON</span>\n";
            echo "<span class='switch unselected' customer_id='" . $row['USERNAME'] . "' compression='off'>OFF</span>\n";
        } else {
            echo "<span class='switch unselected' customer_id='" . $row['USERNAME'] . "' compression='on'>ON</span>\n";
            echo "<span class='switch selected'>OFF</span>\n";
        }
        echo "</td>\n";

        // Snapshots
        $timestamp_now = date("Y-m-d H:i");
        $timestamp_start = date("Y-m-d H:i", strtotime("-1 week"));
        $timestamp_end = $timestamp_now;
        echo "<td class='snapshots'>\n";
        echo "<input type='text' class='timestamp' timestamp_now='" . $timestamp_now . "' timestamp_start='" . $timestamp_start . "' timestamp_end='" . $timestamp_end . "'></input>\n";
        echo "<input type='submit' class='button_yes' value='Revert' customer_id='" . $row['USERNAME'] . "' customer_name='" . str_replace(CUSTOMER_PREFIX, '', $row['USERNAME']). "'></input>\n";
        echo "</td>\n";

        // Delete
        echo "<td style='border-color:#ffffff;'><img class='delete' src='/img/delete.png' customer_id='" . $row["USERNAME"] . "' customer_name='" . str_replace(CUSTOMER_PREFIX, "", $row["USERNAME"]) . "'></img></td>\n";

        echo "</tr>\n";
    }
    echo "</table>\n";
}
?>
