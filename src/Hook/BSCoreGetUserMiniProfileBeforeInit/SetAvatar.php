<?php

namespace BlueSpice\Avatars\Hook\BSCoreGetUserMiniProfileBeforeInit;
use BlueSpice\Hook\BSCoreGetUserMiniProfileBeforeInit;
use BlueSpice\Avatars\Generator;

/**
 * Set avatar image if user has no UserImage setting
 */
class SetAvatar extends BSCoreGetUserMiniProfileBeforeInit {

	protected function skipProcessing() {
		if( $this->user->isAnon() ) {
			return true;
		}
		if( !empty( $this->user->getOption( 'MW::UserImage' ) ) ) {
			return true;
		}
		if( wfReadOnly() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$generator = new Generator( $this->getConfig() );
		$this->userMiniProfileView->setUserImageSrc( $generator->generate(
			$this->user,
			$this->params
		));

		return true;
	}

}