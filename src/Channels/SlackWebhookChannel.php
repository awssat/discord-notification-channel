<?php


namespace Awssat\Notifications\Channels;


use Illuminate\Notifications\Channels\SlackWebhookChannel as SlackLaravelChannel;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackWebhookChannel extends SlackLaravelChannel
{
    /**
     * Build up a JSON payload for the Slack webhook.
     *
     * @param  \Illuminate\Notifications\Messages\SlackMessage  $message
     * @return array
     */
    public function buildJsonPayload(SlackMessage $message)
    {
        return parent::buildJsonPayload($message);
    }
}