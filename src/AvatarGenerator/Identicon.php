<?php

namespace BlueSpice\Avatars\AvatarGenerator;

use BlueSpice\Avatars\AvatarGenerator;
use User;

class Identicon extends AvatarGenerator {

	/**
	 *
	 * @param User $user
	 * @param type $size
	 * @param array $params
	 * @return string
	 */
	public function generate( User $user, $size, array $params = [] ) {
		require_once dirname( dirname( __DIR__ ) ) . "/includes/lib/Identicon/identicon.php";
		return generateIdenticon( $user->getId(), $size );
	}

}
