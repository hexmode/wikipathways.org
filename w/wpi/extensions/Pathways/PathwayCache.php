<?php

class PathwayCache extends FileCacheBase {
	static public function newFromKey( $name, $type ) {
		wfProfileIn( __METHOD__ );
		$cache = new self();

		$cache->mKey = (string)$name;
		$cache->mType = $type;
		$cache->mExt = $type;

		wfProfileOut( __METHOD__ );
		return $cache;
	}

	/**
	 * Get the base file cache directory
	 * @return string
	 */
	protected function cacheDirectory() {
		return $this->baseCacheDirectory() . '/pathways';
	}

	/**
	 * Clear all cached files
	 * \param fileType The file type to remove the cache for (one of FILETYPE_* constants)
	 * or null to remove all files
	 */
	public function clearCache($fileType = null) {
		wfProfileIn( __METHOD__ );
		if(!$fileType) { //Update all
			$this->clearCache(FILETYPE_PNG);
			$this->clearCache(FILETYPE_GPML);
			$this->clearCache(FILETYPE_IMG);
		} else {
			$file = $this->getFileName($fileType, false);
			if( $file ) {
				$repo->delete( $file, "archive" );
			}
		}
		wfProfileOut( __METHOD__ );
	}

	public function getGpmlModificationTime() {
		wfProfileIn( __METHOD__ );
		$gpmlTitle = $this->getTitleObject();
		$gpmlRev = Revision::newFromTitle($gpmlTitle);
		if($gpmlRev) {
			$gpmlDate = $gpmlRev->getTimestamp();
		} else {
			wfProfileOut( __METHOD__ );
			throw new Exception("No GPML page");
		}
		wfProfileOut( __METHOD__ );
		return $gpmlDate;
	}

	/**
	 * Save a cached version of a filetype to be converted
	 * from GPML
	 */
	private function saveConvertedCache($fileType) {
		wfProfileIn( __METHOD__ );
		# Convert gpml to fileType
			$gpmlFile = $this->getFileLocation(FILETYPE_GPML);
		if( !file_exists( $gpmlFile ) ) {
			wfProfileOut( __METHOD__ );
			throw new MWException( "File does not exist: $gpmlFile" );
		}
		$gpmlFile = realpath( $gpmlFile );
		$conFile = $this->getFileLocation($fileType, false);
		$outFile = basename( $gpmlFile, FILETYPE_GPML );

		if ( $conFile === null ) {
			$conFile = wfTempDir() . "/$outFile$fileType";
		}
		wfDebug( "Saving $gpmlFile to $fileType in $conFile\n" );
		self::convert($gpmlFile, $conFile);
		wfProfileOut( __METHOD__ );
		return $conFile;
	}

	public function getPath() {
		return $this->cachePath();
	}

	public function saveText( $pathway ) {
		wfProfileIn( __METHOD__ );
		if ( $this->useGzip() ) {
			$text = gzencode( $pathway->serialize() );
		} else {
			$text = $pathway->serialize();
		}

		$this->checkCacheDirs(); // build parent dir
		if ( !file_put_contents( $this->cachePath(), $text, LOCK_EX ) ) {
			wfDebug( __METHOD__ . "() failed saving ". $this->cachePath() . "\n" );
			$this->mCached = null;
			wfProfileOut( __METHOD__ );
			return false;
		}

		$this->mCached = true;
		wfProfileOut( __METHOD__ );
		return $text;
	}

}