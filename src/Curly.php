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

use Exception;

use Curly\Response;

class Curly {

	/**
	 * CA bundle path
	 * @var string
	 */
	protected $cainfo = '';

	/**
	 * HTTP method
	 * @var string
	 */
	protected $method = 'get';

	/**
	 * Request URL
	 * @var string
	 */
	protected $url = '';

	/**
	 * Request body
	 * @var string
	 */
	protected $body = '';

	/**
	 * Request parameters (GET)
	 * @var array
	 */
	protected $params = [];

	/**
	 * Request fields (POST)
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Request headers
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Extra CURL options
	 * @var array
	 */
	protected $options = [];

	/**
	 * Request response
	 * @var Response|null
	 */
	protected $response = null;

	/**
	 * Request info
	 * @var array
	 */
	protected $info = [];

	/**
	 * Last error
	 * @var string
	 */
	protected $error = '';

	/**
	 * Debug info
	 * @var string
	 */
	protected $debug = '';

	/**
	 * Verbose flag
	 * @var bool
	 */
	protected $verbose = false;

	/**
	 * Constructor
	 * @param string $cainfo Path for CA bundle
	 */
	function __construct(string $cainfo = '') {
		$this->cainfo = $cainfo;
		# Try to load the cainfo path from php.ini
		$this->cainfo = $this->cainfo ? $this->cainfo : (ini_get('curl.cainfo') ?: null);
		$this->cainfo = $this->cainfo ? $this->cainfo : (ini_get('openssl.cafile') ?: null);
	}

	/**
	 * Return a new instance
	 * @param  string $cainfo Path for CA bundle
	 * @return Curly
	 */
	static function newInstance(string $cainfo = ''): Curly {
		$new = new self($cainfo);
		return $new;
	}

	/**
	 * Set the HTTP method ('get, 'post', 'put', etc)
	 * @param  string $method The HTTP verb
	 * @return $this
	 */
	public function setMethod(string $method) {
		$this->method = strtolower($method);
		return $this;
	}

	/**
	 * Set the URL
	 * @param  string $url The URL to which the request will be made
	 * @return $this
	 */
	public function setURL(string $url) {
		$this->url = $url;
		return $this;
	}

	/**
	 * Set request body
	 * @param  string $body The request body
	 * @return $this
	 */
	public function setBody(string $body) {
		$this->body = $body;
		return $this;
	}

	/**
	 * Set the params for the current request
	 * @param  array $params Array of named params (associative) that will be passed on the URL
	 * @return $this
	 */
	public function setParams(array $params) {
		$this->params = $params;
		return $this;
	}

	/**
	 * Set the fields for the current request
	 * @param  array $fields Array of named fields (associative) that will be passed on the request body (application/x-www-form-urlencoded)
	 * @return $this
	 */
	public function setFields(array $fields) {
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Set the headers for the current request
	 * @param  array $headers Array of named headers (associative) that will be passed to the request
	 * @return $this
	 */
	public function setHeaders(array $headers) {
		$this->headers = $headers;
		return $this;
	}

	/**
	 * Set additional CuRL options for the current request
	 * @param  array $options Array of named options and its values (associative) in the form `array('CURLOPT_RETURNTRANSFER' => true)`
	 * @return $this
	 */
	public function setOptions(array $options) {
		$this->options = $options;
		return $this;
	}

	/**
	 * Set verbose flag
	 * @param bool $verbose Verbose flag
	 * @return $this
	 */
	public function setVerbose(bool $verbose) {
		$this->verbose = $verbose;
		return $this;
	}

	/**
	 * Get the response of the current request, once executed
	 * @return Response
	 */
	public function getResponse(): Response {
		return $this->response;
	}

	/**
	 * Get the last error
	 * @return string
	 */
	public function getError(): string {
		return $this->error;
	}

	/**
	 * Get debug info
	 * @return string
	 */
	public function getDebug(): string {
		return $this->debug;
	}

	/**
	 * Get response info
	 * @return array
	 */
	public function getInfo(): array {
		return $this->info;
	}

	/**
	 * Execute helper, you shouldn't worry about this
	 * @return $this
	 */
	public function execute() {
		# Create query string
		$query = http_build_query($this->params);
		$url = $this->url;
		if ($query) {
			$url = "{$this->url}?{$query}";
		}
		# Open connection
		$ch = curl_init();
		# Set the url, number of POST vars, POST data, etc
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		# Extra options
		if ($this->options) {
			foreach ($this->options as $key => $value) {
				curl_setopt($ch, $key, $value);
			}
		}
		# Add headers
		if ($this->headers) {
			$headers = array();
			foreach ($this->headers as $key => $value) {
				$headers[] = "{$key}: {$value}";
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
		# SSL
		if ( preg_match('/https:\/\//', $url) === 1 ) {
			if ($this->cainfo) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_CAINFO, $this->cainfo);
			} else {
				throw new Exception('Invalid Certificate Authority (CA) bundle path, you need a valid copy of it to perform HTTPS requests.');
			}
		}
		# POST/PUT/DELETE
		if ($this->method != 'get') {
			if ( is_array($this->fields) && count($this->fields) > 0 && $this->method == 'post' ) {
				# Standard POST
				$has_files = false;
				# Check for files
				foreach ($this->fields as $field) {
					if ( $field instanceof \CURLFile ) {
						$has_files = true;
						break;
					}
				}
				# Now prepare the request
				if ($has_files) {
					# Request has files, send them directly
					curl_setopt($ch, CURLOPT_POSTFIELDS, $this->fields);
				} else {
					# Request hasn't files, process the fields
					$fields = http_build_query($this->fields);
					curl_setopt($ch, CURLOPT_POST, count($this->fields));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
				}
			} else {
				# May be POST/PUT/DELETE or anything
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
			}
		}
		# Prepare response objects
		$body = '';
		$headers = [];
		$status = 0;
		# Headers callback
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
			$len = strlen($header);
			$header = explode(':', $header, 2);
			if (count($header) < 2)
				return $len;
			$headers[ strtolower( trim( $header[0] ) ) ] = trim( $header[1] );
			return $len;
		});
		$output = null;
		# Start debug
		if ($this->verbose) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			$output = fopen('php://temp', 'w+');
			curl_setopt($ch, CURLOPT_STDERR, $output);
		}
		# Execute request
		$body = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		# @phpstan-ignore-next-line
		$this->response = new Response($body ?: '', $headers, $status);
		# Get info about the request
		$this->error = curl_error($ch);
		$this->info = curl_getinfo($ch);
		# End debug
		if ( $this->verbose && is_resource($output) ) {
			rewind($output);
			$this->debug = stream_get_contents($output) ?: '';
			fclose($output);
		}
		# Close connection
		curl_close($ch);
		# Return self
		return $this;
	}
}
