bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.UploadProvider = function() {
	bs.avatars.ui.UploadProvider.parent.call( this );
};

OO.inheritClass( bs.avatars.ui.UploadProvider, ext.userProfile.ui.ProfileImageProvider );

bs.avatars.ui.UploadProvider.prototype.getPage = function() {
	return new bs.avatars.ui.UploadImagePage();
};

bs.avatars.ui.UploadProvider.prototype.getActions = function() {
	return [
		{
			action: 'upload',
			label: mw.msg( 'bs-avatars-upload-button' ),
			flags: [ 'primary', 'progressive' ], modes: [ 'upload' ],
			type: 'executable'
		},
		{
			action: 'switchToUpload',
			label: mw.msg( 'bs-avatars-file-upload-fieldset-title' ),
			type: 'switch',
			switchFor: 'upload'
		}
	];
};

bs.avatars.ui.UploadProvider.prototype.canHandle = function( action ) {
	return action === 'upload' || action === 'switchToUpload';
};

bs.avatars.ui.UploadProvider.prototype.handleAction = function( action, page, dialog ) {
	var dfd = $.Deferred();
	if ( action === 'upload' ) {
		if ( !page || page.getName() !== action ) {
			return;
		}
		return this.executePageAction( page, dialog );
	} else if ( action === 'switchToUpload' ) {
		dialog.switchPanel( 'upload' );
	}
	return dfd.promise();
};

bs.avatars.ui.UploadProvider.prototype.isEnabledByDefault = function() {
	return false;
};

ext.userProfile.profileImage.providerRegistry.register( 'UploadProvider', new bs.avatars.ui.UploadProvider() );
