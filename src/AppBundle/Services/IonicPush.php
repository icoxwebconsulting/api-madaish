<?php

namespace AppBundle\Services;

use Dmitrovskiy\IonicPush\PushProcessor;

/**
 * Class IonicPush
 *
 * @package AppBundle\Services
 */
class IonicPush
{
    /**
     * @var Client
     */
    private $client;

    /**
     * IonicPush constructor.
     *
     * @param string $token
     * @param string $profile
     */
    public function __construct($token, $profile)
    {
        $this->client = new PushProcessor(
            $profile,
            $token
        );
    }

    /**
     * Send push notification
     *
     * @param $tokens
     * @param $title
     * @param $message
     * @param $payload
     * @return array
     */
    public function sendNotification($tokens, $title, $message, $payload = array())
    {
        $stats = array();
        // up to 100 registration ids can be sent to at once
        $chunks = array_chunk($tokens, 100);

        foreach ($chunks as $token) {
           // $devices = array($token);
            $notification = array();

            $notification['title'] = $title;
            $notification['message'] = $message;
            $notification['payload'] = $payload;

            $response = $this->client->notify($token, $notification);
            $response->getBody()->rewind();
            $response = json_decode($response->getBody()->getContents(), true);

            print_r($response);

            if($response['meta']['status'] == 201)
                $stats['status'] = true;
            else
                $stats['status'] = false;
        }

        return $stats;
    }
}