<?php

namespace LaravelFCM\Request;

use LaravelFCM\Message\Topics;
use LaravelFCM\Message\Options;
use LaravelFCM\Message\PayloadData;
use LaravelFCM\Message\PayloadNotification;

/**
 * Class Request.
 */
class Request extends BaseRequest
{
    /**
     * @internal
     *
     * @var string|array
     */
    protected $to;

    /**
     * @internal
     *
     * @var Options
     */
    protected $options;

    /**
     * @internal
     *
     * @var PayloadNotification
     */
    protected $notification;

    /**
     * @internal
     *
     * @var PayloadData
     */
    protected $data;

    /**
     * @internal
     *
     * @var Topics|null
     */
    protected $topic;

    /**
     * Request constructor.
     *
     * @param                     $to
     * @param Options             $options
     * @param PayloadNotification $notification
     * @param PayloadData         $data
     * @param Topics|null         $topic
     */
    public function __construct($to, Options $options = null, PayloadNotification $notification = null, PayloadData $data = null, Topics $topic = null)
    {
        parent::__construct();

        $this->to = $to;
        $this->options = $options;
        $this->notification = $notification;
        $this->data = $data;
        $this->topic = $topic;
    }

    /**
     * Build the body for the request.
     *
     * @return array
     */
    protected function buildBody()
    {
        $preFormatNotification = $this->getNotification();
        $preFormatOptions = $this->getOptions();
        
        $message = [
            'message' => [
                'topic' => $this->getTopic(),
                'notification' => [
                    'title' => $preFormatNotification['title'],
                    'body' => $preFormatNotification['body'],
                ],
                'data' => $this->getData(),
            ]
        ];

        // remove null entries
        return $this->arrayFilterRecursive($message);
    }

    protected function arrayFilterRecursive($input) {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = array_filter_recursive($value);
            }
        }
    
        // Return filtered array, you can also use array_filter($input) without a callback to remove all falsy values
        return array_filter($input, function($value) {
            return !is_null($value);
        });
    }

    /**
     * get to key transformed.
     *
     * @return array|null|string
     */
    protected function getTo()
    {
        $to = is_array($this->to) ? null : $this->to;

        if ($this->topic && $this->topic->hasOnlyOneTopic()) {
            $to = $this->topic->build();
        }

        return $to;
    }

    /**
     * get topic key transformed.
     *
     * @return array|null|string
     */
    protected function getTopic()
    {
        $topic = null;

        if ($this->topic && $this->topic->hasOnlyOneTopic()) {
            $topic = $this->topic->build();
        }

        return $topic;
    }

    /**
     * get registrationIds transformed.
     *
     * @return array|null
     */
    protected function getRegistrationIds()
    {
        return is_array($this->to) ? $this->to : null;
    }

    /**
     * get Options transformed.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = $this->options ? $this->options->toArray() : [];

        if ($this->topic && !$this->topic->hasOnlyOneTopic()) {
            $options = array_merge($options, $this->topic->build());
        }

        return $options;
    }

    /**
     * get notification transformed.
     *
     * @return array|null
     */
    protected function getNotification()
    {
        return $this->notification ? $this->notification->toArray() : null;
    }

    /**
     * get data transformed.
     *
     * @return array|null
     */
    protected function getData()
    {
        return $this->data ? $this->data->toArray() : null;
    }
}
