(function($) {
  $.entwine("ss", function($) {

		/**
		 * GridFieldUploadFile
		 */


    $(".ss-gridfield-upload-file .btn__gridfieldupload").entwine({
      onclick: function() {
        var link 		= this.data("href");

        try {
          var grid = this.getGridField();
          var folderid  	= this.parents(".ss-gridfield-upload-file").find("input").val();
          folderid = +folderid || 0
          this.getGridField().showDetailView(link.replace("{folderid}", folderid));
        } catch(e) {
        }
        return false;
      }
    });

  });
})(jQuery);
