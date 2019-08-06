(function($) {

    $.entwine('ss', function($) {

        // js-relation-selector
        $('.grid-field .js-relation-selector').entwine({

            onclick: function(){
                var gridfield = $(this).getGridField();
                var state = gridfield.getState('GridFieldRelationHandler');
                var relationsHandler = typeof state.GridFieldRelationHandler !== 'undefined' ? state.GridFieldRelationHandler : {};
                var relations = relationsHandler.RelationVal ? relationsHandler.RelationVal : [];
                if (this.is(':checked')) {
                    relations.push(this.val());
                } else {
                    var newRelations = [];
                    for (var i = 0; i < relations.length; i++) {
                        if (relations[i] != this.val()) {
                            newRelations.push(relations[i]);
                        }
                    }
                    relations = newRelations;
                }
                relationsHandler.RelationVal = relations;
                gridfield.setState('GridFieldRelationHandler', relationsHandler);
            }

        });

    });

})(jQuery)