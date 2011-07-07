$(function(){
    //Open New Storage Form
    $('#storage .new').click(function(){
        $('#new_storage_form').slideToggle();
    });

    //Show/Hide Operation on hover
    $('#new_storage_form .switch').mouseover(function(){
        $('.hidden', $(this)).show();
    });
    $('#new_storage_form .switch').mouseout(function(){
        $('.hidden', $(this)).hide();
    });

    //add_storage | detach_storage (if AWS enabled)
    $('#new_storage_form .unselected').click(function(){
        var $disk_path = $(this).attr('disk_path');
        var $action = $(this).attr('action');
        if ($action == 'add') {
            $message = 'Going to add New Storage.<br />Are you sure?';
            $op = 'add_storage';
        } else if ($action == 'detach') {
            $message = 'Going to detach Storage: "' +$disk_path+ '".<br />Are you sure?';
            $op = 'detach_storage';
        }
        $('#new_storage_form').slideToggle();
    	$.confirm({
            'title'     :   'Confirmation',
            'message'   :   $message,
            'buttons'   :   {
                'Yes'   :   {
                    'class' :   'blue',
                    'action':   function(){
                                    $.post(
                                        '/op.php', 
                                        { op: $op, disk_path: $disk_path },
                                        function(data){
                                            if (data.error == 1) {
                                                $message = '';
                                                for (i in data.stack_msg) {
                                                    $message += data.stack_msg[i] + '<br/>';
                                                }
    	                                        $.confirm.error({
                                                    'title' : 'Error',
                                                    'message' : $message,
                                                    'buttons'	: {
                                                        'Close'	: {
                                                            'class'	: 'gray',
                                                            'action':   function(){}
                                                        }
                                                    }
    	                                        });
                                            } else {
                                                $('#middle').load('/ #middle', function(){
                                                    $.getScript('/js/script.js');
                                                    $.confirm.hide(); 
                                                    $('#new_storage_form').slideToggle();
                                                });
                                            }
                                        },'json'
                                    );
                                }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
                }
            }
    	});
    });

    //remove_storage
    $('#new_storage_form .selected').click(function(){
        var $disk_path = $(this).attr('disk_path');
        $('#new_storage_form').slideToggle();
    	$.confirm({
            'title'     :   'Confirmation',
            'message'   :   'Going to remove Storage: "' +$disk_path+ '"<br />Are you sure?',
            'buttons'   :   {
                'Yes'   :   {
                    'class' :   'blue',
                    'action':   function(){
                                    $.post(
                                        '/op.php', 
                                        { op: 'remove_storage', disk_path: $disk_path },
                                        function(data){
                                            if (data.error == 1) {
                                                $message = '';
                                                for (i in data.stack_msg) {
                                                    $message += data.stack_msg[i] + '<br/>';
                                                }
    	                                        $.confirm.error({
                                                    'title' : 'Error',
                                                    'message' : $message,
                                                    'buttons'	: {
                                                        'Close'	: {
                                                            'class'	: 'gray',
                                                            'action':   function(){}
                                                        }
                                                    }
    	                                        });
                                            } else {
                                                $('#middle').load('/ #middle', function(){
                                                    $.getScript('/js/script.js');
                                                    $.confirm.hide(); 
                                                    $('#new_storage_form').slideToggle();
                                                });
                                            }
                                        },'json'
                                    );
                                }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
                }
            }
    	});
    });

    //Close New Storage Form
    $('#new_storage_form .button_no').click(function(){
        $('#new_storage_form').slideUp('slow');
    });

    //Show Resize Flash Cache size form
    $('#flash_cache #fc_db_flash_cache_size .current_value').click(function(){
        $('#flash_cache #fc_db_flash_cache_size .current_value').remove();
        $('#flash_cache #fc_db_flash_cache_size :hidden').show();
    });

    //fc_update_db_flash_cache_size
    $('#flash_cache #fc_db_flash_cache_size .button_yes').click(function(){
        var $fc_db_flash_cache_size = $(':text', $(this).parent()).attr('value');
    	$.confirm({
            'title'     :   'Confirmation',
            'message'   :   'Going to resize Flash Cache to '+$fc_db_flash_cache_size+' GB.<br/>Database must be restarted to take effect.<br/>Are you sure?',
            'buttons'   :   {
                'Yes'   :   {
                    'class' :   'blue',
                    'action':   function(){
                                    $.post(
                                        '/op.php', 
                                        { op: 'fc_update_db_flash_cache_size', fc_db_flash_cache_size: $fc_db_flash_cache_size },
                                        function(data){
                                            if (data.error == 1) {
                                                $message = '';
                                                for (i in data.stack_msg) {
                                                    $message += data.stack_msg[i] + '<br/>';
                                                }
    	                                        $.confirm.error({
                                                    'title' : 'Error',
                                                    'message' : $message,
                                                    'buttons'	: {
                                                        'Close'	: {
                                                            'class'	: 'gray',
                                                            'action':   function(){}
                                                        }
                                                    }
    	                                        });
                                            } else {
                                                $('#middle').load('/ #middle', function(){
                                                    $.getScript('/js/script.js');
                                                    $.confirm.hide(); 
                                                });
                                            }
                                        },'json'
                                    );
                                }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
                }
            }
    	});
    });

    //swap ACTVIE/STANDBY tag in hover site
    $('#site .ss_inactive_site').mouseover(function(){
        $('#site .ss_active_site .ss_active').hide();
        $('#site .ss_active_site .ss_inactive').show();
        $('#site .ss_inactive_site .ss_active').show();
        $('#site .ss_inactive_site .ss_inactive').hide();
        $('#ss_direction .visible').hide();
        $('#ss_direction .hidden').show();
    });
    $('#site .ss_inactive_site').mouseout(function(){
        $('#site .ss_inactive_site .ss_active').hide();
        $('#site .ss_inactive_site .ss_inactive').show();
        $('#site .ss_active_site .ss_active').show();
        $('#site .ss_active_site .ss_inactive').hide();
        $('#ss_direction .visible').show();
        $('#ss_direction .hidden').hide();
    });

    //ss_switchover
    $('#ss_switchover .ss_inactive_site').click(function(){
        var $ss_inactive_site = $(this).attr('sitename');
    	$.confirm({
            'title'     :   'Confirmation',
            'message'   :   'Going to switch Active Site to "' +$ss_inactive_site+ '".<br/>Are you sure?',
            'buttons'   :   {
                'Yes'   :   {
                    'class' :   'blue',
                    'action':   function(){
                                    $.post(
                                        '/op.php',
                                        { op: 'ss_switchover' },
                                        function(data){
                                            if (data.error == 1) {
                                                $message = '';
                                                for (i in data.stack_msg) {
                                                    $message += data.stack_msg[i] + '<br/>';
                                                }
    	                                        $.confirm.error({
                                                    'title' : 'Error',
                                                    'message' : $message,
                                                    'buttons'	: {
                                                        'Close'	: {
                                                            'class'	: 'gray',
                                                            'action':   function(){}
                                                        }
                                                    }
    	                                        });
                                            } else {
                                                $('#middle').load('/ #middle', function(){
                                                    $.getScript('/js/script.js');
                                                    $.confirm.hide(); 
                                                });
                                            }
                                        },'json'
                                    );
                                }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
                }
            }
    	});
    });

    //ss_switch_site
    $('#site .ss_inactive_site').click(function(){
    });
    
    //ss_update_protection_mode
    $('#ss_protection_mode .unselected').click(function(){
        var $ss_protection_mode = $(this).attr('ss_protection_mode');
        $.post(
            '/op.php', 
            { op: 'ss_update_protection_mode', ss_protection_mode: $ss_protection_mode },
            function(data){
                if (data.error == 1) {
                    $message = '';
                    for (i in data.stack_msg) {
                        $message += data.stack_msg[i] + '<br/>';
                    }
    	            $.confirm.error({
                        'title' : 'Error',
                        'message' : $message,
                        'buttons'	: {
                            'Close'	: {
                                'class'	: 'gray',
                                'action':   function(){}
                            }
                        }
    	            });
                } else {
                    $('#middle').load('/ #middle', function(){
                        $.getScript('/js/script.js');
                        $.confirm.hide(); 
                    });
                }
            },'json'
        );
        $.confirm.status();
    });

    //ss_update_compression
    $('#ss_compression .unselected').click(function(){
        var $ss_compression = $(this).attr('ss_compression');

        $.post(
            '/op.php', 
            { op: 'ss_update_compression', ss_compression: $ss_compression },
            function(data){
                if (data.error == 1) {
                    $message = '';
                    for (i in data.stack_msg) {
                        $message += data.stack_msg[i] + '<br/>';
                    }
    	            $.confirm.error({
                        'title' : 'Error',
                        'message' : $message,
                        'buttons'	: {
                            'Close'	: {
                                'class'	: 'gray',
                                'action':   function(){}
                            }
                        }
    	            });
                } else {
                    $('#middle').load('/ #middle', function(){
                        $.getScript('/js/script.js');
                        $.confirm.hide(); 
                    });
                }
            },'json'
        );
        $.confirm.status();
    });

    //delete_customer
    $('#existing_db .db .delete').click(function(){
        var $customer_id = $(this).attr('customer_id');
        var $customer_name = $(this).attr('customer_name');
        var $elem = $(this).closest('.db');
    	
    	$.confirm({
            'title'     :   'Confirmation',
            'message'   :   'Going to delete "' +$customer_name+ '"<br />Are you sure?',
            'buttons'   :   {
                'Yes'   :   {
                    'class' :   'blue',
                    'action':   function(){
                                    $.post(
                                        '/op.php', 
                                        { op: "delete_customer", customer_id: $customer_id },
                                        function(data){
                                            if (data.error == 1) {
                                                $message = '';
                                                for (i in data.stack_msg) {
                                                    $message += data.stack_msg[i] + '<br/>';
                                                }
    	                                        $.confirm.error({
                                                    'title' : 'Error',
                                                    'message' : $message,
                                                    'buttons'	: {
                                                        'Close'	: {
                                                            'class'	: 'gray',
                                                            'action':   function(){}
                                                        }
                                                    }
    	                                        });
                                            } else {
                                                $.confirm.hide(); 
                                                $elem.fadeOut(2000);
                                            }
                                        },'json'
                                    );
                                }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
                }
            }
    	});
    });

    //Open New Database Form
    $('#new_db .new').click(function(){
        $('#new_db_form').slideToggle();
    });

    //Close New Database Form
    $('#new_db_form .button_no').click(function(){
        $('#new_db_form').slideUp('slow');
    });

    //create_customer
    $('#new_db_form .button_yes').click(function(){
        var $customer_name = $('#new_db_form :text').attr('value');
        var $customer_password = $('#new_db_form :password').attr('value');
        $('#new_db_form').slideUp('slow');
        $.confirm.status();
        $.post(
            '/op.php', 
            { op: 'create_customer', customer_name: $customer_name, customer_password: $customer_password },
            function(data){
                if (data.error == 1) {
                    $message = '';
                    for (i in data.stack_msg) {
                        $message += data.stack_msg[i] + '<br/>';
                    }
    	            $.confirm.error({
                        'title' : 'Error',
                        'message' : $message,
                        'buttons'	: {
                            'Close'	: {
                                'class'	: 'gray',
                                'action':   function(){}
                            }
                        }
    	            });
                } else {
                    $('#new_db_form :text').attr('value', '');
                    $('#new_db_form :password').attr('value', '');
                    $('#middle').load('/ #middle', function(){
                        $.getScript('/js/script.js');
                        $.confirm.hide(); 
                    });
                }
            },'json'
        );
    });

    //update_consumer_group
    $('#existing_db .cpu_speed .unselected').click(function(){
        var $customer_id = $(this).attr('customer_id');
        var $consumer_group = $(this).attr('consumer_group');
        $.confirm.status();
        $.post(
            '/op.php', 
            { op: 'update_consumer_group', customer_id: $customer_id, consumer_group: $consumer_group },
            function(data){
                if (data.error == 1) {
                    $message = '';
                    for (i in data.stack_msg) {
                        $message += data.stack_msg[i] + '<br/>';
                    }
    	            $.confirm.error({
                        'title' : 'Error',
                        'message' : $message,
                        'buttons'	: {
                            'Close'	: {
                                'class'	: 'gray',
                                'action':   function(){}
                            }
                        }
    	            });
                } else {
                    $('#middle').load('/ #middle', function(){
                        $.getScript('/js/script.js');
                        $.confirm.hide(); 
                    });
                }
            },'json'
        );
    });

    //Display Storage Quota Form
    $('#existing_db .storage_quota').click(function(){
        $('.current_value', $(this)).remove();
        $(':hidden', $(this)).show();
    });

    //update_storage_quota
    $('#existing_db .storage_quota .button_yes').click(function(){
        var $customer_id = $(':hidden', $(this).parent()).attr('value');
        var $max_gbytes = $(':text', $(this).parent()).attr('value');
        $.confirm.status();
        $.post(
            '/op.php', 
            { op: 'update_storage_quota', customer_id: $customer_id, max_gbytes: $max_gbytes },
            function(data){
                if (data.error == 1) {
                    $message = '';
                    for (i in data.stack_msg) {
                        $message += data.stack_msg[i] + '<br/>';
                    }
    	            $.confirm.error({
                        'title' : 'Error',
                        'message' : $message,
                        'buttons'	: {
                            'Close'	: {
                                'class'	: 'gray',
                                'action':   function(){}
                            }
                        }
    	            });
                } else {
                    $('#middle').load('/ #middle', function(){
                        $.getScript('/js/script.js');
                        $.confirm.hide(); 
                    });
                }
            },'json'
        );
    });
    	
    //update_compression
    $('#existing_db .compression .unselected').click(function(){
        var $customer_id = $(this).attr('customer_id');
        var $compression = $(this).attr('compression');
        $.confirm.status();
        $.post(
            '/op.php', 
            { op: 'update_compression', customer_id: $customer_id, compression: $compression },
            function(data){
                if (data.error == 1) {
                    $message = '';
                    for (i in data.stack_msg) {
                        $message += data.stack_msg[i] + '<br/>';
                    }
    	            $.confirm.error({
                        'title' : 'Error',
                        'message' : $message,
                        'buttons'	: {
                            'Close'	: {
                                'class'	: 'gray',
                                'action':   function(){}
                            }
                        }
    	            });
                } else {
                    $('#middle').load('/ #middle', function(){
                        $.getScript('/js/script.js');
                        $.confirm.hide(); 
                    });
                }
            },'json'
        );
    });

    //Show Login Information
    $('#existing_db .db_name').click(function(){
        var $customer_id = $(this).attr('customer_id');
        var $hostname = $(this).attr('hostname');
        var $service = $(this).attr('service');
    	$.confirm({
            'title' : 'Login Information',
            'message' : 'Login ID: ' +$customer_id+ '<br/>Password: ******<br/>Hostname: ' +$hostname+ '<br/>Service: ' +$service,
            'buttons' : {
                'Close'	: {
                    'class' : 'gray',
                    'action' : function(){}
                }
            }
    	});
    });

    //Show Calender
    var $timestamp_now = $('#db .snapshots .timestamp').attr('timestamp_now');
    var $timestamp_start = $('#db .snapshots .timestamp').attr('timestamp_start');
    var $timestamp_end = $('#db .snapshots .timestamp').attr('timestamp_end');
    $('#db .snapshots .timestamp').datetime({ value: $timestamp_now, minDate: $timestamp_start, maxDate: $timestamp_end });

    //rollback_customer
    $('#db .snapshots .button_yes').click(function(){
        var $customer_id = $(this).attr('customer_id');
        var $customer_name = $(this).attr('customer_name');
        var $timestamp = $(':text', $(this).parent()).attr('value');
    	$.confirm({
            'title'     :   'Confirmation',
            'message'   :   'Going to rewind "'+$customer_name+'" to '+$timestamp+'.<br/>Are you sure?',
            'buttons'   :   {
                'Yes'   :   {
                    'class' :   'blue',
                    'action':   function(){
                                    $.post(
                                        '/op.php', 
                                        { op: 'rollback_customer', customer_id: $customer_id, timestamp: $timestamp },
                                        function(data){
                                            if (data.error == 1) {
                                                $message = '';
                                                for (i in data.stack_msg) {
                                                    $message += data.stack_msg[i] + '<br/>';
                                                }
    	                                        $.confirm.error({
                                                    'title' : 'Error',
                                                    'message' : $message,
                                                    'buttons'	: {
                                                        'Close'	: {
                                                            'class'	: 'gray',
                                                            'action':   function(){}
                                                        }
                                                    }
    	                                        });
                                            } else {
                                                $('#middle').load('/ #middle', function(){
                                                    $.getScript('/js/script.js');
                                                    $.confirm.hide(); 
                                                });
                                            }
                                        },'json'
                                    );
                                }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action': function(){}	// Nothing to do in this case. You can as well omit the action property.
                }
            }
    	});
    });

    //Error
    var $flag_error = $('.flag_error:first').text();
    if ($flag_error == 'TRUE'){
        $message = ''
        $('.error_msg').each(function(){$message += $(this).text() + '<br/>';});
    	$.confirm.error({
            'title' : 'Error',
            'message' : $message,
            'buttons' : {
                'Close'	: {
                    'class' : 'gray',
                    'action' : function(){}
                }
            }
    	});
    }

    //Initialize
    var $flag_role_required = $('#flag_role_required').text();
    var $flag_consumer_group_required = $('#flag_consumer_group_required').text();
    var $flag_resource_plan_required = $('#flag_resource_plan_required').text();
    var $flag_resource_plan_disabled = $('#flag_resource_plan_disabled').text();
    var $flag_snapshot_retention_dirty = $('#flag_snapshot_retention_dirty').text();

    if ($flag_role_required == 'TRUE' || $flag_consumer_group_required == 'TRUE' || $flag_resource_plan_required == 'TRUE' || $flag_resource_plan_disabled == 'TRUE' || $flag_snapshot_retention_dirty == 'TRUE'){
    	$.confirm({
            'title'		: 'Confirmation',
            'message'	: 'Database Cloud needs to be initialized.<br />Initialization does not destroy any existing data.<br />Proceed?',
            'buttons'	: {
                'Yes'	: {
                    'class'	: 'blue',
                    'action':   function(){
                                    $.post(
                                        '/op.php', 
                                        { op: "initialize" },
                                        function(data){
                                            if (data.error == 1) {
                                                $message = '';
                                                for (i in data.stack_msg) {
                                                    $message += data.stack_msg[i] + '<br/>';
                                                }
    	                                        $.confirm.error({
                                                    'title' : 'Error',
                                                    'message' : $message,
                                                    'buttons'	: {
                                                        'Close'	: {
                                                            'class'	: 'gray',
                                                            'action':   function(){}
                                                        }
                                                    }
    	                                        });
                                            } else {
                                                $.confirm.hide(); 
                                            }
                                        },'json'
                                    );
                                }
                },
                'No'	: {
                    'class'	: 'gray',
                    'action':   function(){}
                }
            }
    	});
    }

    $('.button_yes').corner();
    $('.button_no').corner();
});
