<?php

namespace BlueSpice\Avatars\Html\FormField;

use BlueSpice\Services;
use BlueSpice\Avatars\DynamicFileDispatcher\UserProfileImage;
use BlueSpice\DynamicFileDispatcher\Params;
use BlueSpice\Avatars\Html\ProfileImage;

class UserImage extends \HTMLTextField {
	public function getLabel() {
		return wfMessage( 'bs-avatars-pref-userimage' )->parse();
	}

	public function getInputHTML( $value ) {
		$this->mParent->getOutput()->addModuleStyles( 'ext.bluespice.avatars.preferences.styles' );

		$profileImage = new ProfileImage( $this->mParent->getUser(), 128, 128 );
		$html = parent::getInputHTML( $value ) . $profileImage->getHtml();

		return $html;
	}
}