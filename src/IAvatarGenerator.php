<?php

namespace BlueSpice\Avatars;

use User;

interface IAvatarGenerator {
	/**
	 * @return string
	 */
	public function getName();

	/**
	 *
	 * @param User $user
	 * @param type $size
	 * @param array $params
	 * @return string
	 */
	public function generate( User $user, $size, array $params = [] );
}
