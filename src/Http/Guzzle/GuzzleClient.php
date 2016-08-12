<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Http\Guzzle;

use GuzzleHttp\ClientInterface;
use Novuso\Common\Application\Http\Client\HttpAsyncClient;
use Novuso\Common\Application\Http\Client\HttpClient;
use Novuso\Common\Application\Http\Client\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * GuzzleClient is a Guzzle HTTP client adapter
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class GuzzleClient implements HttpAsyncClient, HttpClient
{
    /**
     * Guzzle client
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * Constructs GuzzleClient
     *
     * @param ClientInterface $client The Guzzle client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $promise = $this->sendAsync($request, $options);
        $promise->wait();

        if ($promise->getState() === Promise::REJECTED) {
            throw $promise->getException();
        }

        return $promise->getResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsync(RequestInterface $request, array $options = []): Promise
    {
        $promise = $this->client->sendAsync($request, $options);

        return new GuzzlePromise($promise, $request);
    }
}
