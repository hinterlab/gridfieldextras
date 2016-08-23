(function($) {
	$.entwine("ss", function($) {
		$('.cms-edit-form.CMSPageEditController').entwine({
			onbeforesubmitform: function(){
				var save = $('.relationhandler-saverel:visible');
				if(save.length){
					save.trigger('click');
					
					setTimeout(function(){
						// I do not think this slows the triggered function down
					}, 500);
				}
			}
		});
	});
})(jQuery);
