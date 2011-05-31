<?php
$flash = new Flash();
$db_flash_cache = $flash->fetch_db_flash_cache_detail($conn_db);
if ($db_flash_cache['enable'] != TRUE) {
    echo "<div class=disabled>Disabled</div>\n";
} else {
    echo "<table id='fc_db_flash_cache_size' style='width:100%;'>\n";
    
    // Current Value
    echo "<tr class='current_value'>";
    echo "<td style='vertical-align: bottom; text-align:right; border:none; padding-bottom:10px;'>Size :</td>\n";
    echo "<td style='text-align:right; border:none;'><span style='font-size: 3.0em;'>" . round($db_flash_cache['size'] / 1024 / 1024 / 1024) . "</span></td>";
    echo "<td style='vertical-align: bottom; text-align:right; border:none; padding-bottom:10px;'>GB</td>\n";
    echo "</tr>";

    // Resize Form
    echo "<tr style='display:none'>";
    echo "<td style='text-align:right; border:none;'>Size :</td>\n";
    echo "<td style='text-align:right; border:none;'>\n";
    echo "<input type=text name=fc_db_flash_cache_size size=4 value='" . round($db_flash_cache['size'] / 1024 / 1024 / 1024) . "'></input> GB\n";
    echo "<input type=submit class='button_yes' value='Save'></input>\n";
    echo "</td>\n";
    echo "</tr>\n";

    echo "</table>\n";
}
?>
