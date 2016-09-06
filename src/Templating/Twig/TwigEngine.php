<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Templating\Twig;

use Exception;
use Novuso\Common\Application\Templating\TemplateEngine;
use Novuso\Common\Application\Templating\TemplateHelper;
use Novuso\Common\Application\Templating\Exception\DuplicateHelperException;
use Novuso\Common\Application\Templating\Exception\TemplatingException;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig_Environment;
use Twig_Template;

/**
 * TwigEngine is a Twig template engine adapter
 *
 * @copyright Copyright (c) 2016, Novuso. <http://novuso.com>
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
            return $this->load($this->filter($template))->render($data);
        } catch (Exception $exception) {
            throw new TemplatingException($exception->getMessage(), $template, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $template): bool
    {
        if ($template instanceof Twig_Template) {
            return true;
        }

        $loader = $this->environment->getLoader();

        try {
            $loader->getSource((string) $this->filter($template));
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $template): bool
    {
        if ($template instanceof Twig_Template) {
            return true;
        }

        $reference = $this->parser->parse($this->filter($template));

        return $reference->get('engine') === 'twig';
    }

    /**
     * {@inheritdoc}
     */
    public function addHelper(TemplateHelper $helper)
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

    /**
     * Loads the given template
     *
     * @param string|TemplateReferenceInterface|Twig_Template $name The template
     *
     * @return Twig_Template
     *
     * @throws TemplatingException When the template cannot be loaded
     */
    protected function load($name)
    {
        if ($name instanceof Twig_Template) {
            return $name;
        }

        try {
            /** @var Twig_Template $template */
            $template = $this->environment->loadTemplate((string) $name);
        } catch (Exception $exception) {
            throw new TemplatingException($exception->getMessage(), (string) $name, $exception);
        }

        return $template;
    }

    /**
     * Filters the template name
     *
     * @param string|TemplateReferenceInterface|Twig_Template $template The template
     *
     * @return string|TemplateReferenceInterface|Twig_Template
     */
    protected function filter($template)
    {
        if (is_string($template)) {
            return str_replace(':', DIRECTORY_SEPARATOR, $template);
        }

        return $template;
    }
}
