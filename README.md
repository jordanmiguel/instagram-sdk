# Instagram PHP SDK
A simple PHP SDK for Instagram API. Provides a wrapper for making authenticated requests.

## Installation
The easiest way to install is via [Composer](https://getcomposer.org/):

```
composer require danieltrolezi/instagram-sdk: 2.0.*
```

## Usage
To instantiate the class, you simply need to provide an ```CLIENT ID``` and ```CLIENTE SECRET```:

```php
$instagram = new Instagram('CLIENT ID', 'CLIENTE SECRET');
```

### Authentication

Any API call will required an valid access token. First, you set the same ```REDIRECT URI``` registered on the [Instagram Developer Portal](https://www.instagram.com/developer/clients/manage/). Then, you can redirect the user to the login URL.

```php
$instagram->setRedirectUri('REDIRECT URI');
header('location: ' . $instagram->getLoginURL());
```

Once the user authorizes the application, Instagram will redirect to the ```REQUEST URI``` with a ```code``` parameter that can be exchange for an access token:

```php
$access_token = $instagram->getAccessToken($_GET['code']);
```

Now you can start make requests to the API. The ```getAccessToken``` method will automatically set the received access token so you don't need to the pass it in every request.
If you chose to store the access token and make the request later, you can use the ```setAccessToken``` method.

### Making requests

To get information about the owner of the access token, all you need to do is:

```php
 $user = $instagram->call('users/self');
```
