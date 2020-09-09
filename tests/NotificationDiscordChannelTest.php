<?php

namespace Awssat\Tests\Notifications;

use Awssat\Notifications\Channels\DiscordWebhookChannel;
use Awssat\Notifications\Messages\DiscordMessage;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class NotificationDiscordChannelTest extends TestCase
{
    private const DISCORD_SUCCESS_HTTP_CODE = 204;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $container = [];

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::now());

        $this->client = $this->setupGuzzleMock();
    }

    private function setupGuzzleMock()
    {
        $history = Middleware::history($this->container);

        $mock = new MockHandler([
            new Response(self::DISCORD_SUCCESS_HTTP_CODE, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        return new Client(['handler' => $handlerStack]);
    }

    /**
     * @dataProvider payloadDataProvider
     * @param \Illuminate\Notifications\Notification $notification
     * @param string $url
     * @param array $payload
     */
    public function testCorrectPayloadIsSentToDiscord(Notification $notification, string $url, array $payload)
    {
        $discordChannel = new DiscordWebhookChannel($this->client);

        $discordChannel->send(new NotificationDiscordChannelTestNotifiable, $notification);

        self::assertCount(1, $this->container);

        /** @var Request $request */
        $request = $this->container[0]['request'];
        $requestBody = json_decode($request->getBody()->getContents(), true);

        self::assertEquals(ltrim($request->getRequestTarget(), '/'), $url);
        self::assertEquals($requestBody, $payload['json']);
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
                            'ts' => Carbon::now()->timestamp,
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
                    ->timestamp(Carbon::now());
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
