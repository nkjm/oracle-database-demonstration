$(function(){
    if ($('div .error').length > 0) {
        $message = $('div .error:first').attr('value');
        $.confirm({
                'title'		: 'Error',
                'message'	: $message,
                'buttons'	: {
                    'Close'	: {
                        'class'	: 'gray',
                        'action':  function(){}
                    }
                }
        });
    }
});
