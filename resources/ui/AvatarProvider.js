bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.AvatarProvider = function() {
	bs.avatars.ui.AvatarProvider.parent.call( this );
};

OO.inheritClass( bs.avatars.ui.AvatarProvider, ext.userProfile.ui.ProfileImageProvider );

bs.avatars.ui.AvatarProvider.prototype.getPage = function() {
	return new bs.avatars.ui.GenerateAvatarPage();
};

bs.avatars.ui.AvatarProvider.prototype.getActions = function() {
	return [
		{
			action: 'generate',
			label: mw.msg( 'bs-avatars-generate-button' ),
			flags: [ 'primary', 'progressive' ], modes: [ 'generate' ],
			type: 'executable'
		},
		{
			action: 'switchToGenerate',
			label: mw.msg( 'bs-avatars-generate-new-label' ),
			type: 'switch',
			switchFor: 'generate',
		}
	];
};

bs.avatars.ui.AvatarProvider.prototype.canHandle = function( action ) {
	return action === 'generate' || action === 'switchToGenerate';
};

bs.avatars.ui.AvatarProvider.prototype.handleAction = function( action, page, dialog ) {
	var dfd = $.Deferred();
	if ( action === 'generate' ) {
		if ( !page || page.getName() !== action ) {
			return;
		}
		return this.executePageAction( page, dialog );
	} else if ( action === 'switchToGenerate' ) {
		dialog.switchPanel( 'generate' );
	}
	return dfd.promise();
};

bs.avatars.ui.AvatarProvider.prototype.isEnabledByDefault = function() {
	return true;
};

ext.userProfile.profileImage.providerRegistry.register( 'AvatarProvider', new bs.avatars.ui.AvatarProvider() );
