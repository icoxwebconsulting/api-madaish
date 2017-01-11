<?php

namespace AppBundle\Services;

use ZendService\Google\Gcm\Client;
use ZendService\Google\Gcm\Message;

/**
 * Class GCM
 *
 * @package AppBundle\Services
 */
class GCM
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $packageName;

    /**
     * GCM constructor.
     *
     * @param string $token
     * @param string $packageName
     */
    public function __construct($token, $packageName)
    {
        $this->packageName = $packageName;
        $this->client = new Client();
        $this->client->setApiKey($token);

        $httpClient = new \Zend\Http\Client(null, array(
            'adapter' => 'Zend\Http\Client\Adapter\Socket',
            'sslverifypeer' => false
        ));
        $this->client->setHttpClient($httpClient);
    }

    /**
     * Send push notification
     *
     * @param $tokens
     * @param $title
     * @param $text
     * @param $data
     * @param $collapseKey
     * @param bool $delay
     * @param int $ttl
     * @param bool $dry
     * @return array
     */
    public function sendNotification($tokens, $title, $text, $data, $collapseKey, $delay = false, $ttl = 600, $dry = false)
    {
        $stats = ["total" => count($tokens), "successful" => 0, "failed" => 0];
        // up to 100 registration ids can be sent to at once
        $chunks = array_chunk($tokens, 100);

        foreach ($chunks as $chunk) {
            $message = new Message();
            $message->setRegistrationIds($chunk);

            // optional fields
            $base = [
                'title' => $title,
                'message' => $text,
                'content-available' => 1
            ];
            $messageData = array_merge_recursive($base, $data);
            $message->setData($messageData);
            $message->setCollapseKey($collapseKey);
            $message->setRestrictedPackageName($this->packageName);
            $message->setDelayWhileIdle($delay);
            $message->setTimeToLive($ttl);
            $message->setDryRun($dry);

            $response = $this->client->send($message);
            $stats["successful"] += $response->getSuccessCount();
            $stats["failed"] += $response->getFailureCount();
        }

        return $stats;
    }
}