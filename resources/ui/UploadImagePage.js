bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.UploadImagePage = function() {
	bs.avatars.ui.UploadImagePage.parent.call( this, 'upload' );
};

OO.inheritClass( bs.avatars.ui.UploadImagePage, ext.userProfile.ui.ChangeImagePage );

bs.avatars.ui.UploadImagePage.prototype.init = function() {
	bs.avatars.ui.UploadImagePage.parent.prototype.init.call( this );
	this.selector = new OO.ui.SelectFileWidget( {
		multiple: false,
		droppable: true,
		accept: [ 'image/*' ],
		classes: [ 'bs-avatars-upload-selector' ],
		showDropTarget: true,
	} );
	this.selector.connect( this, {
		change: 'onFileSelect'
	} );
	this.$element.append( this.selector.$element );
};

bs.avatars.ui.UploadImagePage.prototype.getHeaderLabel = function() {
	return mw.msg( 'bs-avatars-file-upload-fieldset-title' );
};

bs.avatars.ui.UploadImagePage.prototype.getHeight = function() {
	return 150;
};

bs.avatars.ui.UploadImagePage.prototype.onFileSelect = function( file ) {
	if ( file.length > 0 ) {
		file = file[ 0 ];
	} else {
		file = null;
	}
	this.emit( 'setAbility', !!file, 'upload' );
	this.file = file;
};

bs.avatars.ui.UploadImagePage.prototype.execute = function() {
	if ( !this.file ) {
		return;
	}

	var dfd = $.Deferred();
	var url = mw.util.wikiScript( 'api' ) + '?action=bs-avatars-tasks&task=uploadFile';

	// POST multipart/form-data
	var formData = new FormData();
	formData.append( 'avatars', this.file );
	formData.append( 'name', 'avatars' );
	formData.append( 'token', mw.user.tokens.get( 'csrfToken' ) );

	$.ajax( {
		url: url,
		type: 'POST',
		data: formData,
		processData: false,
		contentType: false
	} ).done( function( data ) {
		dfd.resolve( data );
	}.bind( this ) ).fail( function( err ) {
		dfd.reject( err );
	}.bind( this ) );

	return dfd.promise();
};
