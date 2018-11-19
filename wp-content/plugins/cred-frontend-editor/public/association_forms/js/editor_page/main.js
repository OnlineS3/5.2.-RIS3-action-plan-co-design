var Toolset = Toolset || {};

if ( typeof Toolset.CRED === "undefined" ) {
    Toolset.CRED = {};
}

// the head.js object
Toolset.CRED.head = Toolset.CRED.head || head;

Toolset.CRED.AssociationFormsEditor = {};
Toolset.CRED.AssociationForms = {};
Toolset.CRED.AssociationFormsEditor.viewmodels = {};

Toolset.CRED.AssociationFormsEditor.Class = function( $ ) {
    // private variables in scope
    var self = this, model = null;

    // member variable editor
    self.editorSelector = 'cred_association_form_content';
    self.editorMode = 'myshortcodes';
    self.editor = self.editor || {};
    self.actions = null;
    self.wizardEnabled = false;

    // Extend the generic listing page controller.
    Toolset.Gui.AbstractPage.call(self);

    // Enable or disable
    self.setWizardStatus = function ( status ) {
        self.wizardEnabled = status;
    };
    self.displayWizard = function () {
        var modelData = self.getModelData();
        if( modelData.action === 'cred_association_form_edit' ){
            self.setWizardStatus( false ) ;
        } else {
            // TODO: check user preference

            self.setWizardStatus( true ) ;
        }
    };

    self.getBackboneModel = function(){
        return model;
    };

    self.initializeBackboneModel = function(){
        try{
            var data = Toolset.CRED.AssociationFormsEditor.formModelData;

            data.wpnonce = jQuery('input[name="'+Toolset.CRED.AssociationFormsEditor.wpnonce+'"]').val();

            var create = new Toolset.CRED.AssociationFormCreateEdit( Toolset.CRED.AssociationFormsEditor.action, data );
            var backboneModel = create.getModel();

            return backboneModel;

        } catch( error ){
            console.log( 'Cannot create or edit association form %s', error );
            return null;
        }
    };

    self.getMainViewModel = function() {
        model = self.initializeBackboneModel();
        var view_model = new Toolset.CRED.AssociationFormsEditor.viewmodels.AssociationFormViewModel( model, { 'has_relationships' : self.getModelData().has_relationships } );
        ko.applyBindings(view_model);
        return view_model;
    };

    self.beforeInit = function() {
        var modelData = self.getModelData();
        //noinspection JSUnresolvedVariable
        Toolset.CRED.AssociationFormsEditor.jsPath = modelData.jsIncludePath;
        Toolset.CRED.AssociationFormsEditor.jsEditorPath = modelData.jsEditorIncludePath;
        Toolset.CRED.AssociationFormsEditor.action = modelData.action;
        Toolset.CRED.AssociationFormsEditor.selectedPost = modelData.selected_post;
        Toolset.CRED.AssociationFormsEditor.form_type = modelData.form_type;
        Toolset.CRED.AssociationFormsEditor.wpnonce = modelData.wpnonce;
        Toolset.CRED.AssociationFormsEditor.select2nonce = modelData.select2nonce;
        Toolset.CRED.AssociationFormsEditor.formModelData = modelData.formModelData;
        self.initStaticData( modelData );
        self.displayWizard();
    };

    self.fixEditorMenuItemUrl = function(){
        var $link = jQuery('a.current'), id = model.get('id'), url = $link.prop('href');
        if( id ){
            $link.prop( 'href', url + '&action=edit&id=' + id );
        }
    };


    self.afterInit = function() {
        self.initIclEditor();
        self.actions =  new Toolset.CRED.AssociationFormActions();
        self.fixEditorMenuItemUrl();
        Toolset.hooks.addAction( 'cred_editor_exit_wizard_mode', self.reinitialiseContentEditor );
    };

    self.deleteForm = function( associationForm ) {

        var data = {
            'to_delete' : associationForm,
            'delete_type' : 'single'
        };

        var dialog = Toolset.CRED.AssociationForms.dialogs.DeleteForm( data, function(result) {
            self.actions.delete_single_form( function( updated_model, response ){
                if(response.success === true){
                    window.location.href = window.location.origin+window.location.pathname+'?page=cred_relationship_forms';
                } else {
                    associationForm.displayedMessage({text: response.data.message, type: 'error'});
                    associationForm.messageVisibilityMode('show')
                }
                associationForm.display.isSaving(false);
            }, result  );
        }, self);

        dialog.display();
    };

    self.reinitialiseContentEditor = function(){
        self.editorDestroy();
        self.initIclEditor();
        self.fixEditorMenuItemUrl();
    };


    self.initIclEditor = function () {
        self.buildCodeMirror();
        self.refreshContentEditor();
        self.addQtButtons();
		self.addBootstrapGridButton();
		self.addHooks();
    };

    self.buildCodeMirror = function(){
        WPV_Toolset.CodeMirror_instance[self.editorSelector] = icl_editor.codemirror(
            self.editorSelector,
            true,
            self.editorMode
        );

        self.editor.codemirror = WPV_Toolset.CodeMirror_instance[self.editorSelector];
    };

    self.editorDestroy = function(){
        WPV_Toolset.CodeMirror_instance[self.editorSelector] = null;
        window.iclCodemirror[self.editorSelector] = null;
    };

    self.addQtButtons = function(){
        self._visual_editor_html_editor_qt = quicktags( { id: 'cred_association_form_content', buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' } );
        WPV_Toolset.add_qt_editor_buttons( self._visual_editor_html_editor_qt, self.editor.codemirror );
    };
	
	self.addBootstrapGridButton = function() {
		Toolset.hooks.doAction( 'toolset_text_editor_CodeMirror_init', self.editorSelector );
	};
	
	self.addHooks = function() {
		Toolset.hooks.addAction( 'cred_editor_refresh_content_editor', self.refreshContentEditor );
		Toolset.hooks.addAction( 'cred_editor_focus_content_editor', self.focusContentEditor );
	};

    self.refreshContentEditor = function(){
        try{
            self.editor.codemirror.refresh();
            self.editor.codemirror.focus();
        } catch( e ){
            console.log( 'There is a problem with CodeMirror instance: ', e.message );
        }
    };
	
	self.focusContentEditor = function(){
        try{
            self.editor.codemirror.focus();
        } catch( e ){
            console.log( 'There is a problem with CodeMirror instance: ', e.message );
        }
    };

    self.initStaticData = function( modelData ) {
        Toolset.CRED.AssociationFormsEditor.strings = modelData.strings || {};
        Toolset.CRED.AssociationFormsEditor.itemsPerPage = modelData.itemsPerPage || {};
        Toolset.CRED.AssociationFormsEditor.bulkActions = modelData.bulkActions || {};
    };

    self.loadDependencies = function( nextStep ) {
        // Continue after loading the view of the listing table.
        Toolset.CRED.head.load(
            Toolset.CRED.AssociationFormsEditor.jsPath + '/dialogs/DeleteForm.js',
            Toolset.CRED.AssociationFormsEditor.jsPath + '/AssociationFormActions.js',
            Toolset.CRED.AssociationFormsEditor.jsPath + '/models/AssociationFormModel.js',
            Toolset.CRED.AssociationFormsEditor.jsEditorPath + '/AssociationForm_ExtraEditors.js',
            Toolset.CRED.AssociationFormsEditor.jsEditorPath + '/AssociationFormViewModel.js',
            Toolset.CRED.AssociationFormsEditor.jsEditorPath + '/AssociationFormCreateEdit.js',
            nextStep
        );
    };

    _.bindAll( self, 'initIclEditor' );
};

Toolset.CRED.AssociationFormsEditor.main = new Toolset.CRED.AssociationFormsEditor.Class( $ );
Toolset.CRED.head.ready( Toolset.CRED.AssociationFormsEditor.main.init );