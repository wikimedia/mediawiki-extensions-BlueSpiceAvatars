<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use BlueSpice\Avatars\Generator;
use BlueSpice\DynamicFileDispatcher\UserProfileImage\AnonImage;
use BlueSpice\DynamicFileDispatcher\UserProfileImage as UPI;

class UserProfileImage extends UPI {

	/**
	 *
	 * @return Image|ImageExternal
	 */
	public function getFile() {
		$file = parent::getFile();
		if ( $file instanceof AnonImage ) {
			return $file;
		}

		$profileImage = $this->user->getOption( 'bs-avatars-profileimage' );
		if ( empty( $profileImage ) ) {
			return $this->getDefaultUserImageFile();
		}

		if ( wfParseUrl( $profileImage ) !== false ) {
			return new ImageExternal( $this, $profileImage, $this->user );
		}

		$repoFile = \RepoGroup::singleton()->findFile( $profileImage );
		if ( $repoFile === false || !$repoFile->exists() ) {
			return $this->getDefaultUserImageFile();
		}

		return $this->getThumbnailImageFile(
			$repoFile, static::WIDTH, static::HEIGHT );
	}

	/**
	 *
	 * @return Image
	 */
	protected function getDefaultUserImageFile() {
		$generator = new Generator( $this->getConfig() );
		$file = $generator->getAvatarFile( $this->user );
		if ( !$file->exists() ) {
			$generator->generate( $this->user );
		}

		return $this->getThumbnailImageFile(
			$file, UPI::WIDTH, UPI::HEIGHT );
	}

	/**
	 * @param File $file
	 * @param string $widthName
	 * @param string $heightName
	 * @return Image
	 */
	protected function getThumbnailImageFile( $file, $widthName, $heightName ) {
		$params = [ 'width' => $this->params[ $widthName ] ];
		$height = $this->params[ $heightName ];
		if ( $height != -1 ) {
			$params['height'] = $height;
		}
		return new Image( $this, $file->transform( $params ) );
	}
}
