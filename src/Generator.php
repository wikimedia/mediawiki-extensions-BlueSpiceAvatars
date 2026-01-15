<?php

namespace BlueSpice\Avatars;

use File;
use MediaWiki\Config\Config;
use MediaWiki\Message\Message;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler;
use RuntimeException;

class Generator {
	public const FILE_PREFIX = "BS_avatar_";

	public const PARAM_OVERWRITE = 'overwrite';
	public const PARAM_HEIGHT = 'height';
	public const PARAM_WIDTH = 'width';

	/**
	 *
	 * @param Config $config
	 * @param AvatarGeneratorFactory $factory
	 * @param StorageHandler $storageHandler
	 * @param \RepoGroup $repoGroup
	 */
	public function __construct(
		private readonly Config $config,
		private readonly AvatarGeneratorFactory $factory,
		private readonly StorageHandler $storageHandler,
		private readonly \RepoGroup $repoGroup
	) {
	}

	/**
	 * @param User $user
	 * @param array $params
	 * @return void
	 * @throws RuntimeException
	 */
	public function generate( User $user, array $params = [] ) {
		$defaultSize = 1024;

		$oFile = $this->getAvatarFile( $user );

		if ( !$oFile->exists() || isset( $params[static::PARAM_OVERWRITE] ) ) {
			$generator = $this->factory->newFromName(
				$this->config->get( 'AvatarsGenerator' )
			);
			if ( !$generator ) {
				throw new RuntimeException(
					"Avatar generator '{$this->config->get( 'AvatarsGenerator' )}' not found!"
				);
			}

			$rawPNGAvatar = $generator->generate( $user, $defaultSize );

			$status = $this->storageHandler->newTransaction()
				->create( $oFile->getName(), $rawPNGAvatar, 'Avatars', [ 'overwrite' => true ] )
				->deleteDirectory( "Avatars/thumb/{$oFile->getName()}" )
				->commit();

			if ( !$status->isGood() ) {
				throw new RuntimeException(
					'FATAL: Avatar could not be saved! ' .
					Message::newFromSpecifier( $status->getMessages()[0] )->text()
				);
			}

			$user->invalidateCache();
		}
	}

	/**
	 * Gets Avatar file from user ID
	 * @param User $user
	 * @return File|null
	 */
	public function getAvatarFile( User $user ): ?File {
		$repo = $this->repoGroup->getRepoByName( 'Avatars' );
		return $repo->newFile( static::FILE_PREFIX . $user->getId() . ".png" );
	}
}
