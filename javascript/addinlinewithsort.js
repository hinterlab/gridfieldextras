(function($) {
	$.entwine("ss", function($) {

		$(".grid.ss-gridfield.ss-gridfield-editable").entwine({
			onaddnewinline: function(e) {
                if(e.target != this[0]) {
                    return;
                }

				var tmpl = window.tmpl;
				var row = this.find(".ss-gridfield-add-inline-template:last");
				var num = this.data("add-inline-num") || 1;
				var sort = this.data("add-inline-sort") || 1;
				
				tmpl.cache[this[0].id + "ss-gridfield-add-inline-template"] = tmpl(row.html());

				this.find("tbody:first").append(tmpl(this[0].id + "ss-gridfield-add-inline-template", { num: num, sort: sort }));
                this.find("tbody:first").children(".ss-gridfield-no-items").hide();
				this.data("add-inline-num", num + 1);
				this.data("add-inline-sort", sort + 1);
			}
		});

	});
})(jQuery);
