<?php

class PathwayCache extends FileCacheBase {
	static public function newFromKey( $name, $type ) {
		$cache = new self();

		$cache->mKey = (string)$name;
		$cache->mType = $type;
		$cache->mExt = $type;

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
	}

	public function getGpmlModificationTime() {
		$gpmlTitle = $this->getTitleObject();
		$gpmlRev = Revision::newFromTitle($gpmlTitle);
		if($gpmlRev) {
			$gpmlDate = $gpmlRev->getTimestamp();
		} else {
			throw new Exception("No GPML page");
		}
		return $gpmlDate;
	}

	/**
	 * Save a cached version of a filetype to be converted
	 * from GPML
	 */
	private function saveConvertedCache($fileType) {
		# Convert gpml to fileType
		$gpmlFile = $this->getFileLocation(FILETYPE_GPML);
		if( !file_exists( $gpmlFile ) ) {
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
		return $conFile;
	}

	public function getPath() {
		return $this->cachePath();
	}

}