<?php
	/**
	 * Curly
	 * Easy to use, general purpose CuRL wrapper
	 * @author 	biohzrdmx <github.com/biohzrdmx>
	 * @version 2.0
	 * @license MIT
	 */

	namespace Curly;

	class Response {

		/**
		 * Response body
		 * @var string
		 */
		protected $body;

		/**
		 * Response headers
		 * @var array
		 */
		protected $headers;

		/**
		 * Response status code
		 * @var integer
		 */
		protected $status;

		/**
		 * Constructor
		 * @param string  $body    Response body
		 * @param array   $headers Response headers
		 * @param integer $status  Response status code
		 */
		function __construct($body, $headers, $status) {
			$this->body = $body;
			$this->headers = $headers;
			$this->status = $status;
		}

		/**
		 * Get response body
		 * @param  boolean $raw Whether to return the raw response or process it according to the content-type (JSON/XML currently supported)
		 * @return mixed        The response body, either raw or an object representing it
		 */
		function getBody($raw = false) {
			$ret = $this->body;
			if (!$raw) {
				$content_type = $this->getHeader('content-type');
				switch ($content_type) {
					case 'application/json':
						$ret = @json_decode($this->body);
					break;
					case 'application/xml':
					case 'text/xml':
						$ret = @simplexml_load_string($this->body);
					break;
				}
			}
			return $ret;
		}

		/**
		 * Get a response header
		 * @param  string $name    Header name
		 * @param  string $default Default value
		 * @return string          The header value or the default if it doesn't exist
		 */
		function getHeader($name, $default = null) {
			return isset( $this->headers[$name] ) ? $this->headers[$name] : $default;
		}

		/**
		 * Get response headers
		 * @return array The response headers
		 */
		function getHeaders() {
			return $this->headers;
		}

		/**
		 * Get response status code
		 * @return integer HTTP response status code
		 */
		function getStatus() {
			return $this->status;
		}
	}

?>