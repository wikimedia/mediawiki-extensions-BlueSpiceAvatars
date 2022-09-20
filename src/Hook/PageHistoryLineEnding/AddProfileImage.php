<?php

namespace BlueSpice\Avatars\Hook\PageHistoryLineEnding;

use BlueSpice\Hook\PageHistoryLineEnding;
use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage as DFDImage;

class AddProfileImage extends PageHistoryLineEnding {
	protected function doProcess() {
		$this->history->getOutput()->addModuleStyles(
			'ext.bluespice.avatars.history.styles'
		);

		$services = $this->getServices();
		$user = $services->getUserFactory()->newFromName( $this->row->rev_user_text );
		if ( $user instanceof \User === false ) {
			return true;
		}
		$factory = $services->getService( 'BSRendererFactory' );
		$params = [
			DFDImage::PARAM_WIDTH => 32,
			DFDImage::PARAM_HEIGHT => 32,
			DFDImage::PARAM_USER => $user,
		];
		$renderer = $factory->get( 'userimage', new Params( $params ) );

		$this->s = preg_replace(
			"#(<span class='history-user'>)#",
			'$1' . $renderer->render(),
			$this->s
		);

		return true;
	}
}
