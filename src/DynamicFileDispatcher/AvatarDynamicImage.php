<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use MediaWiki\Rest\Stream;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use Psr\Http\Message\StreamInterface;
use ThumbnailImage;

class AvatarDynamicImage implements IDynamicFile {

	/** @var ThumbnailImage */
	protected ThumbnailImage $thumb;

	/**
	 * @param ThumbnailImage $image
	 */
	public function __construct( ThumbnailImage $image ) {
		$this->thumb = $image;
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType(): string {
		return $this->thumb->getFile()->getMimeType();
	}

	/**
	 * @return StreamInterface
	 */
	public function getStream(): StreamInterface {
		return new Stream( fopen( $this->thumb->getLocalCopyPath(), 'rb' ) );
	}
}
