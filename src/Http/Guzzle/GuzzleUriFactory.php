<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Http\Guzzle;

use Exception;
use function GuzzleHttp\Psr7\uri_for;
use Novuso\Common\Application\Http\Message\UriFactory;
use Novuso\System\Exception\DomainException;
use Psr\Http\Message\UriInterface;

/**
 * GuzzleUriFactory is a Guzzle URI factory
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class GuzzleUriFactory implements UriFactory
{
    /**
     * {@inheritdoc}
     */
    public function createUri($uri): UriInterface
    {
        try {
            return uri_for($uri);
        } catch (Exception $exception) {
            throw new DomainException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
