<?php

namespace BlueSpice\Avatars\Hook;

use BlueSpice\Avatars\DynamicFileDispatcher\UserProfileImage;
use BlueSpice\Avatars\Generator;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserOptionsLookup;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\MWStakeDynamicFileDispatcherRegisterModuleHook;
use RepoGroup;

class RegisterDynamicFileModule implements MWStakeDynamicFileDispatcherRegisterModuleHook {

	/** @var UserOptionsLookup */
	private $optionsLookup;

	/** @var RepoGroup */
	private $repoGroup;

	/** @var Generator */
	private $generator;

	/** @var UserFactory */

	/**
	 * @param RepoGroup $repoGroup
	 * @param UserOptionsLookup $optionsLookup
	 * @param Generator $generator
	 * @param UserFactory $userFactory
	 */
	public function __construct(
		RepoGroup $repoGroup, UserOptionsLookup $optionsLookup, Generator $generator, UserFactory $userFactory
	) {
		$this->optionsLookup = $optionsLookup;
		$this->repoGroup = $repoGroup;
		$this->generator = $generator;
		$this->userFactory = $userFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeDynamicFileDispatcherRegisterModule( &$modules ) {
		$modules['userprofileimage'] = new UserProfileImage(
			$this->optionsLookup, $this->repoGroup, $this->generator, $this->userFactory
		);
	}
}
