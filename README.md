# BlueskyApi

Simple class for making requests to the Bluesky API/AT protocol.  Not affiliated with Bluesky.

## Usage

### Starting a session

Starting a session requires a handle and password.

```php
use cjrasmussen\BlueskyApi\BlueskyApi;

$bluesky = new BlueskyApi();

try {
    $bluesky->auth($handle, $app_password);
} catch (Exception $e) {
    // TODO: Handle the exception however you want
}
```

### Getting a refresh token

If you're running up against rate limits by repeatedly creating a session, you may want to cache a refresh token and use that to refresh your session instead of starting a new one.  Cache it however you want for later usage, or see the session helper below.

```php
$refresh_token = $bluesky->getRefreshToken();
```

### Refreshing a session

You can use that cached refresh token later to refresh your session instead of starting a new session.

```php
try {
    $bluesky->auth($refresh_token);
} catch (Exception $e) {
    // TODO: Handle the exception however you want
}
```

### Sending a message

```php
$args = [
	'collection' => 'app.bsky.feed.post',
	'repo' => $bluesky->getAccountDid(),
	'record' => [
		'text' => 'Testing #TestingInProduction',
		'langs' => ['en'],
		'createdAt' => date('c'),
		'$type' => 'app.bsky.feed.post',
	],
];
$data = $bluesky->request('POST', 'com.atproto.repo.createRecord', $args);
```

### Sending a message with a hashtag

The above example has a hashtag in the text, however it will not be rendered as a hashtag. You must explicitly define text as a hashtag when posting via the Bluesky API as the service won't do it for you.

```php
$args = [
	'collection' => 'app.bsky.feed.post',
	'repo' => $bluesky->getAccountDid(),
	'record' => [
		'text' => 'Testing #TestingInProduction',
		'facets' => [
			[
				'index' => [
					'byteStart' => 8,
					'byteEnd' => 28,
				],
				'features' => [
					[
						'$type' => 'app.bsky.richtext.facet#tag',
						'tag' => 'TestingInProduction',
					],
				],
			],		
		],
		'langs' => ['en'],
		'createdAt' => date('c'),
		'$type' => 'app.bsky.feed.post',
	],
];
$data = $bluesky->request('POST', 'com.atproto.repo.createRecord', $args);
```

### Sending a message with a link

Similarly, you must explicitly define links in text when posting via the Bluesky API.

```php
$args = [
	'collection' => 'app.bsky.feed.post',
	'repo' => $bluesky->getAccountDid(),
	'record' => [
		'text' => 'Testing https://cjr.dev',
		'facets' => [
			[
				'index' => [
					'byteStart' => 8,
					'byteEnd' => 23,
				],
				'features' => [
					[
						'$type' => 'app.bsky.richtext.facet#link',
						'uri' => 'https://cjr.dev',
					],
				],
			],		
		],
		'langs' => ['en'],
		'createdAt' => date('c'),
		'$type' => 'app.bsky.feed.post',
	],
];
$data = $bluesky->request('POST', 'com.atproto.repo.createRecord', $args);
```

### Sending a message with an attached image

This assumes that your image file is a PNG

```php
$body = file_get_contents($file);
$response = $bluesky->request('POST', 'com.atproto.repo.uploadBlob', [], $body, 'image/png');
$image = $response->blob;

$args = [
	'collection' => 'app.bsky.feed.post',
	'repo' => $bluesky->getAccountDid(),
	'record' => [
		'text' => 'Testing with an image #TestingInProduction',
		'langs' => ['en'],
		'createdAt' => date('c'),
		'$type' => 'app.bsky.feed.post',
		'embed' => [
			'$type' => 'app.bsky.embed.images',
			'images' => [
				[
					'alt' => 'A test image',
					'image' => $image,
				],
			],
		],
	],
];
$response = $bluesky->request('POST', 'com.atproto.repo.createRecord', $args);
```

### Using the session helper to manage refresh token caching

As mentioned above, you can manually cache a session refresh token however you want. The BlueskyApiSessionHelper::auth method is one way of doing that. Provide the path to a file containing a refresh token and the method will refresh your session and update the cache file with the new refresh token. Optionally provide a handle and (app) password to fall back on creating a new session if the refresh token fails.

```php
use cjrasmussen\BlueskyApi\BlueskyApi;
use cjrasmussen\BlueskyApi\BlueskyApiSessionHelper;

$blueskyApi = new BlueskyApi();
$blueskyApiSessionHelper = new BlueskyApiSessionHelper($blueskyApi);

try {
    $blueskyApiSessionHelper->auth($refresh_token_path, $handle, $password);
} catch (Exception $e) {
    // TODO: Handle the exception however you want
}
```

### Getting response header for API requests

Bluesky returns data about rate limits in the header of each API request response. The most recent request response header can be accessed as a string as follows:

```php
$blueskyApi->getLastResponseHeader();
```

The header can then be parsed as necessary.

## Installation

Simply add a dependency on cjrasmussen/bluesky-api to your composer.json file if you use [Composer](https://getcomposer.org/) to manage the dependencies of your project:

```sh
composer require cjrasmussen/bluesky-api
```

Although it's recommended to use Composer, you can actually include the file(s) any way you want.

## Further Reference

It's not much, but I do have some Bluesky API-related stuff [on my blog](https://cjr.dev/?s=bluesky). Additionally, there's an unofficial "Bluesky API Touchers" Discord (which seems to be invite-only) with a PHP-specific channel.

## License

BlueskyApi is [MIT](http://opensource.org/licenses/MIT) licensed.
