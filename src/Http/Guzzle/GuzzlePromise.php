<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Http\Guzzle;

use Exception;
use GuzzleHttp\Exception as GuzzleExceptions;
use GuzzleHttp\Promise\PromiseInterface;
use Novuso\Common\Application\Http\Client\Promise;
use Novuso\Common\Application\Http\Exception as NovusoExceptions;
use Novuso\System\Exception\MethodCallException;
use Novuso\System\Exception\RuntimeException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * GuzzlePromise is a Guzzle promise adapter
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class GuzzlePromise implements Promise
{
    /**
     * Promise
     *
     * @var PromiseInterface
     */
    protected $promise;

    /**
     * State
     *
     * @var string
     */
    protected $state;

    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Response
     *
     * @var ResponseInterface|null
     */
    protected $response;

    /**
     * Exception
     *
     * @var Exception|null
     */
    protected $exception;

    /**
     * Constructs GuzzlePromise
     *
     * @param PromiseInterface $promise The promise
     * @param RequestInterface $request The request
     */
    public function __construct(PromiseInterface $promise, RequestInterface $request)
    {
        $this->request = $request;
        $this->state = Promise::PENDING;
        $this->promise = $promise->then(
            function ($response) {
                $this->response = $response;
                $this->state = Promise::FULFILLED;

                return $response;
            },
            function ($reason) use ($request) {
                if ($reason instanceof NovusoExceptions\Exception) {
                    $this->state = Promise::REJECTED;
                    $this->exception = $reason;

                    throw $this->exception;
                }

                if (!($reason instanceof GuzzleExceptions\GuzzleException)) {
                    throw new RuntimeException('Invalid reason');
                }

                $this->state = Promise::REJECTED;
                $this->exception = $this->handleException($reason, $request);

                throw $this->exception;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): Promise
    {
        return new static($this->promise->then($onFulfilled, $onRejected), $this->request);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse(): ResponseInterface
    {
        if ($this->state !== Promise::FULFILLED) {
            throw new MethodCallException('Response not available for the current state');
        }

        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function getException(): Exception
    {
        if ($this->state !== Promise::REJECTED) {
            throw new MethodCallException('Error not available for the current state');
        }

        return $this->exception;
    }

    /**
     * {@inheritdoc}
     */
    public function wait(): void
    {
        $this->promise->wait(false);
    }

    /**
     * Converts a Guzzle exception into a Novuso exception
     *
     * @param GuzzleExceptions\GuzzleException $exception The exception
     * @param RequestInterface                 $request   The request
     *
     * @return NovusoExceptions\Exception
     */
    protected function handleException(GuzzleExceptions\GuzzleException $exception, RequestInterface $request)
    {
        if ($exception instanceof GuzzleExceptions\SeekException) {
            return new NovusoExceptions\RequestException($exception->getMessage(), $request, $exception);
        }

        if ($exception instanceof GuzzleExceptions\ConnectException) {
            return new NovusoExceptions\NetworkException(
                $exception->getMessage(),
                $exception->getRequest(),
                $exception
            );
        }

        if ($exception instanceof GuzzleExceptions\RequestException) {
            if ($exception->hasResponse()) {
                return new NovusoExceptions\HttpException(
                    $exception->getMessage(),
                    $exception->getRequest(),
                    $exception->getResponse(),
                    $exception
                );
            }

            return new NovusoExceptions\RequestException(
                $exception->getMessage(),
                $exception->getRequest(),
                $exception
            );
        }

        return new NovusoExceptions\TransferException($exception->getMessage(), 0, $exception);
    }
}
