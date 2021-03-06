(function($){
	
	$.confirm = function(params){
		
		if($('#confirmOverlay').length){
			// A confirm is already shown on the page:
                        $.confirm.hide();
			//return false;
		}
		
		var buttonHTML = '';
		$.each(params.buttons,function(name,obj){
			// Generating the markup for the buttons:
			buttonHTML += '<a href="#" class="button '+obj['class']+'">'+name+'<span></span></a>';
			if(!obj.action){
				obj.action = function(){};
			}
		});
		
		var markup = [
			'<div id="confirmOverlay">',
			'<div id="confirmBox">',
			'<h1>',params.title,'</h1>',
			'<p>',params.message,'</p>',
			'<div id="confirmButtons">',
			buttonHTML,
			'</div></div></div>'
		].join('');
		
		$(markup).hide().appendTo('body').fadeIn();
		
		var buttons = $('#confirmBox .button'),
			i = 0;

		$.each(params.buttons,function(name,obj){
			buttons.eq(i++).click(function(){
				
				// Calling the action attribute when a
				// click occurs, and hiding the confirm.
                                if (name == 'Yes'){
                                    obj.action();
                                    $.confirm.hide();
                                    $.confirm.status();
                                }
                                if (name == 'No' || name == 'Close'){
                                    $.confirm.hide();
                                }
				return false;
			});
		});
	}

        $.confirm.status = function(){
		var markup = [
			'<div id="confirmOverlay">',
			'<div id="confirmBox">',
			'<h1>Processing</h1>',
			'<p>Please wait for a while...</p>',
			'<div id="confirmStatus">',
                        '&nbsp;',
			'</div></div></div>'
		].join('');
		
		$(markup).hide().appendTo('body').fadeIn();
                $("#confirmStatus").activity();
        }

	$.confirm.hide = function(){
		$('#confirmOverlay').fadeOut(function(){
			$(this).remove();
		});
	}
	
	$.confirm.error = function(params){
		if($('#confirmOverlay').length){
			// A confirm is already shown on the page:
                        $.confirm.hide();
		}
		
		var buttonHTML = '';
		$.each(params.buttons,function(name,obj){
			// Generating the markup for the buttons:
			buttonHTML += '<a href="#" class="button '+obj['class']+'">'+name+'<span></span></a>';
			if(!obj.action){
				obj.action = function(){};
			}
		});
		
		var markup = [
			'<div id="confirmOverlay">',
			'<div id="confirmBox">',
			'<h1 style="color:#ff0000;">',params.title,'</h1>',
			'<p style="color:#ff4444;">',params.message,'</p>',
			'<div id="confirmButtons">',
			buttonHTML,
			'</div></div></div>'
		].join('');
		
		$(markup).hide().appendTo('body').fadeIn();
		
		var buttons = $('#confirmBox .button'),
			i = 0;

		$.each(params.buttons,function(name,obj){
			buttons.eq(i++).click(function(){
                            $.confirm.hide();
			});
		});
	}
})(jQuery);
