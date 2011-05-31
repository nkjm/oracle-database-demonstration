<?php
if (SS != TRUE) {
    echo "<div class=disabled>Disabled</div>\n";
} else {
    echo "<div id=ss_switchover>\n";
    echo "<table style='width:100%;'><tr>\n";
    if ($ss_active_sitename == SS_MAIN_SITENAME) {
        ?>
        <td style='border:none;'>
            <div id='ss_main_site' class='ss_active_site' style='margin:0 auto 0 auto;'>
                <?php echo SS_MAIN_SITENAME; ?>
                <div class='ss_active'>ACTIVE</div>
                <div class='ss_inactive'; style='display:none;'>STANDBY</div>
            </div>
        </td>
        <td style='border:none;'>
            <div id='ss_direction' style='padding-top:8px; margin:0 auto 0 auto;'>
                <img class='visible' src=/img/from_left.png></img>
                <img class='hidden' src=/img/from_right.png></img>
            </div>
        </td>
        <td style='border:none;'>
            <div id='ss_backup_site' class='ss_inactive_site' style='margin:0 auto 0 auto;' sitename='<?php echo SS_BACKUP_SITENAME; ?>'>
                <?php echo SS_BACKUP_SITENAME; ?>
                <div class='ss_active' style='display:none;'>ACTIVE</div>
                <div class='ss_inactive'>STANDBY</div>
            </div>
        </td>
        <?php
    } else {
        ?>
        <td style='border:none;'>
            <div id='ss_main_site' class='ss_inactive_site' style='margin:0 auto 0 auto;' sitename='<?php echo SS_MAIN_SITENAME; ?>'>
                <?php echo SS_MAIN_SITENAME; ?>
                <div class='ss_active' style='display:none;'>ACTIVE</div>
                <div class='ss_inactive'>STANDBY</div>
            </div>
        </td>
        <td style='border:none;'>
            <div id='ss_direction' style='padding-top:8px; margin:0 auto 0 auto;'>
                <img class='visible' src=/img/from_right.png></img>
                <img class='hidden' src=/img/from_left.png></img>
            </div>
        </td>
        <td style='border:none;'>
            <div id='ss_backup_site' class='ss_active_site' style='margin:0 auto 0 auto;'>
                <?php echo SS_BACKUP_SITENAME; ?>
                <div class='ss_active'>ACTIVE</div>
                <div class='ss_inactive' style='display:none;'>STANDBY</div>
            </div>
        </td>
        <?php
    }
    echo "</tr></table>\n";
    echo "</div>\n";

    echo "<table style='width:100%; margin-top:30px; font-size:0.9em;'>\n";

    // Protection Mode
    $ss_protection_mode = $site->fetch_protection_mode($conn_db);
    echo "<tr id='ss_protection_mode' style='height:30px;'>\n";
    echo "<td style='text-align:right;'>Protection Mode :</td>\n";
    if ($ss_protection_mode == 'MAXIMUM AVAILABILITY') {
        echo "<td><div class='switch selected'>SYNC</div></td>\n";
        echo "<td><div class='switch unselected' ss_protection_mode='MAXIMUM PERFORMANCE'>ASYNC</div></td>\n";
    } elseif ($ss_protection_mode == 'MAXIMUM PERFORMANCE') {
        echo "<td><div class='switch unselected' ss_protection_mode='MAXIMUM AVAILABILITY'>SYNC</div></td>\n";
        echo "<td><div class='switch selected'>ASYNC</div></td>\n";
    } else {
        echo 'Unknown';
    }
    echo "</tr>\n";

    // Compression
    $ss_compression = $site->fetch_compression($conn_db, SS_LOG_ARCHIVE_DEST_ID);
    echo "<tr id='ss_compression' style='height:30px;'>\n";
    echo "<td style='text-align:right;'>Compression :</td>\n";
    if ($ss_compression == 'ENABLE') {
        echo "<td><div class='switch selected'>ON</div></td>\n";
        echo "<td><div class='switch unselected' ss_compression='DISABLE'>OFF</div></td>\n";
    } elseif ($ss_compression == 'DISABLE') {
        echo "<td><div class='switch unselected' ss_compression='ENABLE'>ON</div></td>\n";
        echo "<td><div class='switch selected'>OFF</div></td>\n";
    } else {
        echo 'Unknown';
    }
    echo "</tr>\n";

    echo "</table>\n";
}
?>
