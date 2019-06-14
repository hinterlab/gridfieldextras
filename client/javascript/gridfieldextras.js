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
          console.log(grid);
        } catch(e) {
          console.log(e);
        }

        console.log(this);

        /*

        var link = this.data("href");
        var cls  = this.parents(".ss-gridfield-add-new-multi-class").find("select").val();

        if(cls && cls.length) {
          this.getGridField().showDetailView(link.replace("{class}", encodeURI(cls)));
        }
*/
        return false;
      }
    });

		// $(".ss-gridfield-upload-file .btn__gridfieldupload").entwine({
		// 	onclick: function() {
		// 		var link 		= this.data("href");
    //
		// 		try {
    //       this.getGridField()
    //     } catch(e) {}
    //
		// 		console.log(this);
		// 		// var folderid  	= this.parents(".ss-gridfield-upload-file").find("input").val();
		// 		// folderid = +folderid || 0
    //     //
		// 		// this.getGridField().showDetailView(link.replace("{folderid}", folderid));
    //     //
		// 		return false;
		// 	}
		// });

  });
})(jQuery);
