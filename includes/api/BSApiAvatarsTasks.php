<?php

use BlueSpice\Api\Response\Standard;
use BlueSpice\Avatars\Generator;

class BSApiAvatarsTasks extends BSApiTasksBase {

	/**
	 * @var array
	 */
	protected $aTasks = [
		'uploadFile' => [
			'examples' => [],
			'params' => []
		],
		'generateAvatar' => [
			'examples' => [],
			'params' => []
		]
	];

	/**
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'uploadFile' => [ 'upload' ],
			'generateAvatar' => [ 'read' ],
		];
	}

	/**
	 * @param stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 * @throws RuntimeException
	 */
	public function task_uploadFile( $oTaskData, $aParams ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$oResponse = $this->makeStandardReturn();
		$oUser = $this->getUser();
		\BlueSpice\Avatars\Extension::unsetUserImage( $oUser );
		$sAvatarFileName = Generator::FILE_PREFIX . $oUser->getId() . ".png";

		$helper = new \BlueSpice\Avatars\AvatarHelper(
			$this->services->getService( 'MWStake.StorageUtilities' )
		);
		$status = $helper->uploadAndConvertImage( $this->getRequest()->getVal( 'name' ), $sAvatarFileName );
		if ( !$status->isGood() ) {
			$oResponse->message = \MediaWiki\Message\Message::newFromSpecifier( $status->getMessages()[0] )->text();
			return $oResponse;
		}

		$helper->deleteThumbs( $sAvatarFileName );

		$oResponse->message = $this->msg( 'bs-avatars-upload-complete' )->text();
		$oResponse->success = true;
		return $oResponse;
	}

	/**
	 * @param stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_generateAvatar( $oTaskData, $aParams ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$oResponse = $this->makeStandardReturn();

		$oUser = $this->getUser();
		\BlueSpice\Avatars\Extension::unsetUserImage( $oUser );
		/** @var Generator */
		$generator = $this->services->getService( 'BSAvatarsAvatarGenerator' );
		$generator->generate( $oUser, [ Generator::PARAM_OVERWRITE => true ] );

		$oResponse->success = true;
		$oResponse->message = $this->msg( 'bs-avatars-generate-complete' )->text();
		return $oResponse;
	}

}
