<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Mail\SwiftMailer;

use Novuso\Common\Application\Mail\Attachment;
use Swift_Attachment;

/**
 * SwiftMailerAttachment is a Swift Mailer attachment adapter
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class SwiftMailerAttachment implements Attachment
{
    /**
     * Attachment
     *
     * @var Swift_Attachment
     */
    protected $attachment;

    /**
     * Constructs SwiftMailerAttachment
     *
     * @param Swift_Attachment $attachment A Swift_Attachment instance
     */
    protected function __construct(Swift_Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Creates instance from a data string
     *
     * @param string      $body        The file contents
     * @param string      $fileName    The file name
     * @param string      $contentType The content type
     * @param string|null $embedId     The embed ID
     *
     * @return SwiftMailerAttachment
     */
    public static function fromString(
        string $body,
        string $fileName,
        string $contentType,
        string $embedId = null
    ): SwiftMailerAttachment {
        /** @var Swift_Attachment $attachment */
        $attachment = Swift_Attachment::newInstance($body, $fileName, $contentType);
        if ($embedId !== null) {
            $attachment->setId($embedId);
            $attachment->setDisposition('inline');
        }

        return new static($attachment);
    }

    /**
     * Creates instance from a local file path
     *
     * @param string      $path        The local path
     * @param string      $fileName    The file name
     * @param string      $contentType The content type
     * @param string|null $embedId     The embed ID
     *
     * @return SwiftMailerAttachment
     */
    public static function fromPath(
        string $path,
        string $fileName,
        string $contentType,
        string $embedId = null
    ): SwiftMailerAttachment {
        /** @var Swift_Attachment $attachment */
        $attachment = Swift_Attachment::fromPath($path, $contentType)
            ->setFilename($fileName);
        if ($embedId !== null) {
            $attachment->setId($embedId);
            $attachment->setDisposition('inline');
        }

        return new static($attachment);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->attachment->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): string
    {
        return $this->attachment->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName(): string
    {
        return $this->attachment->getFilename();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
    {
        return $this->attachment->getContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function getDisposition(): string
    {
        return $this->attachment->getDisposition();
    }

    /**
     * {@inheritdoc}
     */
    public function embed(): string
    {
        return sprintf('cid:%s', $this->attachment->getId());
    }
}
