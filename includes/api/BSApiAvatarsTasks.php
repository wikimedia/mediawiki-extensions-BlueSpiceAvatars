<?php

use BlueSpice\Api\Response\Standard;
use BlueSpice\Avatars\Generator;

class BSApiAvatarsTasks extends BSApiTasksBase {

	/**
	 *
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
		],
		'setUserImage' => [
			'examples' => [
				[
					'userImage' => 'ProfileImage.png'
				]
			],
			'params' => [
				'userImage' => [
					'desc' => 'Name of the image to set',
					'type' => 'string',
					'required' => true
				]
			]
		]
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'uploadFile' => [ 'read' ],
			'generateAvatar' => [ 'read' ],
			'setUserImage' => [ 'read' ]
		];
	}

	// phpcs:disable
	/**
	 *
	 * @param stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 * @throws MWException
	 */
	public function task_uploadFile( $oTaskData, $aParams ) {
		// phpcs:enable
		$oResponse = $this->makeStandardReturn();
		$oUser = $this->getUser();
		\BlueSpice\Avatars\Extension::unsetUserImage( $oUser );
		$sAvatarFileName = Generator::FILE_PREFIX . $oUser->getId() . ".png";
		$oStatus = BsFileSystemHelper::uploadAndConvertImage(
			$this->getRequest()->getVal( 'name' ),
			'Avatars',
			$sAvatarFileName
		);
		if ( !$oStatus->isGood() ) {
			$oResponse->message = $oStatus->getMessage()->text();
			return $oResponse;
		}

		# found no way to regenerate thumbs. just delete thumb folder if it exists
		$oStatus = BsFileSystemHelper::deleteFolder(
			"Avatars/thumb/$sAvatarFileName",
			true
		);
		if ( !$oStatus->isGood() ) {
			throw new MWException( 'FATAL: Avatar thumbs could no be deleted!' );
		}

		$oResponse->message = $this->msg( 'bs-avatars-upload-complete' )->plain();
		$oResponse->success = true;
		return $oResponse;
	}

	// phpcs:disable
	/**
	 *
	 * @param stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 * @throws MWException
	 */
	public function task_setUserImage( $oTaskData, $aParams ) {
		// phpcs:enable
		$urlUtils = $this->services->getUrlUtils();
		$oResponse = $this->makeStandardReturn();
		$sUserImage = $oTaskData->userImage;
		// check if string is URL or valid file
		$oFile = $this->services->getRepoGroup()->findFile( $sUserImage );
		$bIsImage = is_object( $oFile ) && $oFile->canRender();
		if ( !$urlUtils->parse( $sUserImage ) && !$bIsImage ) {
			$oResponse->message = $this->msg( 'bs-avatars-set-userimage-failed' )->plain();
			return $oResponse;
		}

		$oUser = $this->getUser();
		$this->services->getUserOptionsManager()
			->setOption( $oUser, 'bs-avatars-profileimage', $sUserImage );
		$oUser->saveSettings();

		$oResponse->success = true;
		$oResponse->message = $this->msg( 'bs-avatars-set-userimage-saved' )->plain();
		return $oResponse;
	}

	// phpcs:disable
	/**
	 *
	 * @param stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 * @throws MWException
	 */
	public function task_generateAvatar( $oTaskData, $aParams ) {
		// phpcs:enable
		$oResponse = $this->makeStandardReturn();

		$oUser = $this->getUser();
		\BlueSpice\Avatars\Extension::unsetUserImage( $oUser );
		$generator = $this->services->getService( 'BSAvatarsAvatarGenerator' );
		$generator->generate( $oUser, [ Generator::PARAM_OVERWRITE => true ] );

		$oResponse->success = true;
		$oResponse->message = $this->msg( 'bs-avatars-generate-complete' )->plain();
		return $oResponse;
	}

}
