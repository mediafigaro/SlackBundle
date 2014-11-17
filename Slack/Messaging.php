<?php

namespace DZunke\SlackBundle\Slack;

use DZunke\SlackBundle\Slack\Client\Actions;
use DZunke\SlackBundle\Slack\Messaging\Attachment;
use DZunke\SlackBundle\Slack\Messaging\IdentityBag;

class Messaging
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var IdentityBag
     */
    protected $identityBag;

    /**
     * @param Client      $client
     * @param IdentityBag $identityBag
     */
    public function  __construct(Client $client, IdentityBag $identityBag)
    {
        $this->client      = $client;
        $this->identityBag = $identityBag;
    }

    /**
     * @return IdentityBag
     */
    public function getIdentityBag()
    {
        return $this->identityBag;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string       $channel
     * @param string       $message
     * @param string       $identity
     * @param Attachment[] $attachments
     * @return Client\Response|bool
     * @throws \InvalidArgumentException
     */
    public function message($channel, $message, $identity, array $attachments = [])
    {
        if (!$this->identityBag->has($identity)) {
            throw new \InvalidArgumentException('identiy "' . $identity . '" is not registered');
        }

        return $this->client->send(
            Actions::ACTION_POST_MESSAGE,
            [
                'identity'    => $this->identityBag->get($identity),
                'channel'     => $channel,
                'text'        => $message,
                'attachments' => $attachments
            ],
            $identity
        );
    }


    /**
     * @param string        $channel
     * @param string        $title
     * @param string        $file
     * @param string|null   $comment
     * @return Client\Response
     */
    public function upload($channel, $title, $file, $comment = null)
    {
        if (!file_exists($file)) {
            return false;
        }

        $params = [];
        $params['title'] = $title;
        $params['initial_comment'] = $comment;
        $params['channels'] = null;

        $channelDiscover = new Channels($this->client);
        $channelId = $channelDiscover->getId($channel);

        if (!empty($channelId)) {
            $params['channels'] = $channelId;
        }

        $params['content'] = file_get_contents($file);
        $params['fileType'] = mime_content_type($file);
        $params['filename'] = basename($file);

        return $this->client->send(
            Actions::ACTION_FILES_UPLOAD,
            $params
        );
    }
}
