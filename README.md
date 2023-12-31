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

If you're running up against rate limits by repeatedly creating a session, you may want to cache a refresh token and use that to refresh your session instead of starting a new one.  Cache it however you want for later usage.

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

## Installation

Simply add a dependency on cjrasmussen/bluesky-api to your composer.json file if you use [Composer](https://getcomposer.org/) to manage the dependencies of your project:

```sh
composer require cjrasmussen/bluesky-api
```

Although it's recommended to use Composer, you can actually include the file(s) any way you want.

## Further Reference

It's not much, but I do have some Bluesky API-related stuff [on my blog](https://cjr.dev/?s=bluesky). Additionally, there's an unofficial Discord for Bluesky API users with a [PHP-focused channel](https://discord.com/channels/1097580399187738645/1100721113702608999).

## License

BlueskyApi is [MIT](http://opensource.org/licenses/MIT) licensed.