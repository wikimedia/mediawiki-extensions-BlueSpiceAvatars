<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use BlueSpice\DynamicFileDispatcher\Module;

class Image extends \BlueSpice\DynamicFileDispatcher\File {
	/**
	 *
	 * @var string
	 */
	protected $src = '';

	/**
	 *
	 * @var \User
	 */
	protected $user = null;

	/**
	 *
	 * @param Module $dfd
	 * @param string $src
	 * @param \User $user
	 */
	public function __construct( Module $dfd, $src, $user ) {
		parent::__construct( $dfd );
		$this->src = $src;
		$this->user = $user;
	}

	/**
	 * Sets the headers for given \WebResponse
	 * @param \WebResponse $response
	 * @return void
	 */
	public function setHeaders( \WebResponse $response ) {
		$headers = [];

		$headers[] = 'Cache-Control: private';
		$headers[] = 'Vary: Cookie';
		$headers[] = 'Content-type: ' . $this->getMimeType();

		// This is temporay code until the UserMiniProfile gets a rewrite
		$path = $GLOBALS['IP'];
		$scriptPath = $this->dfd->getConfig()->get( 'ScriptPath' );
		if ( $scriptPath && $scriptPath != "" ) {
			$countDirs = substr_count( $scriptPath, '/' );
			$i = 0;
			while ( $i < $countDirs ) {
				$path = dirname( $path );
				$i++;
			}
		}
		$path = str_replace(
			[ '/img_auth.php/', '/nsfr_img_auth.php/' ],
			'/images/',
			$path . '/' . \BsFileSystemHelper::normalizePath( $this->src )
		);

		$streamer = new \HTTPFileStreamer(
			$path,
			[
				'obResetFunc' => null,
				'streamMimeFunc' => null
			]
		);

		$res = $streamer->stream( $headers, true );
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType() {
		return 'image/png';
	}
}
