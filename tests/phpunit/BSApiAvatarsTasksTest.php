<?php

use BlueSpice\Tests\BSApiTasksTestBase;
use MediaWiki\Title\Title;

/*
 * Test BlueSpiceAvatars API Endpoints
 */

/**
 * @group BlueSpiceAvatars
 * @group BlueSpiceExtensions
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 * @covers \BSApiAvatarsTasks
 */
class BSApiAvatarsTasksTest extends BSApiTasksTestBase {

	/** @var array Used to fake $_FILES in tests and given to FauxRequest */
	protected $requestDataFiles = [];

	/** @inheritDoc */
	protected function buildFauxRequest( $params, $session ) {
		$request = parent::buildFauxRequest( $params, $session );
		$request->setUploadData( $this->requestDataFiles );
		return $request;
	}

	protected function getModuleName() {
		return "bs-avatars-tasks";
	}

	/**
	 * 'generateAvatar' => [
	 *	   'examples' => [],
	 *	   'params' => []
	 *	],
	 */
	public function testGenerateAvatar() {
		$data = $this->executeTask(
		  'generateAvatar', []
		);

		$this->assertTrue( $data->success, "Avatar was not generated" );
	}

	/**
	 *
	 * 'uploadFile' => [
	 *	   'examples' => [],
	 *	   'params' => []
	 * ],
	 */
	public function testUploadFile() {
		// create example image
		$extension = 'jpg';
		$mimeType = 'image/jpg';

		try {
			$randomImageGenerator = new RandomImageGenerator();
			$filePaths = $randomImageGenerator->writeImages( 1, $extension, $this->getNewTempDirectory() );
		} catch ( Exception $e ) {
			$this->markTestIncomplete( $e->getMessage() );
		}

		/** @var array $filePaths */
		$filePath = $filePaths[0];
		$fileSize = filesize( $filePath );
		$fileName = basename( $filePath );

		if ( !$this->fakeUploadFile( 'file', $fileName, $mimeType, $filePath ) ) {
			$this->markTestIncomplete( "Couldn't upload file!\n" );
		}

		// TODO: Complete test!
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
/*
		$_GET["name"] = $fileName;

		$data = $this->executeTask(
		  'uploadFile', []
		);

		$this->assertEquals( true, $data->success, $data->message);

		$this->deleteFileByFileName( $fileName );
		$this->deleteFileByContent( $filePath );
 *
 */
	}

	/**
	 * Helper function -- remove files and associated articles by Title
	 *
	 * @param Title $title Title to be removed
	 *
	 * @return bool
	 */
	public function deleteFileByTitle( $title ) {
		if ( $title->exists() ) {
			$services = $this->getServiceContainer();
			$file = $services->getRepoGroup()
				->findFile( $title, [ 'ignoreRedirect' => true ] );
			// yes this really needs to be set this way
			$noOldArchive = "";
			$comment = "removing for test";
			$restrictDeletedVersions = false;
			$user = $this->getTestSysop()->getUser();
			$status = FileDeleteForm::doDelete(
				$title,
				$file,
				$noOldArchive,
				$comment,
				$restrictDeletedVersions,
				$user
			);

			if ( !$status->isGood() ) {
				return false;
			}

			$page = $services->getWikiPageFactory()->newFromTitle( $title );
			$deletePage = $services->getDeletePageFactory()->newDeletePage( $page, $user );
			$deletePage->deleteIfAllowed( 'removing for test' );

			// see if it now doesn't exist; reload
			$title = Title::newFromText( $title->getText(), NS_FILE );
		}

		return !( $title && $title instanceof Title && $title->exists() );
	}

	/**
	 * Helper function -- remove files and associated articles with a particular filename
	 *
	 * @param string $fileName Filename to be removed
	 *
	 * @return bool
	 */
	public function deleteFileByFileName( $fileName ) {
		return $this->deleteFileByTitle( Title::newFromText( $fileName, NS_FILE ) );
	}

	/**
	 * Helper function -- given a file on the filesystem, find matching
	 * content in the db (and associated articles) and remove them.
	 *
	 * @param string $filePath Path to file on the filesystem
	 *
	 * @return bool
	 */
	public function deleteFileByContent( $filePath ) {
		$hash = FSFile::getSha1Base36FromPath( $filePath );
		$dupes = $this->getServiceContainer()->getRepoGroup()->findBySha1( $hash );
		$success = true;
		foreach ( $dupes as $dupe ) {
			$success &= $this->deleteFileByTitle( $dupe->getTitle() );
		}

		return $success;
	}

	/**
	 * Fake an upload by dumping the file into temp space, and adding info to $_FILES.
	 * (This is what PHP would normally do).
	 *
	 * @param string $fieldName Name this would have in the upload form
	 * @param string $fileName Name to title this
	 * @param string $type MIME type
	 * @param string $filePath Path where to find file contents
	 *
	 * @throws Exception
	 * @return bool
	 */
	protected function fakeUploadFile( $fieldName, $fileName, $type, $filePath ) {
		$tmpName = $this->getNewTempFile();
		if ( !file_exists( $filePath ) ) {
			throw new Exception( "$filePath doesn't exist!" );
		}

		if ( !copy( $filePath, $tmpName ) ) {
			throw new Exception( "couldn't copy $filePath to $tmpName" );
		}

		clearstatcache();
		$size = filesize( $tmpName );
		if ( $size === false ) {
			throw new Exception( "couldn't stat $tmpName" );
		}

		$this->requestDataFiles[$fieldName] = [
			'name' => $fileName,
			'type' => $type,
			'tmp_name' => $tmpName,
			'size' => $size,
			'error' => null
		];

		return true;
	}

	protected function fakeUploadChunk( $fieldName, $fileName, $type, &$chunkData ) {
		$tmpName = $this->getNewTempFile();
		// copy the chunk data to temp location:
		if ( !file_put_contents( $tmpName, $chunkData ) ) {
			throw new Exception( "couldn't copy chunk data to $tmpName" );
		}

		clearstatcache();
		$size = filesize( $tmpName );
		if ( $size === false ) {
			throw new Exception( "couldn't stat $tmpName" );
		}

		$this->requestDataFiles[$fieldName] = [
			'name' => $fileName,
			'type' => $type,
			'tmp_name' => $tmpName,
			'size' => $size,
			'error' => null
		];
	}

}
