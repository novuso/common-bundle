<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Routing\Symfony;

use Novuso\Common\Application\Routing\Exception\InvalidParameterException;
use Novuso\Common\Application\Routing\Exception\MissingParametersException;
use Novuso\Common\Application\Routing\Exception\RouteNotFoundException;
use Novuso\Common\Application\Routing\UrlGenerator;
use Symfony\Component\Routing\Exception\InvalidParameterException as ParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException as MissingException;
use Symfony\Component\Routing\Exception\RouteNotFoundException as RouteException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * SymfonyUrlGenerator is a Symfony URL generator adapter
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class SymfonyUrlGenerator implements UrlGenerator
{
    /**
     * URL generator
     *
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * Constructs SymfonyUrlGenerator
     *
     * @param UrlGeneratorInterface $urlGenerator The URL generator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $name, array $parameters = [], bool $absolute = false): string
    {
        try {
            if ($absolute) {
                $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;
            } else {
                $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
            }

            return $this->urlGenerator->generate($name, $parameters, $referenceType);
        } catch (RouteException $exception) {
            throw new RouteNotFoundException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (MissingException $exception) {
            throw new MissingParametersException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (ParameterException $exception) {
            throw new InvalidParameterException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
