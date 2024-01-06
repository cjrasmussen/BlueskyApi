# BlueskyApi

Simple class for making requests to the Bluesky API/AT protocol.  Not affiliated with Bluesky.

## Usage

```php
use cjrasmussen\BlueskyApi\BlueskyApi;

$bluesky = new BlueskyApi();

// ESTABLISH SESSION WITH HANDLE AND PASSWORD
try {
    $bluesky->auth($handle, $app_password);
} catch (Exception $e) {
    // TODO: Handle the exception however you want
}

// GET REFRESH TOKEN FOR OPTIONAL CACHING
$refresh_token = $bluesky->getRefreshToken();

// SEND A MESSAGE
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

// SEND A MESSAGE WITH AN IMAGE, ASSUMING $file IS A PNG
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

### Optional Session Refresh

If you're running up against rate limits while creating sessions, you might want to cache the refresh token as noted above and use that to refresh your session instead of creating a new session. It should be noted that the refresh token can be used only once and a new one will be generated.

```php
try {
    $bluesky->auth($refresh_token);
} catch (Exception $e) {
    // TODO: Handle the exception however you want
}

$refresh_token = $bluesky->getRefreshToken();
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