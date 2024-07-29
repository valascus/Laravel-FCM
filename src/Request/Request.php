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

        if(!isset($preFormatOptions['priority')) {
            $android_priority = 'NORMAL';
            $apn_priority = 5;
        } else if($preFormatOptions['priority'] == 1) {
            $android_priority = 'NORMAL';
            $apn_priority = 1;
        } elseif($preFormatOptions['priority'] == 'HIGH' || $preFormatOptions['priority'] == 10) {
            $android_priority = 'HIGH';
            $apn_priority = 10;
        } else {
            $android_priority = 'NORMAL';
            $apn_priority = 5;
        }
        
        $message = [
            'message' => [
                'token' => $this->getTo(),
                'tokens' => $this->getRegistrationIds(),
                'topic' => $this->getTopic(),
                'notification' => [
                    'title' => $preFormatNotification['title'],
                    'body' => $preFormatNotification['body'],
                ],
                'data' => $this->getData(),
                'android' => [
                    'collapse_key' => $preFormatOptions['collapse_key'],
                    'priority' => $android_priority, // NORMAL | HIGH
                    'ttl' => $preFormatOptions['time_to_live'],
                    'restricted_package_name' => $preFormatOptions['restricted_package_name'],
                    'notification' => [
                        'channel_id' => $preFormatNotification['android_channel_id'],
                        'icon' => $preFormatNotification['icon'],
                        'sound' => $preFormatNotification['sound'] ?? "default",
                        'tag' => $preFormatNotification['tag'],
                        'color' => $preFormatNotification['color'],
                        'click_action' => $preFormatNotification['click_action'],
                        'body_loc_key' => $preFormatNotification['body_loc_key'],
                        'body_loc_args' => $preFormatNotification['body_loc_args'],
                        'title_loc_key' => $preFormatNotification['title_loc_key'],
                        'title_loc_args' => $preFormatNotification['title_loc_args'],
                    ]
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "badge" => $preFormatNotification['badge'],
                            "sound" => $preFormatNotification['sound'] ?? "default", // Use "default" or specify a sound file
                            "content-available" => $preFormatOptions['content_available'], // Use 1 for true
                            'mutable-content' => $preFormatOptions['mutable_content'],
                            "alert" => [
                                "loc-key" => $preFormatNotification['body_loc_key'],
                                "loc-args" => $preFormatNotification['body_loc_args'],
                                "title-loc-key" => $preFormatNotification['title_loc_key'],
                                "title-loc-args" => $preFormatNotification['title_loc_args'],
                            ],
                        ]
                    ],
                    "headers" => [
                        "apns-priority" => $apn_priority, // 1 | 5 | 10
                    ]
                ],
            ]
        ];

        // remove null entries
        return arrayFilterRecursive($message);
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
