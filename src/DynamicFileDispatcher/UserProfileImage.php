<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use BlueSpice\Avatars\Generator;
use File;
use MediaWiki\Permissions\Authority;
use MediaWiki\User\UserOptionsLookup;
use MWException;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\Module\UserProfileImage as DefaultImageModule;
use RepoGroup;
use ThumbnailImage;
use User;

class UserProfileImage extends DefaultImageModule {

	/** @var User|null */
	private $user = null;

	/** @var UserOptionsLookup */
	private $optionsLookup;

	/** @var RepoGroup */
	private $repoGroup;

	/** @var Generator */
	private $generator;

	/**
	 * @param UserOptionsLookup $optionsLookup
	 * @param RepoGroup $repoGroup
	 * @param Generator $generator
	 */
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
		}

		$repoFile = $this->repoGroup->findFile( $setImage );
		if ( $repoFile === false || !$repoFile->exists() ) {
			return $this->getDefaultUserImageFile( $params );
		}

		return $this->getThumbnailImageFile(
			$repoFile, $params['width'] ?? 32, $params['height'] ?? 32
		);
	}

	/**
	 *
	 * @param array $params
	 * @return IDynamicFile|null
	 * @throws MWException
	 */
	protected function getDefaultUserImageFile( array $params ): ?IDynamicFile {
		$file = $this->generator->getAvatarFile( $this->user );
		if ( !$file->exists() ) {
			$this->generator->generate( $this->user );
		}

		return $this->getThumbnailImageFile(
			$file, $params['width'] ?? 32, $params['height'] ?? 32
		);
	}

	/**
	 * @param File $file
	 * @param mixed $width
	 * @param mixed $height
	 * @return IDynamicFile|null
	 */
	protected function getThumbnailImageFile( File $file, $width, $height ): ?IDynamicFile {
		$params = [ 'width' => (int)$width ];

		if ( $file->getWidth() && $params[ 'width' ] >= $file->getWidth() ) {
			$params['width'] = $file->getWidth() - 1;
		}
		$height = (int)$height;
		if ( $height != -1 ) {
			$params['height'] = $height;
		}
		if (
			isset( $params['height'] ) &&
			$file->getHeight() && $params['height'] >= $file->getHeight()
		) {
			$params['height'] = $file->getHeight() - 1;
		}

		$transformation = $file->transform( $params );

		if ( !( $transformation instanceof ThumbnailImage ) ) {
			return null;
		}

		return new AvatarDynamicImage( $transformation );
	}
}
