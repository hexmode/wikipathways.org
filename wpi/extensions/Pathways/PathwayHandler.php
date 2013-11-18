<?php

class PathwayHandler extends ContentHandler {

	public function __construct( $x ) {
		parent::__construct( CONTENT_MODEL_PATHWAY, array( $x ) );
	}

	/**
	 * Serializes a Content object of the type supported by this ContentHandler.
	 *
	 * @since 1.21
	 *
	 * @param $content Content The Content object to serialize
	 * @param $format null|String The desired serialization format
	 * @return string Serialized form of the content
	 */
	public function serializeContent( Content $content, $format = null ) {
		var_dump( __METHOD__ );
	}

	/**
	 * Unserializes a Content object of the type supported by this ContentHandler.
	 *
	 * @since 1.21
	 *
	 * @param string $blob serialized form of the content
	 * @param $format null|String the format used for serialization
	 * @return Content the Content object created by deserializing $blob
	 */
	public function unserializeContent( $blob, $format = null ) {
		var_dump( __METHOD__ );
	}

	/**
	 * Creates an empty Content object of the type supported by this
	 * ContentHandler.
	 *
	 * @since 1.21
	 *
	 * @return Content
	 */
	public function makeEmptyContent() {
		var_dump( __METHOD__ );
	}

}