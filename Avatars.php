<?php

/**
 * Avatars extension for BlueSpice
 *
 * Provide generic and individual user images
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Marc Reymann <reymann@hallowelt.com>
 * @version    3.0.0
 * @package    BlueSpiceAvatars
 * @subpackage Avatars
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for the Avatars extension
 * @package BlueSpiceAvatars
 * @subpackage Avatars
 */
class Avatars extends \BlueSpice\Extension {

	/**
	 * extension.json callback
	 * @global array $wgForeignFileRepos
	 */
	public static function onRegistration() {
		global $wgForeignFileRepos;
		if ( version_compare( $GLOBALS['wgVersion'], '1.28c', '>' ) ) {
			$wgForeignFileRepos[] = array(
				'class' => 'FileRepo',
				'name' => 'Avatars',
				'directory' => BS_DATA_DIR . '/Avatars/',
				'hashLevels' => 0,
				'url' => BS_DATA_PATH . '/Avatars',
			);
		} else {
			$wgForeignFileRepos[] = array(
				'class' => 'FSRepo',
				'name' => 'Avatars',
				'directory' => BS_DATA_DIR . '/Avatars/',
				'hashLevels' => 0,
				'url' => BS_DATA_PATH . '/Avatars',
			);
		}
	}

	/**
	 * DEPRECATED - Use new \BlueSpice\Avatars\Generator()->getAvatarFile()
	 * instread
	 * Gets Avatar file from user ID
	 * @deprecated since version 3.0.0
	 * @param int $iUserId
	 * @return boolean|\File
	 */
	public static function getAvatarFile( $iUserId ) {
		$config = \BsExtensionManager::getExtension(
			'BlueSpiceAvatars'
		)->getConfig();
		$avatarGenerator = new \BlueSpice\Avatars\Generator( $config );
		return $avatarGenerator->getAvatarFile( \User::newFromId( $iUserId ) );
	}

	/**
	 * Clears a user's UserImage setting
	 * @param User $oUser
	 */
	public static function unsetUserImage($oUser) {
		if( $oUser->getOption( 'MW::UserImage' ) ) {
			$oUser->setOption( 'MW::UserImage', false );
			$oUser->saveSettings();
			$oUser->invalidateCache();
		}
		return;
	}

	/**
	 * DEPRECATED - Use new \BlueSpice\Avatars\Generator()->generate() instread
	 * Generate an avatar image
	 * @deprecated since version 3.0.0
	 * @param User $oUser
	 * @return string Relative URL to avatar image
	 */
	public function generateAvatar( $oUser, $aParams = array(), $bOverwrite = false ) {
		wfDeprecated( __METHOD__, "3.0.0" );
		$config = \BsExtensionManager::getExtension(
			'BlueSpiceAvatars'
		)->getConfig();
		$avatarGenerator = new \BlueSpice\Avatars\Generator( $config );

		if( $bOverwrite ) {
			$aParams[\BlueSpice\Avatars\Generator::PARAM_OVERWRITE] = true;
		}
		return $avatarGenerator->generate( $oUser, $aParams );
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}
