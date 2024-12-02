<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use BlueSpice\DynamicFileDispatcher\UserProfileImage as UPI;
use BlueSpice\DynamicFileDispatcher\UserProfileImage\AnonImage;
use File;
<<<<<<< HEAD   (cf9df2 Localisation updates from https://translatewiki.net.)
use MediaWiki\MediaWikiServices;
||||||| BASE
use MediaWiki\Permissions\Authority;
use MediaWiki\User\UserOptionsLookup;
use MWException;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\Module\UserProfileImage as DefaultImageModule;
use RepoGroup;
use ThumbnailImage;
use User;
=======
use MediaWiki\Permissions\Authority;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserOptionsLookup;
use MWException;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\Module\UserProfileImage as DefaultImageModule;
use RepoGroup;
use ThumbnailImage;
use User;
>>>>>>> CHANGE (b5c075 Fix getting image for other users)

class UserProfileImage extends UPI {

	/** @var UserFactory */
	private $userFactory;

	/**
<<<<<<< HEAD   (cf9df2 Localisation updates from https://translatewiki.net.)
	 *
	 * @return Image
||||||| BASE
	 * @param UserOptionsLookup $optionsLookup
	 * @param RepoGroup $repoGroup
	 * @param Generator $generator
=======
	 * @param UserOptionsLookup $optionsLookup
	 * @param RepoGroup $repoGroup
	 * @param Generator $generator
	 * @param UserFactory $userFactory
>>>>>>> CHANGE (b5c075 Fix getting image for other users)
	 */
<<<<<<< HEAD   (cf9df2 Localisation updates from https://translatewiki.net.)
	public function getFile() {
		$file = parent::getFile();
		if ( $file instanceof AnonImage ) {
			return $file;
||||||| BASE
	public function __construct( UserOptionsLookup $optionsLookup, RepoGroup $repoGroup, Generator $generator ) {
		$this->optionsLookup = $optionsLookup;
		$this->repoGroup = $repoGroup;
		$this->generator = $generator;
	}

	public function isAuthorized( Authority $user, array $params ): bool {
		$this->user = $user->getUser();
		return parent::isAuthorized( $user, $params );
	}

	/**
	 * @param array $params
	 * @return IDynamicFile|null
	 * @throws MWException
	 */
	public function getFile( array $params ): ?IDynamicFile {
		if ( !$this->user->isRegistered() ) {
			return parent::getFile( $params );
		}
		$setImage = $this->optionsLookup->getOption( $this->user, 'bs-avatars-profileimage' );
		if ( empty( $setImage ) ) {
			return $this->getDefaultUserImageFile( $params );
=======
	public function __construct(
		UserOptionsLookup $optionsLookup, RepoGroup $repoGroup, Generator $generator, UserFactory $userFactory
	) {
		$this->optionsLookup = $optionsLookup;
		$this->repoGroup = $repoGroup;
		$this->generator = $generator;
		$this->userFactory = $userFactory;
	}

	public function isAuthorized( Authority $user, array $params ): bool {
		$this->user = $user->getUser();
		return parent::isAuthorized( $user, $params );
	}

	/**
	 * @param array $params
	 * @return IDynamicFile|null
	 * @throws MWException
	 */
	public function getFile( array $params ): ?IDynamicFile {
		if ( $params['username'] ) {
			$this->user = $this->userFactory->newFromName( $params['username'] );
		}
		if ( !$this->user || !$this->user->isRegistered() ) {
			return parent::getFile( $params );
		}
		$setImage = $this->optionsLookup->getOption( $this->user, 'bs-avatars-profileimage' );
		if ( empty( $setImage ) ) {
			return $this->getDefaultUserImageFile( $params );
>>>>>>> CHANGE (b5c075 Fix getting image for other users)
		}

		$services = MediaWikiServices::getInstance();
		$profileImage = $services->getUserOptionsLookup()
			->getOption( $this->user, 'bs-avatars-profileimage' );
		if ( empty( $profileImage ) ) {
			return $this->getDefaultUserImageFile();
		}

		$repoFile = $services->getRepoGroup()->findFile( $profileImage );
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
		$generator = MediaWikiServices::getInstance()->getService(
			'BSAvatarsAvatarGenerator'
		);
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
		$params = [ 'width' => (int)$this->params[$widthName] ];

		if ( $file->getWidth() && $params[ 'width' ] >= $file->getWidth() ) {
			$params[ 'width' ] = $file->getWidth() - 1;
		}
		$height = (int)$this->params[ $heightName ];
		if ( $height != -1 ) {
			$params['height'] = $height;
		}
		if (
			isset( $params['height'] ) &&
			$file->getHeight() && $params['height'] >= $file->getHeight()
		) {
			$params['height'] = $file->getHeight() - 1;
		}

		return new Image( $this, $file->transform( $params ) );
	}
}
