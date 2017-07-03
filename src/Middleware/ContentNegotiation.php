<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Middleware;

use Exception;
use Negotiation\Accept;
use Negotiation\AcceptCharset;
use Negotiation\AcceptEncoding;
use Negotiation\AcceptLanguage;
use Negotiation\CharsetNegotiator;
use Negotiation\EncodingNegotiator;
use Negotiation\LanguageNegotiator;
use Negotiation\Negotiator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Serializer\Encoder\ChainDecoder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use XMLWriter;

/**
 * ContentNegotiation provides content negotiation as HttpKernel middleware
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class ContentNegotiation implements HttpKernelInterface, TerminableInterface
{
    /**
     * Decorated kernel
     *
     * @var HttpKernelInterface
     */
    protected $kernel;

    /**
     * Format priorities
     *
     * @var string[]
     */
    protected $formatPriorities;

    /**
     * Language priorities
     *
     * @var string[]
     */
    protected $languagePriorities;

    /**
     * Encoding priorities
     *
     * @var string[]
     */
    protected $encodingPriorities;

    /**
     * Charset priorities
     *
     * @var string[]
     */
    protected $charsetPriorities;

    /**
     * Format negotiator
     *
     * @var Negotiator
     */
    protected $formatNegotiator;

    /**
     * Language negotiator
     *
     * @var LanguageNegotiator
     */
    protected $languageNegotiator;

    /**
     * Encoding negotiator
     *
     * @var EncodingNegotiator
     */
    protected $encodingNegotiator;

    /**
     * Charset negotiator
     *
     * @var CharsetNegotiator
     */
    protected $charsetNegotiator;

    /**
     * Content decoder
     *
     * @var DecoderInterface
     */
    protected $contentDecoder;

    /**
     * Format map
     *
     * @var array
     */
    protected static $formats = [
        'html' => ['text/html', 'application/xhtml+xml'],
        'txt'  => ['text/plain'],
        'js'   => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css'  => ['text/css'],
        'json' => ['application/json', 'application/x-json'],
        'xml'  => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf'  => ['application/rdf+xml'],
        'atom' => ['application/atom+xml'],
        'rss'  => ['application/rss+xml'],
        'form' => ['application/x-www-form-urlencoded']
    ];

    /**
     * Constructs ContentNegotiation
     *
     * @param HttpKernelInterface     $kernel             The kernel
     * @param string[]                $formatPriorities   The format priorities
     * @param string[]                $languagePriorities The language priorities
     * @param string[]                $encodingPriorities The encoding priorities
     * @param string[]                $charsetPriorities  The charset priorities
     * @param Negotiator|null         $formatNegotiator   The format negotiator
     * @param LanguageNegotiator|null $languageNegotiator The language negotiator
     * @param EncodingNegotiator|null $encodingNegotiator The encoding negotiator
     * @param CharsetNegotiator|null  $charsetNegotiator  The charset negotiator
     * @param DecoderInterface|null   $contentDecoder     The content decoder
     */
    public function __construct(
        HttpKernelInterface $kernel,
        array $formatPriorities = [],
        array $languagePriorities = [],
        array $encodingPriorities = [],
        array $charsetPriorities = [],
        ?Negotiator $formatNegotiator = null,
        ?LanguageNegotiator $languageNegotiator = null,
        ?EncodingNegotiator $encodingNegotiator = null,
        ?CharsetNegotiator $charsetNegotiator = null,
        ?DecoderInterface $contentDecoder = null
    ) {
        $this->kernel = $kernel;
        $this->formatPriorities = $formatPriorities;
        $this->languagePriorities = $languagePriorities;
        $this->encodingPriorities = $encodingPriorities;
        $this->charsetPriorities = $charsetPriorities;
        $this->formatNegotiator = $formatNegotiator ?: new Negotiator();
        $this->languageNegotiator = $languageNegotiator ?: new LanguageNegotiator();
        $this->encodingNegotiator = $encodingNegotiator ?: new EncodingNegotiator();
        $this->charsetNegotiator = $charsetNegotiator ?: new CharsetNegotiator();
        $this->contentDecoder = $contentDecoder ?: new ChainDecoder([new JsonEncoder(), new XmlEncoder()]);
    }

    /**
     * Handles a request to convert it to a response
     *
     * @param Request $request A Request instance
     * @param int     $type    The type of the request
     * @param bool    $catch   Whether to catch exceptions or not
     *
     * @return Response
     *
     * @throws Exception When an Exception occurs during processing
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true): Response
    {
        $acceptHeader = $request->headers->get('Accept');
        if ($acceptHeader !== null && !empty($this->formatPriorities)) {
            $accept = $this->formatNegotiator->getBest($acceptHeader, $this->formatPriorities);
            $request->attributes->set('_accept', $accept);
            if ($accept !== null) {
                /** @var Accept $accept */
                if (false === strpos($accept->getType(), '*')) {
                    $mimeType = $accept->getType();
                    $format = $this->getFormat($mimeType);
                    $request->attributes->set('_mime_type', $mimeType);
                    $request->attributes->set('_format', $format);
                }
            }
        }

        $acceptLanguageHeader = $request->headers->get('Accept-Language');
        if ($acceptLanguageHeader !== null && !empty($this->languagePriorities)) {
            $acceptLanguage = $this->languageNegotiator->getBest($acceptLanguageHeader, $this->languagePriorities);
            $request->attributes->set('_accept_language', $acceptLanguage);
            if ($acceptLanguage !== null) {
                /** @var AcceptLanguage $acceptLanguage */
                $language = $acceptLanguage->getType();
                $request->attributes->set('_language', $language);
            }
        }

        $acceptEncodingHeader = $request->headers->get('Accept-Encoding');
        if ($acceptEncodingHeader !== null && !empty($this->encodingPriorities)) {
            $acceptEncoding = $this->encodingNegotiator->getBest($acceptEncodingHeader, $this->encodingPriorities);
            $request->attributes->set('_accept_encoding', $acceptEncoding);
            if ($acceptEncoding !== null) {
                /** @var AcceptEncoding $acceptEncoding */
                $encoding = $acceptEncoding->getType();
                $request->attributes->set('_encoding', $encoding);
            }
        }

        $acceptCharsetHeader = $request->headers->get('Accept-Charset');
        if ($acceptCharsetHeader !== null && !empty($this->charsetPriorities)) {
            $acceptCharset = $this->charsetNegotiator->getBest($acceptCharsetHeader, $this->charsetPriorities);
            $request->attributes->set('_accept_charset', $acceptCharset);
            if ($acceptCharset !== null) {
                /** @var AcceptCharset $acceptCharset */
                $charset = $acceptCharset->getType();
                $request->attributes->set('_charset', $charset);
            }
        }

        try {
            $this->decodeBody($request);
        } catch (BadRequestHttpException $exception) {
            if ($catch === true) {
                return $this->handleException($exception, $request);
            }
            throw $exception;
        }

        return $this->kernel->handle($request, $type, $catch);
    }

    /**
     * Terminates a request/response cycle
     *
     * @param Request  $request  A Request instance
     * @param Response $response A Response instance
     *
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }
    }

    /**
     * Decodes the request body
     *
     * @param Request $request The request
     *
     * @return void
     */
    protected function decodeBody(Request $request): void
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $contentType = $request->headers->get('Content-Type', '');
            $format = $this->getFormat($contentType);

            if (!$this->contentDecoder->supportsDecoding($format)) {
                return;
            }

            $content = $request->getContent();

            if (!empty($content)) {
                try {
                    $data = $this->contentDecoder->decode($content, $format);
                } catch (Exception $exception) {
                    $data = null;
                }

                if (is_array($data)) {
                    $request->request->replace($data);
                } else {
                    $message = sprintf('Invalid %s message received', $format);
                    throw new BadRequestHttpException($message);
                }
            }
        }
    }

    /**
     * Retrieves the format for a MIME type
     *
     * @param string $mimeType The MIME type
     *
     * @return string|null
     */
    protected function getFormat(string $mimeType): ?string
    {
        $pos = strpos($mimeType, ';');
        if ($pos !== false) {
            $mimeType = substr($mimeType, 0, $pos);
        }

        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Handles an exception by trying to convert it to a response
     *
     * @param Exception $exception The exception
     * @param Request   $request   The request
     *
     * @return Response
     */
    protected function handleException(Exception $exception, Request $request): Response
    {
        $format = $request->attributes->get('_format', 'html');

        switch ($format) {
            case 'json':
                $data = ['error' => $exception->getMessage()];
                $response = new JsonResponse($data, Response::HTTP_BAD_REQUEST);
                break;
            case 'xml':
                $xml = $this->renderXmlError($exception);
                $headers = ['Content-Type' => 'application/xml'];
                $response = new Response($xml, Response::HTTP_BAD_REQUEST, $headers);
                break;
            default:
                $response = new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
                break;
        }

        return $response;
    }

    /**
     * Renders an XML error document
     *
     * @param Exception $exception The exception
     *
     * @return string
     */
    protected function renderXmlError(Exception $exception): string
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString(' ');
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('document');
        $xmlWriter->startElement('error');
        $xmlWriter->startElement('error_code');
        $xmlWriter->text((string) Response::HTTP_BAD_REQUEST);
        $xmlWriter->endElement();
        $xmlWriter->startElement('error_message');
        $xmlWriter->text($exception->getMessage());
        $xmlWriter->endElement();
        $xmlWriter->endElement();
        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        return $xmlWriter->outputMemory();
    }
}
