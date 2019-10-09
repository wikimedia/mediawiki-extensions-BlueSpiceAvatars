<?php

namespace BlueSpice\Avatars\Html\FormField;

use BlueSpice\Services;
use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage as DFDImage;

class UserImage extends \HTMLTextField {

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->msg( 'bs-avatars-pref-userimage' )->parse();
	}

	/**
	 *
	 * @param string $value
	 * @return string
	 */
	public function getInputHTML( $value ) {
		$this->mParent->getOutput()->addModules( 'ext.bluespice.avatars.js' );
		$this->mParent->getOutput()->addModuleStyles(
			'ext.bluespice.avatars.preferences.styles'
		);

		$factory = Services::getInstance()->getBSRendererFactory();
		$params = [
			DFDImage::PARAM_WIDTH => 128,
			DFDImage::PARAM_HEIGHT => 128,
			DFDImage::PARAM_USER => $this->mParent->getUser(),
			DFDImage::PARAM_CLASS => 'bs-avatars-userimage-pref',
		];
		$renderer = $factory->get( 'userimage', new Params( $params ) );
		$button = \Html::element( 'a', [
			'href' => '#',
			'class' => 'bs-avatars-userimage-pref-btn'
		], $this->msg( 'bs-avatars-upload-title' )->plain() );
		$html = parent::getInputHTML( $value ) . $renderer->render() . $button;

		return $html;
	}

	/**
	 * Same as getInputHTML, but returns an OOUI object.
	 * Defaults to false, which getOOUI will interpret as "use the HTML version"
	 *
	 * @param string $value
	 * @return OOUI\Widget|false
	 */
	public function getInputOOUI( $value ) {
		return false;
	}
}
