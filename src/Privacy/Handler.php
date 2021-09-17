<?php

namespace BlueSpice\Avatars\Privacy;

use BlueSpice\Avatars\Extension as Avatars;
use BlueSpice\Avatars\Generator;
use BlueSpice\DynamicFileDispatcher\Params;
use BlueSpice\DynamicFileDispatcher\UserProfileImage;
use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use Exception;
use IDatabase;
use MediaWiki\MediaWikiServices;
use Message;
use Status;
use User;

class Handler implements IPrivacyHandler {
	protected $db;

	/**
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
	}

	/**
	 * @param User $userToDelete
	 * @param User $deletedUser
	 * @return Status
	 */
	public function delete( User $userToDelete, User $deletedUser ) {
		Avatars::unsetUserImage( $userToDelete );
		return Status::newGood();
	}

	/**
	 * @param array $types Types of info users wants to retrieve
	 * @param string $format Requested output format
	 * @param User $user User to export data from
	 * @return Status
	 */
	public function exportData( array $types, $format, User $user ) {
		$params = [
			Params::MODULE => UserProfileImage::MODULE_NAME,
			UserProfileImage::USERNAME => $user->getName(),
			UserProfileImage::WIDTH => 200,
			UserProfileImage::HEIGHT => 200
		];

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$dfdUrlBuilder = MediaWikiServices::getInstance()->getService(
			'BSDynamicFileDispatcherUrlBuilder'
		);
		$url = $dfdUrlBuilder->build( new Params( $params ) );
		$label = Message::newFromKey( 'bs-avatars-upload-label' );
		return Status::newGood( [
			Transparency::DATA_TYPE_PERSONAL => [
				"{$label->plain()}: {$config->get( 'Server' )}$url"
			]
		] );
	}

	/**
	 * @param string $oldUsername
	 * @param string $newUsername
	 * @return Status
	 */
	public function anonymize( $oldUsername, $newUsername ) {
		$user = User::newFromName( $oldUsername );
		$user->setName( $newUsername );

		$generator = MediaWikiServices::getInstance()->getService(
			'BSAvatarsAvatarGenerator'
		);
		try {
			$generator->generate( $user, [ Generator::PARAM_OVERWRITE => true ] );
		} catch ( Exception $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}

		return Status::newGood();
	}
}
