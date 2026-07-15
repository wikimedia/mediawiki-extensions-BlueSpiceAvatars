<?php

namespace BlueSpice\Avatars;

use MediaWiki\Request\WebRequest;
use MediaWiki\Status\Status;
use MWStake\MediaWiki\Component\FileStorageUtilities\StorageHandler;
use RequestContext;
use StatusValue;
use UploadFromFile;

class AvatarHelper {

	public function __construct(
		private readonly StorageHandler $storageHandler
	) {
	}

	/**
	 * @param string $name
	 * @param string $filename
	 * @return StatusValue
	 */
	public function uploadAndConvertImage( string $name, string $filename ) {
		$webRequest = new WebRequest();
		$webRequestUpload = $webRequest->getUpload( $name );
		$uploadFromFile = new UploadFromFile();

		$uploadFromFile->initialize(
			RequestContext::getMain()->getRequest()->getVal( 'name' ),
			$webRequestUpload
		);
		$status = $uploadFromFile->verifyUpload();

		if ( $status['status'] != 0 ) {
			return StatusValue::newFatal(
				wfMessage( $uploadFromFile->getVerificationErrorCode( $status['status'] ) )->text()
			);
		}

		$tempName = $this->storageHandler->getTempFilePath( $filename, 'Avatars' );
		$uploadPath = $webRequestUpload->getTempName();
		[ $iWidth, $iHeight, $iType ] = getimagesize( $uploadPath );
		switch ( $iType ) {
			case IMAGETYPE_GIF:
				$rImage = imagecreatefromgif( $uploadPath );
				break;
			case IMAGETYPE_JPEG:
				$rImage = imagecreatefromjpeg( $uploadPath );
				break;
			case IMAGETYPE_PNG:
				$rImage = imagecreatefrompng( $uploadPath );
				break;
			default:
				return Status::newFatal( wfMessage( 'bs-avatars-upload-unsupported-type' ) );
		}

		$iNewWidth = $iNewHeight = 1024;
		$fRatio = $iWidth / $iHeight;
		if ( $fRatio < 1 ) {
			# portrait
			$iNewWidth = $iNewHeight * $fRatio;
		} else {
			# landscape
			$iNewHeight = $iNewWidth / $fRatio;
		}
		$rNewImage = imagecreatetruecolor( $iNewWidth, $iNewHeight );
		imagealphablending( $rNewImage, false );
		imagesavealpha( $rNewImage, true );
		$iTransparent = imagecolorallocatealpha( $rNewImage, 255, 255, 255, 127 );
		imagefilledrectangle( $rNewImage, 0, 0, $iNewWidth, $iNewHeight, $iTransparent );
		imagecopyresampled( $rNewImage, $rImage, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $iWidth, $iHeight );
		imagepng( $rNewImage, $tempName );

		# Move image to main storage
		$tempFile = $this->storageHandler->getTempFile( $filename, 'Avatars' );
		if ( !$tempFile ) {
			return StatusValue::newFatal( wfMessage( 'bs-avatars-upload-tempfile-missing' )->text() );
		}
		$content = file_get_contents( $tempFile->getPath() );
		$status = $this->storageHandler->newTransaction()
			->create( $filename, $content, 'Avatars', [ 'overwrite' => true ] )
			->commit();

		$this->storageHandler->newTransaction( true )
			->delete( $filename, 'Avatars' )
			->commit();

		if ( !$status->isOK() ) {
			return $status;
		}

		return Status::newGood( $webRequestUpload->getName() );
	}

	/**
	 * @param string $avatarName
	 * @return StatusValue
	 */
	public function deleteThumbs( string $avatarName ): StatusValue {
		return $this->storageHandler->newTransaction()
			->deleteDirectory( "Avatars/thumb/$avatarName" )
			->commit();
	}
}
