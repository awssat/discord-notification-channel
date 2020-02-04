<?php


namespace Awssat\Notifications\Messages;


class DiscordEmbedField
{

    /**
     * The name of the field.
     *
     * @var string
     */
    public $name;

    /**
     * The value of the field.
     *
     * @var string
     */
    public $value;

    /**
     * Whether or not this field should display inline.
     *
     * @var bool
     */
    public $inline;

    /**
     * Set the name of the field.
     *
     * @param string $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of the field.
     *
     * @param string $value
     *
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set the name of the field.
     *
     * @param bool|null $inline
     *
     * @return $this
     */
    public function inline($inline = true)
    {
        $this->inline = boolval($inline);

        return $this;
    }

    /**
     * Get an array representation of the embedded field.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'inline' => $this->inline,
        ];
    }
}