<?php

namespace BlueSpice\Avatars\Html;

use BlueSpice\DynamicFileDispatcher\UrlBuilder;
use BlueSpice\Services;
use BlueSpice\DynamicFileDispatcher\Params;
use BlueSpice\Avatars\DynamicFileDispatcher\UserProfileImage;

/**
 * DEPRECATED
 * @deprecated since version 3.1 - use Services::getInstance()->getBSRendererFactory()
 * ->get( 'userimage'... instead
 */
class ProfileImage {

	/**
	 *
	 * @var \User
	 */
	protected $user = null;

	/**
	 *
	 * @var int
	 */
	protected $width = 32;

	/**
	 *
	 * @var int
	 */
	protected $height = 32;

	/**
	 *
	 * @var UrlBuilder
	 */
	protected $urlBuilder = null;

	/**
	 * DEPRECATED
	 * @deprecated since version 3.1 - use Services::getInstance()->getBSRendererFactory()
	 * ->get( 'userimage'... instead
	 * @param \User $user
	 * @param int $width
	 * @param int $height
	 * @param UrlBuilder|null $urlBuilder
	 */
	public function __construct( $user, $width = 32, $height = 32, $urlBuilder = null ) {
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		$this->user = $user;
		$this->width = $width;
		$this->height = $height;
		$this->urlBuilder = $urlBuilder;
		if ( $urlBuilder === null ) {
			$this->urlBuilder = Services::getInstance()
				->getBSDynamicFileDispatcherUrlBuilder();
		}
	}

	/**
	 * DEPRECATED
	 * @deprecated since version 3.1 - use Services::getInstance()->getBSRendererFactory()
	 * ->get( 'userimage'... instead
	 * @return string
	 */
	public function getHtml() {
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		$params = new Params( [
			Params::MODULE => UserProfileImage::MODULE_NAME,
			UserProfileImage::USERNAME => $this->user->getName(),
			UserProfileImage::WIDTH => $this->width,
			UserProfileImage::HEIGHT => $this->height
		] );

		$userHelper = Services::getInstance()->getBSUtilityFactory()
			->getUserHelper( $this->user );
		$displayUsername = $userHelper->getDisplayName();
		$attribs = [
			'src' => $this->urlBuilder->build( $params ),
			'width' => $this->width,
			'height' => $this->height,
			'alt' => wfMessage( 'bs-avatars-userimage-alt', $displayUsername )->plain(),
			'class' => 'bs-avatars-profile-image',
			'title' => $displayUsername
		];

		return \Html::element( 'img', $attribs );
	}
}
