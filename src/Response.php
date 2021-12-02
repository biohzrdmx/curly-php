<?php

declare(strict_types = 1);

/**
 * Curly
 * Easy to use, general purpose CuRL wrapper
 * @author 	biohzrdmx <github.com/biohzrdmx>
 * @version 2.1
 * @license MIT
 */

namespace Curly;

class Response {

	/**
	 * Response body
	 * @var string
	 */
	protected $body = '';

	/**
	 * Response headers
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Response status code
	 * @var integer
	 */
	protected $status = 0;

	/**
	 * Constructor
	 * @param string  $body    Response body
	 * @param array   $headers Response headers
	 * @param integer $status  Response status code
	 */
	function __construct(string $body, array $headers, int $status) {
		$this->body = $body;
		$this->headers = $headers;
		$this->status = $status;
	}

	/**
	 * Get response body
	 * @param  boolean $raw Whether to return the raw response or process it according to the content-type (JSON/XML currently supported)
	 * @return mixed
	 */
	function getBody(bool $raw = false) {
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
	 * @return string
	 */
	function getHeader(string $name, string $default = ''): string {
		return isset( $this->headers[$name] ) ? $this->headers[$name] : $default;
	}

	/**
	 * Get response headers
	 * @return array
	 */
	function getHeaders(): array {
		return $this->headers;
	}

	/**
	 * Get response status code
	 * @return integer
	 */
	function getStatus(): int {
		return $this->status;
	}
}
