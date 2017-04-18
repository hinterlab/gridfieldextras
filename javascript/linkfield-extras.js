jQuery.entwine("linkfield", function($) {

	$("input.link").entwine({
		onmatch: function() {
			
			var self = this;
			this.setDialog(self.siblings('.linkfield-dialog:first'));

			var formUrl = this.parents('form').attr('action'),
				formUrlParts = formUrl.split('?'),
				formUrl = formUrlParts[0],
				url = formUrl + '/field/' + this.attr('name') + '/LinkFormHTML';

			var editableRow = self.parents('tr:first');
			var editButton = self.parent().siblings('.col-buttons').find('a.edit-link');
			if(editableRow) {
				var rowid =  editableRow.attr('data-id');
				url = formUrl + '/field/Blocks/editableRow/form/' + rowid + '/field/' + this.attr('name') + '/LinkFormHTML';
			}else if(editButton.length){
				$(".linkfield-remove-button").hide();
				url = editButton.prop('href');
				url = url.slice(0, - 5); //remove "edit"
				url = url + '/ItemEditForm/field/' + this.attr('data-title').replace(/ /g,'') + '/LinkFormHTML';
				self.getDialog().data("grid", self.closest(".ss-gridfield"));
			}

			if(self.val().length){
				url = url + '?LinkID=' + self.val();
			}else{
				url = url + '?LinkID=0';
			}

			if(typeof formUrlParts[1] !== 'undefined') {
				url = url + '&' + formUrlParts[1];
			}

			this.setURL(url);

			// configure the dialog
			var windowHeight = $(window).height();

			this.getDialog().data("field", this).dialog({
				autoOpen: 	false,
				width:   	$(window).width()  * 80 / 100,
				height:   	$(window).height() * 80 / 100,
				modal:    	true,
				title: 		this.data('dialog-title'),
				position: 	{ my: "center", at: "center", of: window }
			});

			// submit button loading state while form is submitting 
			this.getDialog().on("click", "button", function() {
				$(this).addClass("loading ui-state-disabled");
			});

			// handle dialog form submission
			this.getDialog().on("submit", "form", function() {
				
				var dlg = self.getDialog().dialog(),
					options = {};

				options.success = function(response) {
					if($(response).is(".field")) {
						self.getDialog().empty().dialog("close");
						var grid = self.getDialog().data("grid");
						if(grid){
							grid.entwine('.').entwine('ss').reload();
						}else{
							self.parents('.field:first').replaceWith(response);
						}
					} else {
						self.getDialog().html(response);
					}
				}

				$(this).ajaxSubmit(options);

				return false;
			});
		}
	});

	$(".linkfield-remove-button").entwine({
		onclick: function() {
			var formUrl = this.parents('form').attr('action'),
				formUrlParts = formUrl.split('?'),
				formUrl = formUrlParts[0],
				url = encodeURI(formUrl) + '/field/' + this.siblings('input:first').prop('name') + '/doRemoveLink';

			var editableRow = this.parents('tr:first');
			if(editableRow) {
				var rowid =  editableRow.attr('data-id');
				url = formUrl + '/field/Blocks/editableRow/form/' + rowid + '/field/' + this.siblings('input:first').prop('name') + '/doRemoveLink';
			}

			if(typeof formUrlParts[1] !== 'undefined') {
				url = url + '&' + formUrlParts[1];
			}
			var holder = this.parents('.field:first');
			this.parents('.middleColumn:first').html("<img src='framework/images/network-save.gif' />");
			holder.load(url);
			return false;
		},
	});
});