<?php

namespace BlueSpice\Avatars\Hook\BSUEModulePDFFindFiles;

use BlueSpice\Avatars\Generator;
use BlueSpice\UEModulePDF\Hook\IBSUEModulePDFFindFiles;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserOptionsLookup;
use MediaWiki\Utils\UrlUtils;
use RepoGroup;

class AddProfileImage implements IBSUEModulePDFFindFiles {

	/**
	 *
	 * @var UrlUtils
	 */
	private $urlUtils = null;

	/**
	 *
	 * @var UserFactory
	 */
	private $userFactory = null;

	/**
	 *
	 * @var UserOptionsLookup
	 */
	private $lookup = null;

	/**
	 *
	 * @var Generator
	 */
	private $generator = null;

	/**
	 *
	 * @var RepoGroup
	 */
	private $repoGroup = null;

	/**
	 *
	 * @param UrlUtils $urlUtils
	 * @param UserFactory $userFactory
	 * @param UserOptionsLookup $lookup
	 * @param Generator $generator
	 * @param RepoGroup $repoGroup
	 */
	public function __construct(
		UrlUtils $urlUtils,
		UserFactory $userFactory,
		UserOptionsLookup $lookup,
		Generator $generator,
		RepoGroup $repoGroup
	) {
		$this->urlUtils = $urlUtils;
		$this->userFactory = $userFactory;
		$this->lookup = $lookup;
		$this->generator = $generator;
		$this->repoGroup = $repoGroup;
	}

	/**
	 * @inheritDoc
	 */
	public function onBSUEModulePDFFindFiles(
		$sender,
		$imageElement,
		&$absoluteFileSystemPath,
		&$fileName,
		$type
	): bool {
		if ( $type !== 'images' ) {
			return true;
		}

		$origUrl = $imageElement->getAttribute( 'data-orig-src' );
		$origUrl = $this->urlUtils->expand( $origUrl );
		$parseUrl = $this->urlUtils->parse( $origUrl );
		$params = wfCgiToArray( $parseUrl['query'] );
		$module = $params['module'];
		if ( $module !== 'userprofileimage' ) {
			return true;
		}

		$username = $params['username'];
		$user = $this->userFactory->newFromName( $username );
		$profileImage = $this->lookup->getOption( $user, 'bs-avatars-profileimage' );
		$width = $params['width'];
		$height = $params['height'];

		if ( empty( $profileImage ) ) {
			$file = $this->generator->getAvatarFile( $user );
		} else {
			$file = $this->repoGroup->findFile( $profileImage );
			if ( $file === false || !$file->exists() ) {
				$file = $this->generator->getAvatarFile( $user );
			}
		}

		if ( $file ) {
			$sourcePath = 'images/bluespice/Avatars/';
			$fileName = $file->getName();
			$src = $sourcePath . $fileName;
			$absoluteFileSystemPath = $src;

			$imageElement->setAttribute( 'src', 'images/' . $fileName );
			$imageElement->setAttribute( 'width', ( $width / 60 ) . 'cm' );
			$imageElement->setAttribute( 'height', ( $height / 60 ) . 'cm' );
		}

		return true;
	}
}
