<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Alireza Alinia <a.alinia@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Kavenegar;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Alireza Alinia <a.alinia@gmail.com>
 *
 * @experimental in 5.1
 */
final class KavenegarTransport extends AbstractTransport
{
    protected const HOST = 'api.kavenegar.com';

    private $apiKey;
    private $from;

    public function __construct(string $apiKey, string $from, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->apiKey = $apiKey;
        $this->from = $from;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('kavenegar://%s?from=%s&api_key=%s', $this->getEndpoint(), $this->from, $this->apiKey);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$this->supports($message)) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, SmsMessage::class, get_debug_type($message)));
        }

        $endpoint = sprintf('https://%s/v1/%s/sms/send.json/', $this->getEndpoint(), $this->apiKey);
        $params = array(
            "receptor" => $message->getPhone(),
            "sender" => $this->from,
            "message" => $message->getSubject()
        );
        $response = $this->client->request('POST', $endpoint, ['query' => $params]);

        $return = $response->toArray(false)['return'];
        if (200 !== $response->getStatusCode() || 200 !== $return['status']) {
            $message = $return['message'] || '';
            throw new TransportException('Unable to send the SMS: '.$message, $response);
        }
    }
}
