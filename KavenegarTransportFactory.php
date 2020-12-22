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

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @author Alireza Alinia <a.alinia@gmail.com>
 *
 * @experimental in 5.1
 */
final class KavenegarTransportFactory extends AbstractTransportFactory
{
    /**
     * @return KavenegarTransport
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        
        if ('kavenegar' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'kavenegar', $this->getSupportedSchemes());
        }
        
        $apiKey = $dsn->getOption('api_key');
        $from = $dsn->getOption('from');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new KavenegarTransport($apiKey, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['kavenegar'];
    }
}
