Ext.define('BS.Avatars.SettingsWindow', {
	requires: [ 'BS.form.UploadPanel' ],
	extend: 'Ext.window.Window',
	title: mw.message('bs-avatars-upload-title').plain(),
	id: 'bs-avatars-upload-window',
	width: 430,
	/*height: 200,*/
	singleton: true,
	closeAction: 'hide',
	bodyPadding: 5,
	layout: "form",
	//Custom Setting
	currentData: {},
	initComponent: function() {
		this.ufLogoUpload = Ext.create('BS.form.UploadPanel', {
			url: bs.api.makeUrl( 'bs-avatars-tasks', { task: 'uploadFile' }, true ),
			uploadFormName: 'avatars',
			uploadFieldLabel: mw.message('bs-avatars-upload-label').plain(),
			uploadLabelWidth: 50,
			uploadButtonsInline: true
		});
		this.ufLogoUpload.on('upload', this.btnUploadClick, this);
		this.fsUpload = Ext.create('Ext.form.FieldSet', {
			title: mw.message('bs-avatars-file-upload-fieldset-title').plain(),
			collapsible: true,
			items: [
				this.ufLogoUpload
			]
		});
		this.tfUserImage = Ext.create('Ext.form.field.Text', {
			name: 'uimg',
			blankText: mw.message('bs-avatars-userimage-help').plain(),
			value: mw.user.options.get( 'bs-avatars-profileimage' ),
			allowBlank: false,
			labelWidth: 150,
			padding: "0 5 0 0"
		});
		this.bUserImage = Ext.create('Ext.Button', {
			text: mw.message('bs-extjs-save').plain(),
			ariaLabel: mw.message('bs-extjs-save').plain(),
			flex:0.5
		});
		this.bUserImage.on('click', this.tfUserImageClick, this);
		this.fsUserImage = Ext.create('Ext.form.FieldSet', {
			title: mw.message('bs-avatars-userimage-title').plain(),
			collapsible: true,
			collapsed: true,
			items: [{
					xtype: 'fieldcontainer',
					// fieldLabel: mw.message('bs-avatars-userimage-title').plain(),
					layout: 'hbox',
					defaults: {
						flex: 1,
						hideLabel: true
					},
					items: [
						this.tfUserImage,
						this.bUserImage
					]
				}
			]
		});
		this.bGenerateNew = Ext.create('Ext.Button', {
			text: mw.message('bs-avatars-generate-new-label').plain(),
			ariaLabel: mw.message('bs-avatars-generate-new-label').plain(),
					//height: 50,
					width: "100%",
					margin: "0 0 10 0"
		});
		this.bGenerateNew.on('click', this.btnGenerateNewClick, this);
		this.fsGenerateNew = Ext.create('Ext.form.FieldSet', {
			title: mw.message('bs-avatars-auto-generate-fieldset-title').plain(),
			collapsible: true,
			collapsed: true,
			items: [
				this.bGenerateNew
			]
		});
		this.bCancel = Ext.create('Ext.Button', {
			text: mw.message('bs-extjs-cancel').plain(),
			ariaLabel: mw.message('bs-extjs-cancel').plain()
		});
		this.bCancel.on('click', this.btnCancelClick, this);
		this.items = [
			this.fsUpload,
			this.fsUserImage,
			this.fsGenerateNew
		];
		this.buttons = [
			this.bCancel
		];

		this.callParent(arguments);
	},
	btnCancelClick: function() {
		this.close();
	},
	doGenerateNew: function() {
		var me = this;
		bs.api.tasks.exec(
			'avatars',
			'generateAvatar'
		).done( function( response ) {
			location.reload();
		});
	},
	confirmOverwrite: function(callback) {
		if( mw.user.options.get( 'bs-avatars-profileimage' ) ) {
			bs.util.confirm('AMwarn2', {
				text: mw.message('bs-avatars-warning-text').plain(),
				title: mw.message('bs-avatars-warning-title').plain()},
			{
				ok: callback,
				scope: this
			}
			);
		}
		else {
			callback.apply(this);
		}
	},
	btnGenerateNewClick: function() {
		this.confirmOverwrite(this.doGenerateNew);
	},
	tfUserImageClick: function() {
		var me = this;
		var userInput = this.tfUserImage.getValue();
		var escapedInput = Ext.util.Format.htmlEncode( userInput );
		bs.api.tasks.exec(
			'avatars',
			'setUserImage',
			{ userImage: escapedInput }
		).done( function( response ) {
			location.reload();
		});
	},
	doUpload: function() {
		var form = this.ufLogoUpload.getForm();
		if (!form.isValid())
			return;
		this.setLoading( true );
		form.submit({
			method: 'POST',
			params: {
				name: 'avatars',
				token: mw.user.tokens.get( 'csrfToken' )
			},
			waitMsg: mw.message('bs-extjs-uploading').plain(),
			success: function(fp, o) {
				// Ignore api warings about wrong result json instead of text/html
				// for extjs upload
				mw.notify( mw.msg( 'bs-extjs-title-success' ) );
				location.reload();
			},
			failure: function(fp, o) {
				// TODO: Quickfix - fake a success response, as the upload will still work
				// but with result failure and security warings due to default ORIGIN DENY
				// header
				// This should be addressed soon!
				mw.notify( mw.msg( 'bs-extjs-title-success' ) );
				location.reload();
			},
			scope: this
		});
	},
	btnUploadClick: function(el, form) {
		this.confirmOverwrite(this.doUpload);
	}
});