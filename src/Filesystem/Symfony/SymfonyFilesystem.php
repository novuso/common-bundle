<?php declare(strict_types=1);

namespace Novuso\Common\Adapter\Filesystem\Symfony;

use Exception;
use Novuso\Common\Application\Filesystem\Exception\FileNotFoundException;
use Novuso\Common\Application\Filesystem\Exception\FilesystemException;
use Novuso\Common\Application\Filesystem\Filesystem as FilesystemInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * SymfonyFilesystem is a Symfony filesystem adapter
 *
 * @copyright Copyright (c) 2017, Novuso. <http://novuso.com>
 * @license   http://opensource.org/licenses/MIT The MIT License
 * @author    John Nickell <email@johnnickell.com>
 */
class SymfonyFilesystem implements FilesystemInterface
{
    /**
     * Symfony filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructs SymfonyFilesystem
     *
     * @param Filesystem|null $filesystem The Symfony filesystem
     */
    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function mkdir($dirs, int $mode = 0775): void
    {
        try {
            $this->filesystem->mkdir($dirs, $mode);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function touch($files, ?int $time = null, ?int $atime = null): void
    {
        try {
            $this->filesystem->touch($files, $time, $atime);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rename(string $origin, string $target, bool $override = false): void
    {
        try {
            $this->filesystem->rename($origin, $target, $override);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function symlink(string $origin, string $target, bool $copyOnWindows = false): void
    {
        try {
            $this->filesystem->symlink($origin, $target, $copyOnWindows);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $originFile, string $targetFile, bool $override = false): void
    {
        if (stream_is_local($originFile) && !is_file($originFile)) {
            throw FileNotFoundException::fromPath($originFile);
        }
        try {
            $this->filesystem->copy($originFile, $targetFile, $override);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mirror(
        string $originDir,
        string $targetDir,
        bool $override = false,
        bool $delete = false,
        bool $copyOnWindows = false
    ): void {
        try {
            $options = ['override' => $override, 'delete' => $delete, 'copy_on_windows' => $copyOnWindows];
            $this->filesystem->mirror($originDir, $targetDir, null, $options);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists($paths): bool
    {
        return $this->filesystem->exists($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($paths): void
    {
        try {
            $this->filesystem->remove($paths);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): string
    {
        if (stream_is_local($path) && !is_file($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        $content = @file_get_contents($path);

        if ($content === false) {
            $message = sprintf('Unable to read file content: %s', $path);
            throw new FilesystemException($message, $path);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, string $content): void
    {
        // if path is a stream [scheme://path], use file_put_contents
        // as Symfony Filesystem method will not work with streams
        if (preg_match('/\A[^:\/?#]+:\/\/.*\z/', $path)) {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                $this->mkdir($dir);
            }
            $bytes = @file_put_contents($path, $content);
            if ($bytes === false) {
                $message = sprintf('Unable to write content to file: %s', $path);
                throw new FilesystemException($message, $path);
            }
            return;
        }

        try {
            $this->filesystem->dumpFile($path, $content);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isLink(string $path): bool
    {
        return is_link($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isExecutable(string $path): bool
    {
        return is_executable($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isAbsolute(string $path): bool
    {
        return $this->filesystem->isAbsolutePath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path): int
    {
        if (!is_file($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        $timestamp = @filemtime($path);

        if ($timestamp === false) {
            $message = sprintf('Unable to fetch last modified: %s', $path);
            throw new FilesystemException($message, $path);
        }

        return $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function fileSize(string $path): int
    {
        if (!is_file($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        $size = @filesize($path);

        if ($size === false) {
            $message = sprintf('Unable to fetch file size: %s', $path);
            throw new FilesystemException($message, $path);
        }

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function fileName(string $path): string
    {
        if (!file_exists($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function fileExt(string $path): string
    {
        if (!file_exists($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    public function dirName(string $path): string
    {
        if (!file_exists($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        return dirname($path);
    }

    /**
     * {@inheritdoc}
     */
    public function baseName(string $path, ?string $suffix = null): string
    {
        if (!file_exists($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        if ($suffix === null) {
            return basename($path);
        }

        return basename($path, $suffix);
    }

    /**
     * {@inheritdoc}
     */
    public function fileType(string $path): string
    {
        $type = @filetype($path);

        if ($type === false) {
            $message = sprintf('Unable to fetch file type: %s', $path);
            throw new FilesystemException($message, $path);
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function mimeType(string $path): string
    {
        if (!is_file($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        $mime = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);

        if ($mime === false) {
            $message = sprintf('Unable to fetch mime type: %s', $path);
            throw new FilesystemException($message, $path);
        }

        return $mime;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturn(string $path)
    {
        if (!is_file($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        return require $path;
    }

    /**
     * {@inheritdoc}
     */
    public function requireOnce(string $path): void
    {
        if (!is_file($path)) {
            throw FileNotFoundException::fromPath($path);
        }

        require_once $path;
    }

    /**
     * {@inheritdoc}
     */
    public function chmod($paths, int $mode, int $umask = 0000, bool $recursive = false): void
    {
        try {
            $this->filesystem->chmod($paths, $mode, $umask, $recursive);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function chown($paths, string $user, bool $recursive = false): void
    {
        try {
            $this->filesystem->chown($paths, $user, $recursive);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function chgrp($paths, string $group, bool $recursive = false): void
    {
        try {
            $this->filesystem->chgrp($paths, $group, $recursive);
        } catch (IOException $exception) {
            throw new FilesystemException($exception->getMessage(), $exception->getPath(), $exception);
        } catch (Exception $exception) {
            throw new FilesystemException($exception->getMessage(), null, $exception);
        }
    }
}
