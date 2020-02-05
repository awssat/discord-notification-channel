<?php


namespace Awssat\Notifications\Messages;


class DiscordMessage
{
    /**
     * The message contents (up to 2000 characters).
     *
     * @var string
     */
    public $content;

    /**
     * Override the default username of the webhook.
     *
     * @var string|null
     */
    public $username;

    /**
     * Override the default avatar of the webhook.
     *
     * @var string|null
     */
    public $avatar_url;

    /**
     * true if this is a TTS message.
     *
     * @var string|null
     */
    public $tts;


    /**
     * Embedded rich content.
     *
     * @var array
     */
    public $embeds;

    /**
     * Http options
     *
     * @var array
     */
    public $http = [];


    /**
     * Set the content of the message.
     *
     * @param string $content
     *
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Override the default username and avatar url of the webhook.
     *
     * @param string $username
     * @param string|null $avatar_url
     *
     * @return $this
     */
    public function from($username, $avatar_url = null)
    {
        $this->username = $username;

        if (! is_null($avatar_url)) {
            $this->avatar_url = $avatar_url;
        }

        return $this;
    }

    /**
     * Send as a TTS message.
     *
     * @param bool|null $enabled
     *
     * @return $this
     */
    public function tts($enabled = true)
    {
        $this->tts = $enabled ? 'true' : 'false';

        return $this;
    }


    /**
     * Define an embedded rich content for the message.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function embed(\Closure $callback)
    {
        $this->embeds[] = $embed = new DiscordEmbed;

        $callback($embed);

        return $this;
    }


    /**
     * Set additional request options for the Guzzle HTTP client.
     *
     * @param  array  $options
     * @return $this
     */
    public function http(array $options)
    {
        $this->http = $options;

        return $this;
    }
}