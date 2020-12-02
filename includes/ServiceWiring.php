<?php

use MediaWiki\MediaWikiServices;

use BlueSpice\Avatars\AvatarGeneratorFactory;
use BlueSpice\Avatars\Generator;
use BlueSpice\ExtensionAttributeBasedRegistry;

return [

	'BSAvatarsAvatarGenerator' => function ( MediaWikiServices $services ) {
		return new Generator(
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getService( 'BSAvatarsAvatarGeneratorFactory' )
		);
	},

	'BSAvatarsAvatarGeneratorFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceAvatarsAvatarGeneratorRegistry'
		);
		return new AvatarGeneratorFactory(
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$registry
		);
	},
];
