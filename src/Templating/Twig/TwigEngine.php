<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Templating\Twig;

use Exception;
use Novuso\Common\Application\Templating\TemplateEngine;
use Novuso\Common\Application\Templating\TemplateHelper;
use Novuso\Common\Application\Templating\Exception\DuplicateHelperException;
use Novuso\Common\Application\Templating\Exception\TemplatingException;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Twig_Environment;

/**
 * TwigEngine is a Twig template engine adapter
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class TwigEngine implements TemplateEngine
{
    /**
     * Twig environment
     *
     * @var Twig_Environment
     */
    protected $environment;

    /**
     * Template name parser
     *
     * @var TemplateNameParserInterface
     */
    protected $parser;

    /**
     * Template helpers
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * TwigEngine constructor.
     *
     * @param Twig_Environment            $environment The Twig environment
     * @param TemplateNameParserInterface $parser      The template name parser
     */
    public function __construct(Twig_Environment $environment, TemplateNameParserInterface $parser)
    {
        $this->environment = $environment;
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $template, array $data = []): string
    {
        try {
            return $this->environment->render($template, $data);
        } catch (Exception $exception) {
            throw new TemplatingException($exception->getMessage(), $template, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $template): bool
    {
        return $this->environment->getLoader()->exists($template);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $template): bool
    {
        $reference = $this->parser->parse($template);

        return $reference->get('engine') === 'twig';
    }

    /**
     * {@inheritdoc}
     */
    public function addHelper(TemplateHelper $helper): void
    {
        $name = $helper->getName();

        if (isset($this->helpers[$name])) {
            throw DuplicateHelperException::fromName($name);
        }

        $this->helpers[$name] = $helper;
        $this->environment->addGlobal($name, $helper);
    }

    /**
     * {@inheritdoc}
     */
    public function hasHelper(TemplateHelper $helper): bool
    {
        $name = $helper->getName();

        if (isset($this->helpers[$name])) {
            return true;
        }

        return false;
    }
}
