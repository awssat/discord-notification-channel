# Laravel Discord Notification Channel 

### Introduction

Send Discord messages through webhook with Discord or Slack payload via Laravel Notifications channels

## Features
- Support slack payload by using `new  (new SlackMessage)` or `$this->toSlack($notifiable)`
- Support discord webhook payload
- Easy to use 

## Install

Via Composer
``` bash
composer require awssat/discord-notification-channel
``` 

## Usage
in your notification you should define the `discord` channel in the via method

```php
public function via($notifiable)
{
    return ['mail', 'discord'];
}
```

you should have a `toDiscord` method

```php
    public function toDiscord($notifiable)
    {
        return (new DiscordMessage)
            ->from('Laravel')
            ->content('Content')
            ->embed(function ($embed) {
                $embed->title('Discord is cool')->description('Slack nah')
                    ->field('Laravel', '7.0.0', true)
                    ->field('PHP', '8.0.0', true);
            });
    }
```

`toDiscord` method can receive `DiscordMessage` or `SlackMessage`

#### Example of slack message

```php
    public function toDiscord($notifiable)
    {
        return (new SlackMessage)
                ->content('One of your invoices has been paid!');
    }
```

or if you want you can make it run from `toSlack` method

```php
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
                ->content('One of your invoices has been paid!');
    }

    public function toDiscord($notifiable)
    {
        return $this->toSlack($notifiable);
    }
```

https://laravel.com/docs/6.x/notifications#slack-notifications for further laravel slack messages examples

### Routing Discord Notifications

To route Discord notifications to the proper location, define a `routeNotificationForDiscord` method on your notifiable entity. This should return the webhook URL to which the notification should be delivered. read Webhook Discord docs here https://support.discordapp.com/hc/en-us/articles/228383668-Intro-to-Webhooks


```php
<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Route notifications for the Discord channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForDiscord($notification)
    {
        return 'https://discordapp.com/api/webhooks/.......';
    }
}

```


