# BlueskyApi

Simple class for making requests to the Bluesky API/AT protocol.  Not affiliated with Bluesky.

## Usage

```php
use cjrasmussen\BlueskyApi\BlueskyApi;

$bluesky = new BlueskyApi($handle, $app_password);

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
```

## Installation

Simply add a dependency on cjrasmussen/bluesky-api to your composer.json file if you use [Composer](https://getcomposer.org/) to manage the dependencies of your project:

```sh
composer require cjrasmussen/bluesky-api
```

Although it's recommended to use Composer, you can actually include the file(s) any way you want.


## License

BlueskyApi is [MIT](http://opensource.org/licenses/MIT) licensed.