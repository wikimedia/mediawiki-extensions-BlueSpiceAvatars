<?php

namespace BlueSpice\Avatars\Hook\BSUEModulePDFFindFiles;

use BlueSpice\UEModulePDF\Hook\IBSUEModulePDFFindFiles;
use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\User\UserFactory;
use MediaWiki\Utils\UrlUtils;

class AddProfileImage implements IBSUEModulePDFFindFiles {

	/**
	 *
	 * @var UrlUtils
	 */
	private $urlUtils;

	/**
	 *
	 * @var UserFactory
	 */
	private $userFactory;

	/**
	 *
	 * @var ProfileImageProviderFactory
	 */
	private $profileImageProviderFactory;

	/**
	 *
	 * @param UrlUtils $urlUtils
	 * @param UserFactory $userFactory
	 * @param ProfileImageProviderFactory $profileImageProviderFactory
	 */
	public function __construct(
		UrlUtils $urlUtils,
		UserFactory $userFactory,
		ProfileImageProviderFactory $profileImageProviderFactory
	) {
		$this->urlUtils = $urlUtils;
		$this->userFactory = $userFactory;
		$this->profileImageProviderFactory = $profileImageProviderFactory;
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
		if ( !str_ends_with( $parseUrl['path'], '/dynamic-file-dispatcher/userprofileimage' ) ) {
			return true;
		}

		$username = $params['username'];
		$user = $this->userFactory->newFromName( $username );
		if ( !$user ) {
			return true;
		}
		$imageInfo = null;
		foreach ( $this->profileImageProviderFactory->getAll() as $handler ) {
			$imageInfo = $handler->provide( $user, $params );
			if ( $imageInfo ) {
				break;
			}
		}

		if ( !$imageInfo ) {
			return true;
		}
		$fileName = basename( $imageInfo->getPath() );
		$src = $imageInfo->getPath();
		$absoluteFileSystemPath = $src;

		$width = $params['width'] ? (int)$params['width'] : 32;
		$height = $params['height'] ? (int)$params['height'] : 32;
		$imageElement->setAttribute( 'src', 'images/' . $fileName );
		$imageElement->setAttribute( 'width', ( $width / 60 ) . 'cm' );
		$imageElement->setAttribute( 'height', ( $height / 60 ) . 'cm' );

		return true;
	}
}
