<?php

namespace Awssat\Tests\Notifications;

use Awssat\Notifications\Channels\DiscordWebhookChannel;
use GuzzleHttp\Psr7\Response;
use Awssat\Notifications\Messages\DiscordMessage;
use GuzzleHttp\Client;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class NotificationDiscordChannelTest extends TestCase
{
    /**
     * @var DiscordWebhookChannel
     */
    private $discordChannel;

    /**
     * @var \Mockery\MockInterface|\GuzzleHttp\Client
     */
    private $guzzleHttp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzleHttp = m::mock(Client::class);

        $this->discordChannel = new DiscordWebhookChannel($this->guzzleHttp);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider payloadDataProvider
     * @param \Illuminate\Notifications\Notification $notification
     * @param array $payload
     */
    public function testCorrectPayloadIsSentToDiscord(Notification $notification, string $url, array $payload)
    {
        $this->guzzleHttp->shouldReceive('post')->andReturnUsing(function ($argUrl, $argPayload) use ($payload, $url) {
            $this->assertEquals($argUrl, $url);
            $this->assertEquals($argPayload, $payload);

            return new Response();
        });

        $this->discordChannel->send(new NotificationDiscordChannelTestNotifiable, $notification);
    }

    public static function payloadDataProvider()
    {
        return [
            'payloadWithDiscord' => self::getPayloadWithDiscord(),
        ];
    }

    private static function getPayloadWithDiscord()
    {
        return [
            new NotificationDiscordChannelTestNotificationWithDiscordMessage,
            'url',
            [
                'json' => [
                    'username' => 'Ghostbot',
                    'content' => 'Content',
                    'embeds' =>
                        [
                            [
                                'title' => 'Discord is cool',
                                'description' => 'Slack nah',
                                'fields' =>
                                    [
                                        [
                                            'name' => 'Laravel',
                                            'value' => '7.0.0',
                                            'inline' => true,
                                        ],
                                        [
                                            'name' => 'PHP',
                                            'value' => '8.0.0',
                                            'inline' => true,
                                        ],
                                    ],
                            ],
                        ],
                ],
            ],
        ];
    }

}

class NotificationDiscordChannelTestNotifiable
{
    use Notifiable;

    public function routeNotificationForDiscord()
    {
        return 'url';
    }
}

class NotificationDiscordChannelTestNotificationWithDiscordMessage extends Notification
{
    public function toDiscord($notifiable)
    {
        return (new DiscordMessage)
            ->from('Ghostbot')
            ->content('Content')
            ->embed(function ($embed) {
                $embed->title('Discord is cool')->description('Slack nah')
                    ->field('Laravel', '7.0.0', true)
                    ->field('PHP', '8.0.0', true);
            });
    }
}
