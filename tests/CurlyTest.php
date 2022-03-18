<?php
	namespace Curly\Tests;

	use PHPUnit\Framework\TestCase;
	use Curly\Curly;
	use Curly\Response;

	class CurlyTest extends TestCase {

		public static function execInBackground($cmd) {
			if (substr(php_uname(), 0, 7) == "Windows"){
				pclose(popen("start /B ". $cmd, "r"));
			} else {
				exec($cmd . " > /dev/null &");
			}
		}

		public static function setUpBeforeClass(): void {
			$script = dirname(__FILE__) . '/server.js';
			$cmd = sprintf('node "%s" 8080 &', $script);
			self::execInBackground($cmd);
			fwrite(STDOUT, 'Starting node server...' . "\n");
			sleep(2);
		}

		public static function tearDownAfterClass(): void {
			fwrite(STDOUT, "\n" . 'Stopping node server...');
			$curly = Curly::newInstance()
				->setMethod('DELETE')
				->setURL('http://localhost:8080')
				->execute();
			sleep(2);
		}

		public function testRequest() {
			$header = null;
			$headers = null;
			$curly = Curly::newInstance()
				->setMethod('GET')
				->setURL('http://localhost:8080')
				->setOptions([CURLOPT_AUTOREFERER => true])
				->execute();
			$response = $curly->getResponse();
			if ($response) {
				$header = $response->getHeader('access-control-allow-origin');
				$headers = $response->getHeaders();
			}
			$error = $curly->getError();
			$info = $curly->getInfo();
			$this->assertInstanceOf(Response::class, $response);
			$this->assertSame(200, $response->getStatus());
			$this->assertEquals('*', $header);
			$this->assertIsArray($headers);
			$this->assertIsArray($info);
			$this->assertEmpty($error);
		}

		public function testResponse() {
			$response = new Response('foo', [], 200);
			$this->assertInstanceOf(Response::class, $response);
			$this->assertEquals('foo', $response->getBody());
		}

		public function testParams() {
			$body = '';
			$curly = Curly::newInstance()
				->setMethod('GET')
				->setURL('http://localhost:8080')
				->setParams( ['foo' => 'bar'] )
				->execute();
			$response = $curly->getResponse();
			if ($response) {
				$body = $response->getBody();
			}
			$this->assertInstanceOf(Response::class, $response);
			$this->assertSame(200, $response->getStatus());
			#
			$this->assertStringStartsWith('GET /?foo=bar HTTP', $body);
		}

		public function testFields() {
			$body = null;
			$curly = Curly::newInstance()
				->setMethod('POST')
				->setURL('http://localhost:8080')
				->setFields( ['foo' => 'bar'] )
				->execute();
			$response = $curly->getResponse();
			if ($response) {
				$body = $response->getBody();
			}
			$this->assertInstanceOf(Response::class, $response);
			$this->assertSame(200, $response->getStatus());
			#
			$this->assertStringStartsWith('POST / HTTP', $body);
			$this->assertStringContainsString('Content-Type: application/x-www-form-urlencoded', $body);
			$this->assertStringEndsWith('foo=bar', $body);
		}

		public function testOther() {
			$body = null;
			$curly = Curly::newInstance()
				->setMethod('PUT')
				->setURL('http://localhost:8080')
				->setHeaders( ['Content-Type' => 'application/json'] )
				->setBody( json_encode( ['foo' => 'bar'] ) )
				->execute();
			$response = $curly->getResponse();
			if ($response) {
				$body = $response->getBody();
			}
			$this->assertInstanceOf(Response::class, $response);
			$this->assertSame(200, $response->getStatus());
			#
			$this->assertStringStartsWith('PUT / HTTP', $body);
			$this->assertStringContainsString('Content-Type: application/json', $body);
			$this->assertStringEndsWith('{"foo":"bar"}', $body);
		}

		public function testDownload() {
			$body = null;
			$source = dirname(__FILE__) . '/data.txt';
			$target = dirname(__FILE__) . '/download.txt';
			$resource = fopen($target, 'wb');
			if ($resource) {
				$curly = Curly::newInstance()
					->setMethod('GET')
					->setResource($resource)
					->setURL('http://localhost:8080/download')
					->execute();
				$response = $curly->getResponse();
				#
				fclose($resource);
				#
				$hash_source = md5( file_get_contents($source) );
				$hash_target = md5( file_get_contents($target) );
				#
				$this->assertInstanceOf(Response::class, $response);
				$this->assertSame(200, $response->getStatus());
				$this->assertSame($hash_source, $hash_target);
			} else {
				$this->fail();
			}
		}
	}

?>