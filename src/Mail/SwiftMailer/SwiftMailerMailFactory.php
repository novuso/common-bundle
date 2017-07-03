<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Mail\SwiftMailer;

use Novuso\Common\Application\Mail\Attachment;
use Novuso\Common\Application\Mail\MailFactory;
use Novuso\Common\Application\Mail\Message;
use Swift_Attachment;

/**
 * SwiftMailerMailFactory is a Swift Mailer mail factory
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class SwiftMailerMailFactory implements MailFactory
{
    /**
     * {@inheritdoc}
     */
    public function createMessage(): Message
    {
        return new Message();
    }

    /**
     * {@inheritdoc}
     */
    public function createAttachmentFromString(
        string $body,
        string $fileName,
        string $contentType,
        ?string $embedId = null
    ): Attachment {
        return SwiftMailerAttachment::fromString($body, $fileName, $contentType, $embedId);
    }

    /**
     * {@inheritdoc}
     */
    public function createAttachmentFromPath(
        string $path,
        string $fileName,
        string $contentType,
        ?string $embedId = null
    ): Attachment {
        return SwiftMailerAttachment::fromPath($path, $fileName, $contentType, $embedId);
    }

    /**
     * {@inheritdoc}
     */
    public function generateEmbedId(): string
    {
        return Swift_Attachment::newInstance()->generateId();
    }
}
