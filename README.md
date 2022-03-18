curly-php
=========

Easy to use, general purpose CuRL wrapper

### Basic usage

First require `biohzrdmx/curly-php` with Composer.

Now get an instance of `Curly`, prepare your request and execute it:

```php
use Curly\Curly;

$curly = Curly::newInstance()
  ->setMethod('GET')
  ->setURL('https://api.icndb.com/jokes/random')
  ->setParams([ 'limitTo' => 'nerdy' ])
  ->execute();
```

Once executed you'll need to call the `getResponse` method to retrieve the `Response` object and use the data for whathever you like:

```php
$response = $curly->getResponse();
if ($response && $response->getStatus() == 200) {
  $body = $response->getBody();
  if ($body->type == 'success') {
    echo $body->value->joke;
  }
} else {
  echo 'API error: ' . $curly->getError();
}
```

It's important to note that `getBody` will parse `JSON` and `XML` payloads if the response has a valid `Content-Type` header, but you may disable that by passing `true` as the first parameter to get a `raw` response body;

Also if the request fails you may call `getError` or `getInfo` to find the cause of the failure.

The `Response` object can also retrieve the value of a single header with the `getHeader` method or all the response headers via the `getHeaders` one.

As this internally uses the `curl` extension, it supports `HTTPS` out of the box if you have a properly configured server, but you may specify the location of you CA bundle by passing it as the first parameter in the `newInstance` call:

```php
$cainfo = '/absolute/path/to/cacert.pem';
$curly = Curly::newInstance($cainfo);
```

Just grab a copy of `cacert.pem` [from here](https://curl.haxx.se/docs/caextract.html) and pass its absolute path. Curly will automatically honor your `php.ini` if its properly set there.

#### Downloading files

Starting from version 2.2 it's possible to stream file downloads directly to a file handle, to do so you will need to pass a resource before executing the request:

```php
$resource = fopen('/absolute/path/to/file.zip', 'wb');
if ($resource) {
  $curly = Curly::newInstance()
    ->setMethod('GET')
    ->setResource($resource)
    ->setURL('https://my-web-app.com/assets/file.zip')
    ->execute();
  $response = $curly->getResponse();
  fclose($resource);
}
```

In this case the file data is written directly to the resource, so `$response->getBody()` will return an empty string, but you can check the status with `$response->getStatus()` and get the corresponding headers with `$response->getHeader()` and `$response->getHeaders()`.

Remember to always close the file with `fclose`;

#### Uploading files

Also, there's support for file uploads with the `CURLFile` class,

```php
use CURLFile;

$avatar = new CURLFile('/absolute/path/to/avatar.png');
$curly = Curly::newInstance()
  ->setMethod('POST')
  ->setURL('https://my-web-app.com/api/users/profile')
  ->setFields( ['email' => 'foo@bar.baz', 'avatar' => $avatar] )
  ->execute();
```

Just add your files like any other field and set the request method to `POST`, the class adjust everything automatically.

### Licensing

This software is released under the MIT license.

Copyright © 2022 biohzrdmx

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### Credits

**Lead coder:** biohzrdmx [github.com/biohzrdmx](http://github.com/biohzrdmx)