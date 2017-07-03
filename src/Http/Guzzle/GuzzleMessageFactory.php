<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Http\Guzzle;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Novuso\Common\Application\Http\Message\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * GuzzleMessageFactory is a Guzzle HTTP message factory
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class GuzzleMessageFactory implements MessageFactory
{
    /**
     * {@inheritdoc}
     */
    public function createRequest(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $protocol = '1.1'
    ): RequestInterface {
        return new Request($method, $uri, $headers, $body, $protocol);
    }

    /**
     * {@inheritdoc}
     */
    public function createResponse(
        int $status = 200,
        array $headers = [],
        $body = null,
        string $protocol = '1.1',
        ?string $reason = null
    ): ResponseInterface {
        return new Response($status, $headers, $body, $protocol, $reason);
    }
}
