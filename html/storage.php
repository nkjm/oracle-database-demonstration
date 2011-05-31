<table style='width:100%;'>
    <tr>
        <td style='text-align:right; vertical-align:bottom; border:none; padding-bottom:10px;'>Total Space :</td>
        <td style='text-align:right; vertical-align:bottom; border:none;'><span style='font-size:3.0em; color:#000000;'><?php echo round($storage['space_total'] / 1024); ?></span></td>
        <td style='text-align:right; vertical-align:bottom; border:none; padding-bottom:10px;'>GB</td>
    </tr>
    <tr>
        <td style='text-align:right; vertical-align:bottom; border:none; padding-bottom:10px;'>Usage :</td>
        <td style='text-align:right; vertical-align:bottom; border:none;'><span style='font-size:3.0em; color:#000000;'><?php echo $storage['space_usage']; ?></span></td>
        <td style='text-align:right; vertical-align:bottom; border:none; padding-bottom:10px;'>%</td>
    </tr>
</table>
<img class=new height=20 width=20 src='/img/new.png' style='float:left;'></img><span class=new style='float:left;margin-top:6px; font-size:0.8em; font-weight: bold;'>Add/Remove Storage</span>
<div id='new_storage_form' style='clear:both; display:none; margin-top:30px; padding: 5px 5px 5px 5px;'>
<?php
// fetch Member Disks
$array_member_disk = $drive->fetch_member_disk($conn_asm, DEFAULT_DG);

// fetch Candidate or Former Disks
$array_candidate_disk = $drive->fetch_candidate_disk($conn_asm);

// Merge both Disks and Sort by disk_path
$array_all_disk = array_merge($array_member_disk, $array_candidate_disk);
foreach ($array_all_disk as $k => $disk) {
    if (isset($disk['PATH'])) {
        $path[$k] = $disk['PATH'];
    }
}
array_multisort($path, SORT_REGULAR, $array_all_disk);

foreach ($array_all_disk as $disk) {
    if ($disk['HEADER_STATUS'] == 'MEMBER' && $disk['STATE'] != 'DROPPING') {
        echo "<div class='switch selected' style='clear:both; margin-bottom:2px; cursor:pointer;' disk_path='" . $disk['PATH'] . "'>\n";
    } elseif ($disk['HEADER_STATUS'] == 'MEMBER' && $disk['STATE'] == 'DROPPING') {
        echo "<div class='switch intermediate' style='clear:both; margin-bottom:2px;'>\n";
    } else {
        echo "<div class='switch unselected' style='clear:both; margin-bottom:2px;' disk_path='" . $disk['PATH'] . "'>\n";
    }
    echo "<div style='float:left; width:30%;'>\n";
    echo str_replace('/dev/', '', $disk['PATH']);
    echo "</div>\n";
    echo "<div style='text-align:right; float:left; width:30%;'>\n";
    echo round($disk['OS_MB'] / 1024) . " GB";
    echo "</div>\n";
    echo "<div style='text-align:right; width:100%;'>\n";
    echo "<span class='hidden' style='display:none;'>\n";
    if ($disk['HEADER_STATUS'] == 'MEMBER' && $disk['STATE'] != 'DROPPING') {
        echo "Remove";
    } elseif ($disk['HEADER_STATUS'] == 'MEMBER' && $disk['STATE'] == 'DROPPING') {
        echo "&nbsp;";
    } else {
        echo "Add";
    }
    echo "</span>\n";
    echo "&nbsp;";
    echo "</div>\n";
    echo "</div>\n";
}
?>
</div>
