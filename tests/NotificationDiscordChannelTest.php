<?php

namespace Awssat\Tests\Notifications;

use Awssat\Notifications\Channels\DiscordWebhookChannel;
use Awssat\Notifications\Messages\DiscordEmbed;
use Awssat\Notifications\Messages\DiscordMessage;
use GuzzleHttp\Client;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
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
        });

        $this->discordChannel->send(new NotificationDiscordChannelTestNotifiable, $notification);
    }

    public function payloadDataProvider()
    {
        return [
            'payloadWithDiscord' => $this->getPayloadWithDiscord(),
            'payloadWithSlackMessage' => $this->getPayloadWithSlackMessage(),
        ];
    }

    private function getPayloadWithSlackMessage()
    {
        return [
            new NotificationDiscordChannelTestNotificationWithSlack,
            'url/slack',
            [
                'json' => [
                    'username' => 'Ghostbot',
                    'icon_emoji' => ':ghost:',
                    'channel' => '#ghost-talk',
                    'text' => 'Content',
                    'attachments' => [
                        [
                            'title' => 'Laravel',
                            'title_link' => 'https://laravel.com',
                            'text' => 'Attachment Content',
                            'fallback' => 'Attachment Fallback',
                            'fields' => [
                                [
                                    'title' => 'Project',
                                    'value' => 'Laravel',
                                    'short' => true,
                                ],
                            ],
                            'mrkdwn_in' => ['text'],
                            'footer' => 'Laravel',
                            'footer_icon' => 'https://laravel.com/fake.png',
                            'author_name' => 'Author',
                            'author_link' => 'https://laravel.com/fake_author',
                            'author_icon' => 'https://laravel.com/fake_author.png',
                            'ts' => 1234567890,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getPayloadWithDiscord()
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

class NotificationDiscordChannelTestNotificationWithSlack extends Notification
{
    public function toDiscord($notifiable)
    {
        return (new SlackMessage)
            ->from('Ghostbot', ':ghost:')
            ->to('#ghost-talk')
            ->content('Content')
            ->attachment(function ($attachment) {
                $timestamp = m::mock(Carbon::class);
                $timestamp->shouldReceive('getTimestamp')->andReturn(1234567890);
                $attachment->title('Laravel', 'https://laravel.com')
                    ->content('Attachment Content')
                    ->fallback('Attachment Fallback')
                    ->fields([
                        'Project' => 'Laravel',
                    ])
                    ->footer('Laravel')
                    ->footerIcon('https://laravel.com/fake.png')
                    ->markdown(['text'])
                    ->author('Author', 'https://laravel.com/fake_author', 'https://laravel.com/fake_author.png')
                    ->timestamp($timestamp);
            });
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
