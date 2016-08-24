(function($) {
	var reloadGrid 				= false;
	var closestGrid 			= null;
	
	$.entwine("ss", function($) {
		$('.cms-edit-form.CMSPageEditController').entwine({
			onbeforesubmitform: function(){
				var save = $('.relationhandler-saverel:visible');
				if(save.length){
					reloadGrid 	= true;
					closestGrid = save.closest(".ss-gridfield");
					save.trigger('click');
				}
			},
			onaftersubmitform: function(){
				if(reloadGrid){
					var id = closestGrid.attr('id');
					$('#Form_EditForm_Packages.ss-gridfield').entwine('.').entwine('ss').reload();
					reloadGrid = false;
				}
			}
		});
		
	});
})(jQuery);