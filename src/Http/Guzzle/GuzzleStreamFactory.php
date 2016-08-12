<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Http\Guzzle;

use Exception;
use function GuzzleHttp\Psr7\stream_for;
use Novuso\Common\Application\Http\Message\StreamFactory;
use Novuso\System\Exception\DomainException;
use Psr\Http\Message\StreamInterface;

/**
 * GuzzleStreamFactory is a Guzzle stream factory
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class GuzzleStreamFactory implements StreamFactory
{
    /**
     * {@inheritdoc}
     */
    public function createStream($body = null): StreamInterface
    {
        try {
            return stream_for($body);
        } catch (Exception $exception) {
            throw new DomainException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
