<?php

namespace Awssat\Notifications\Channels;

use Awssat\Notifications\Messages\DiscordEmbed;
use Awssat\Notifications\Messages\DiscordEmbedField;
use Awssat\Notifications\Messages\DiscordMessage;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackAttachmentField;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class DiscordWebhookChannel
{

    /**
     * The HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * Create a new Slack channel instance.
     *
     * @param  \GuzzleHttp\Client  $http
     * @return void
     */
    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $url = $notifiable->routeNotificationFor('discord', $notification)) {
            return;
        }

        $message = $notification->toDiscord($notifiable);

        if($message instanceof SlackMessage) {
            return $this->http->post($url . '/slack', $this->buildSlackJsonPayload($message));
        }

        return $this->http->post($url, $this->buildDiscordJsonPayload($message));
    }

    /**
     * Build up a JSON payload for the Discord webhook.
     *
     * @param DiscordMessage $message
     * @return array
     */
    protected function buildDiscordJsonPayload(DiscordMessage $message)
    {
        $optionalFields = array_filter([
            'username' => data_get($message, 'username'),
            'avatar_url' => data_get($message, 'avatar_url'),
            'tts' => data_get($message, 'tts'),
            'timestamp' => data_get($message, 'timestamp'),
        ]);

        return array_merge([
            'json' => array_merge([
                'content' => $message->content,
                'embeds' => $this->embeds($message),
            ], $optionalFields),
        ], $message->http);
    }


    /**
     * Format the message's embedded content.
     *
     * @param DiscordMessage $message
     *
     * @return array
     */
    protected function embeds(DiscordMessage $message)
    {
        return collect($message->embeds)->map(function (DiscordEmbed $embed) {
            return array_filter([
                'color' => $embed->color,
                'title' => $embed->title,
                'description' => $embed->description,
                'link' => $embed->url,
                'thumbnail' => $embed->thumbnail,
                'image' => $embed->image,
                'footer' => $embed->footer,
                'author' => $embed->author,
                'fields' => $this->embedFields($embed),
            ]);
        })->all();
    }

    protected function embedFields(DiscordEmbed $embed)
    {
        return collect($embed->fields)->map(function ($value, $key) {
            if ($value instanceof DiscordEmbedField) {
                return $value->toArray();
            }

            return ['name' => $key, 'value' => $value, 'inline' => true];
        })->values()->all();
    }

    /**
     * Build up a JSON payload for the Slack webhook.
     *
     * @param  \Illuminate\Notifications\Messages\SlackMessage  $message
     * @return array
     */
    protected function buildSlackJsonPayload(SlackMessage $message)
    {
        $optionalFields = array_filter([
            'channel' => data_get($message, 'channel'),
            'icon_emoji' => data_get($message, 'icon'),
            'icon_url' => data_get($message, 'image'),
            'link_names' => data_get($message, 'linkNames'),
            'unfurl_links' => data_get($message, 'unfurlLinks'),
            'unfurl_media' => data_get($message, 'unfurlMedia'),
            'username' => data_get($message, 'username'),
        ]);

        return array_merge([
            'json' => array_merge([
                'text' => $message->content,
                'attachments' => $this->attachments($message),
            ], $optionalFields),
        ], $message->http);
    }

    /**
     * Format the message's attachments.
     *
     * @param  \Illuminate\Notifications\Messages\SlackMessage  $message
     * @return array
     */
    protected function attachments(SlackMessage $message)
    {
        return collect($message->attachments)->map(function ($attachment) use ($message) {
            return array_filter([
                'actions' => $attachment->actions,
                'author_icon' => $attachment->authorIcon,
                'author_link' => $attachment->authorLink,
                'author_name' => $attachment->authorName,
                'color' => $attachment->color ?: $message->color(),
                'fallback' => $attachment->fallback,
                'fields' => $this->fields($attachment),
                'footer' => $attachment->footer,
                'footer_icon' => $attachment->footerIcon,
                'image_url' => $attachment->imageUrl,
                'mrkdwn_in' => $attachment->markdown,
                'pretext' => $attachment->pretext,
                'text' => $attachment->content,
                'thumb_url' => $attachment->thumbUrl,
                'title' => $attachment->title,
                'title_link' => $attachment->url,
                'ts' => $attachment->timestamp,
            ]);
        })->all();
    }

    /**
     * Format the attachment's fields.
     *
     * @param  \Illuminate\Notifications\Messages\SlackAttachment  $attachment
     * @return array
     */
    protected function fields(SlackAttachment $attachment)
    {
        return collect($attachment->fields)->map(function ($value, $key) {
            if ($value instanceof SlackAttachmentField) {
                return $value->toArray();
            }

            return ['title' => $key, 'value' => $value, 'short' => true];
        })->values()->all();
    }
}