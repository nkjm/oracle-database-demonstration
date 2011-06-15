<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Oracle Database Cloud" />
<title>Oracle Database Demonstration</title>
<link href='http://fonts.googleapis.com/css?family=Cuprum&amp;subset=latin' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="/css/oracle.css" />
<link rel="stylesheet" type="text/css" href="/jquery.confirm/jquery.confirm.css" />
<link rel="stylesheet" type="text/css" href="/jquery.ui.datetime/jquery.ui.datetime.css" />
<link rel="stylesheet" type="text/css" media="screen" href="http://hotlink.jquery.com/jqueryui/themes/base/jquery.ui.all.css" />
<link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/themes/flick/jquery-ui.css" />

</head>
<body>

<?php
if (count($error->msg_list) > 0) {
    foreach ($error->msg_list as $msg) {
        echo "<div class='error'>" . $msg . "</div>\n";
    }
}

if ($error->blank == TRUE) {
    :
} else {
?>

<div id=top style='text-align:right; padding: 20px 10px 10px 0;'>
    <h1><a href='/' style='text-decoration:none; padding: 4px 0 0 0;'>Oracle Database Demonstration</a></h1>
</div>
<div id=middle>
    <div id=cloud_status style='margin: 0 auto 0 auto; width: 95%;'>
        <div id=storage class=cloud_status>
            <h3>Storage</h3>
            <div style='padding-top:10px;'>
            <?php require_once 'storage.php'; ?>
            </div>
        </div>
        <div id=flash_cache class=cloud_status>
            <h3>Flash Cache</h3>
            <div style='padding-top:10px;'>
            <?php require_once 'flash_cache.php';?>
            </div>
        </div>
        <div id=site class=cloud_status>
            <h3>Site</h3>
            <div style='padding-top:10px;'>
            <?php require_once 'site.php';?>
            </div>
        </div>
    </div>
    <div id=db style='clear:both; padding: 20px 0 0 0;'>
        <div id=new_db style='width:20%; margin:0 auto 0 auto;'>
            <img class=new src='/img/new.png' style='float:left;'></img><span class=new style='float:left; margin-top:10px; font-size:0.9em; font-weight: bold;'>New Database</span>
        </div>
        <div id=new_db_form style='display:none; font-size:1.2em; clear:both; width:40%; margin:50px auto 10px auto;'>
            <div style='text-align:center; padding: 10px;'>DB Name  : <input type=text name=customer_name></input></div>
            <div style='text-align:center; padding: 10px;'>Password : <input type=password name=customer_password></input></div>
            <div style='text-align:center; padding: 10px;'>
                <input type=submit class=button_yes value='CREATE'></input>
                <input type=submit class=button_no value='CANCEL'></input>
            </div>
        </div>
        <div id=existing_db style='clear:both; padding: 10px 0 0 0;'>
            <?php require_once 'existing_db.php'; ?>
        </div>
    </div>
</div>
<div id=bottom style='padding: 20px 0 0 0;'>
    &nbsp;
</div>

<?php
}
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/jquery-ui.min.js"></script>
<script src="/jquery.confirm/jquery.confirm.js"></script>
<script src="/jquery.ui.datetime/jquery.ui.datetime.src.js"></script>
<script src="/jquery.activity_indicator/jquery.activity-indicator-1.0.0.js"></script>
<script src="/jquery.corner/jquery.corner.js"></script>
<script src="/js/script.js"></script>
<script src="/js/error.js"></script>
</body>
</html>

