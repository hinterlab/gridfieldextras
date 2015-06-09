(function($) {
	$.entwine("ss", function($) {

		/**
		 * GridFieldUploadFile
		 */

		$(".ss-gridfield-upload-file .ss-ui-button").entwine({
			onclick: function() {
				var link 		= this.data("href");
				var folderid  	= this.parents(".ss-gridfield-upload-file").find("input").val();
				folderid = +folderid || 0

				this.getGridField().showDetailView(link.replace("{folderid}", folderid));

				return false;
			}
		});

	});
})(jQuery);
