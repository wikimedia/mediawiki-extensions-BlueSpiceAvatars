bs.util.registerNamespace( 'bs.avatars.ui' );

bs.avatars.ui.GenerateAvatarPage = function() {
	bs.avatars.ui.GenerateAvatarPage.parent.call( this, 'generate' );
};

OO.inheritClass( bs.avatars.ui.GenerateAvatarPage, ext.userProfile.ui.ChangeImagePage );

bs.avatars.ui.GenerateAvatarPage.prototype.getHeaderLabel = function() {
	return mw.msg( 'bs-avatars-generate-new-label' );
};

bs.avatars.ui.GenerateAvatarPage.prototype.getHeight = function() {
	return 30;
};

bs.avatars.ui.GenerateAvatarPage.prototype.execute = function() {
	var dfd = $.Deferred();
	new mw.Api().postWithToken( 'csrf', {
		action: 'bs-avatars-tasks',
		task: 'generateAvatar'
	} ).done( function( r ) {
		if ( r.hasOwnProperty( 'success' ) && r.success ) {
			dfd.resolve();
		} else {
			dfd.reject();
		}
	} ).fail( function( e ) {
		dfd.reject();
	} );
	return dfd.promise();
};