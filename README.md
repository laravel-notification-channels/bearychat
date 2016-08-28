# BearyChat notifications channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/bearychat.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/bearychat)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/laravel-notification-channels/bearychat/master.svg?style=flat-square)](https://travis-ci.org/laravel-notification-channels/bearychat)
[![StyleCI](https://styleci.io/repos/66657812/shield)](https://styleci.io/repos/66657812)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/69edea46-6837-4c7c-9b2f-11c8e320379b.svg?style=flat-square)](https://insight.sensiolabs.com/projects/69edea46-6837-4c7c-9b2f-11c8e320379b)
[![Quality Score](https://img.shields.io/scrutinizer/g/laravel-notification-channels/bearychat.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/bearychat)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/laravel-notification-channels/bearychat/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/bearychat/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/bearychat.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/bearychat)

This package makes it easy to send notifications using [BearyChat][] with Laravel 5.3.

## Contents

- [Installation](#installation)
  - [Setting up the BearyChat service](#setting-up-the-bearychat-service)
- [Usage](#usage)
  - [Basic Usage](#basic-usage)
  - [Routing Notifications](#routing-notifications)
  - [Available Message Methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

You can install the package via [Composer][]:

```sh
$ composer require laravel-notification-channels/bearychat
```

Once package is installed, you need to register the service provider by adding the following to the `providers` array in `config/app.php`:

```php
NotificationChannels\BearyChat\BearyChatServiceProvider::class,
```

This package is based on [Laravel-BearyChat][], then you may publish the config file if you have not done yet:

```sh
$ php artisan vendor:publish --provider="ElfSundae\BearyChat\Laravel\ServiceProvider"
```

### Setting up the BearyChat service

You may create an Incoming Robot in your [BearyChat][] team account, and read the [payload format][Incoming].

## Usage

### Basic Usage

You can now use the channel in the `via()` method inside the Notification class.

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\BearyChat\BearyChatChannel;
use ElfSundae\BearyChat\Message;

class InvoicePaid extends Notification
{
    public function via($notifiable)
    {
        return [BearyChatChannel::class];
    }

    public function toBearyChat($notifiable)
    {
        return (new Message)
            ->text('foo')
            ->add('bar');
    }
}
```

You can also use a BearyChat `Client`  to create the message for notification, and the client's message defaults will be used for creating this new `Message` instance.

```php
public function toBearyChat($notifiable)
{
    return bearychat('admin')->text('New VIP has been paid!');
}
```

> + For more details about BearyChat `Client` and `Message`, please [read the documentation][PHP-BearyChat] of the BearyChat PHP package.
> + For more details about `BearyChat` facade or `bearychat()` helper function, please [read the documentation][Laravel-BearyChat] of the original Laravel package.

### Routing Notifications

To route BearyChat notifications to the proper Robot, define a `routeNotificationForBearyChat` method on your notifiable entity. 

```php
class User extends Authenticatable
{
    use Notifiable;

    public function routeNotificationForBearyChat()
    {
        return 'https://hook.bearychat.com/...';
    }
}
```

You can also route the user, channel or configured client in the `routeNotificationForBearyChat` method.

- `'@Elf'` will route the notification to user "Elf".
- `'#iOS-Dev'` will route the notification to channel "iOS-Dev".
- `'http://webhook/url'` will route the notification to an Incoming Robot.
- `'Server'` will route the notification via a client which named "Server" in your config file `config/bearychat.php`, and the message defaults of this client will be applied to the outgoing notification message.

### Available Message Methods

- `text()` : (string) Message content.
- `notification()` : (string) Message notification.
- `markdown(true)` : (boolean) Indicates the message should be parsed as markdown syntax.
- `add()` : (mixed) Add an attachment to the message. The parameter can be an payload array that contains all of attachment's fields. The parameters can also be attachment's fields that in order of "text", "title", "images" and "color".
- `remove()` : (mixed) Remove attachment(s), you can pass an integer of attachment index, or an array of indices.
- `channel()` : (string) The channel that the message should be sent to.
- `user()` : (string) The user that the message should be sent to.
- `to()` : (string) The target (user or channel) that the message should be sent to. The target may be started with "**@**" for sending to an user, and the channel's starter mark "**#**" is optional.

```php
$message = (new Message)
    ->text('message content')
    ->notification('notification for this message')
    ->add('attachment content', 'attachment title', 'http://path/to/image', '#FF0000')
    ->to('@Boss');
```

A notification uses the above Message instance will be sent with the following payload:

```json
{
    "text": "message content",
    "notification": "notification for this message",
    "user": "Boss",
    "attachments": [
        {
            "text": "attachment content",
            "title": "attachment title",
            "images": [
                {
                    "url": "http://path/to/image"
                }
            ],
            "color": "#FF0000"
        }
    ]
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email elf.sundae@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Elf Sundae](https://github.com/ElfSundae)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


[BearyChat]: https://bearychat.com
[Composer]: https://getcomposer.org
[Laravel-BearyChat]: https://github.com/ElfSundae/Laravel-BearyChat
[PHP-BearyChat]: https://github.com/ElfSundae/BearyChat
[Incoming]: https://bearychat.com/integrations/incoming
