curly-php
=========

Easy to use, general purpose CuRL wrapper

### Basic usage

You'll need to include `cacher.inc.php` [from here](https://github.com/biohzrdmx/cacher-php) before including `curly.inc.php`.

	// Just grab a new instance, the boolean parameter controls caching:
	$curly = Curly::newInstance(false)
		->setMethod('get')
		->setURL('http://api.icndb.com/jokes/random')
		->setParams([ 'limitTo' => 'nerdy' ])
		->execute();
	// Then just get the response, you may even specify the format ('plain' or 'json')
	$res = $curly->getResponse('json');
	// Then just get the response, you may even specify the format ('plain' or 'json')
	$res = $curly->getResponse('json');
	// And just use the returned data
	if ($res && $res->type == 'success') {
		# Error checking may vary, here the API sets a } `type` member
		echo $res->value->joke;
	} else {
		echo 'API error: ' . $curly->getError();
	}

For `HTTPS` just grab a copy of `cacert.pem` [from here](https://curl.haxx.se/docs/caextract.html) and drop it on the same folder where the `curly.inc.php` file is located.

###Licensing

This software is released under the MIT license.

Copyright © 2017 biohzrdmx

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

###Credits

**Lead coder:** biohzrdmx [github.com/biohzrdmx](http://github.com/biohzrdmx)