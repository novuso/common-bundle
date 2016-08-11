<?php

namespace Novuso\Test\Common\Adapter\Filesystem\Symfony;

use Novuso\Common\Adapter\Filesystem\Symfony\SymfonyFilesystem;
use Novuso\Test\System\TestCase\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @covers Novuso\Common\Adapter\Filesystem\Symfony\SymfonyFilesystem
 */
class SymfonyFilesystemTest extends UnitTestCase
{
    /**
     * @var SymfonyFilesystem
     */
    protected $filesystem;

    protected function setUp()
    {
        $this->filesystem = new SymfonyFilesystem();
    }

    public function test_that_mkdir_creates_directories_recursively()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->mkdir($this->vfsPath('app/storage/files'));
        $this->assertTrue(is_dir($this->vfsPath('app/storage/files')));
    }

    public function test_that_touch_creates_non_existing_file()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->touch($this->vfsPath('app/paths.php'));
        $this->assertTrue(is_file($this->vfsPath('app/paths.php')));
    }

    public function test_that_rename_moves_existing_file()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->filesystem->rename($this->vfsPath('app/test.txt'), $this->vfsPath('app/new.txt'));
        $this->assertTrue(
            !is_file($this->vfsPath('app/test.txt'))
            && (file_get_contents($this->vfsPath('app/new.txt')) === 'Test content')
        );
    }

    public function test_that_symlink_creates_symbolic_link()
    {
        $file = sprintf('/tmp/%s.txt', uniqid('test', true));
        $link = sprintf('/tmp/%s.txt', uniqid('link', true));
        $this->filesystem->touch($file);
        $this->filesystem->symlink($file, $link);
        $this->assertTrue(is_link($link));
        $this->filesystem->remove([$file, $link]);
    }

    public function test_that_copy_copies_existing_file()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->filesystem->copy($this->vfsPath('app/test.txt'), $this->vfsPath('app/new.txt'));
        $this->assertTrue(file_get_contents($this->vfsPath('app/new.txt')) === 'Test content');
    }

    public function test_that_mirror_copies_existing_directory()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->filesystem->mirror($this->vfsPath('app'), $this->vfsPath('mirror'));
        $this->assertTrue(file_get_contents($this->vfsPath('mirror/test.txt')) === 'Test content');
    }

    public function test_that_exists_returns_true_for_existing_file()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertTrue($this->filesystem->exists($this->vfsPath('app/test.txt')));
    }

    public function test_that_remove_correctly_removes_existing_file()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->filesystem->remove($this->vfsPath('app/test.txt'));
        $this->assertFalse(is_file($this->vfsPath('app/test.txt')));
    }

    public function test_that_get_retrieves_content_of_existing_file()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $content = $this->filesystem->get($this->vfsPath('app/test.txt'));
        $this->assertSame('Test content', $content);
    }

    public function test_that_put_dumps_content_into_stream()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->put($this->vfsPath('app/storage/test.txt'), 'Test content');
        $this->assertSame('Test content', file_get_contents($this->vfsPath('app/storage/test.txt')));
    }

    public function test_that_put_dumps_content_into_file()
    {
        $file = sprintf('/tmp/%s.txt', uniqid('put', true));
        $this->filesystem->put($file, 'Test content');
        $this->assertSame('Test content', file_get_contents($file));
        $this->filesystem->remove($file);
    }

    public function test_that_is_file_returns_true_for_existing_file()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertTrue($this->filesystem->isFile($this->vfsPath('app/test.txt')));
    }

    public function test_that_is_dir_returns_true_for_existing_directory()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertTrue($this->filesystem->isDir($this->vfsPath('app')));
    }

    public function test_that_is_link_returns_false_for_existing_file()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertFalse($this->filesystem->isLink($this->vfsPath('app/test.txt')));
    }

    public function test_that_is_readable_returns_true_when_readable()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertTrue($this->filesystem->isReadable($this->vfsPath('app/test.txt')));
    }

    public function test_that_is_writable_returns_true_when_writable()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertTrue($this->filesystem->isWritable($this->vfsPath('app/test.txt')));
    }

    public function test_that_is_executable_returns_false_when_not_executable()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertFalse($this->filesystem->isExecutable($this->vfsPath('app/test.txt')));
    }

    public function test_that_is_absolute_returns_true_when_absolute_path()
    {
        $this->assertTrue($this->filesystem->isAbsolute('/tmp'));
    }

    public function test_that_last_modified_returns_modified_timestamp()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $timestamp = filemtime($this->vfsPath('app/test.txt'));
        $this->assertSame($timestamp, $this->filesystem->lastModified($this->vfsPath('app/test.txt')));
    }

    public function test_that_file_size_returns_the_file_size()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame(12, $this->filesystem->fileSize($this->vfsPath('app/test.txt')));
    }

    public function test_that_file_name_returns_expected_value()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame('test', $this->filesystem->fileName($this->vfsPath('app/test.txt')));
    }

    public function test_that_file_ext_returns_expected_value()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame('txt', $this->filesystem->fileExt($this->vfsPath('app/test.txt')));
    }

    public function test_that_dir_name_returns_expected_value()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame($this->vfsPath('app'), $this->filesystem->dirName($this->vfsPath('app/test.txt')));
    }

    public function test_that_base_name_returns_expected_value_without_suffix()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame('test.txt', $this->filesystem->baseName($this->vfsPath('app/test.txt')));
    }

    public function test_that_base_name_returns_expected_value_with_suffix()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame('test', $this->filesystem->baseName($this->vfsPath('app/test.txt'), '.txt'));
    }

    public function test_that_file_type_returns_expected_value()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame('file', $this->filesystem->fileType($this->vfsPath('app/test.txt')));
    }

    public function test_that_mime_type_returns_expected_value()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->assertSame('text/plain', $this->filesystem->mimeType($this->vfsPath('app/test.txt')));
    }

    public function test_that_get_returns_returns_expected_value()
    {
        $this->createVfs(['app' => ['config.php' => '<?php return ["foo" => "bar"];']]);
        $this->assertSame(['foo' => 'bar'], $this->filesystem->getReturn($this->vfsPath('app/config.php')));
    }

    public function test_that_require_once_includes_passed_file()
    {
        $this->createVfs(['app' => ['test.php' => '<?php echo "foo";']]);
        ob_start();
        $this->filesystem->requireOnce($this->vfsPath('app/test.php'));
        $output = ob_get_clean();
        $this->assertSame('foo', $output);
    }

    public function test_that_chmod_delegates_call_as_expected()
    {
        $file = __FILE__;
        $mode = 0644;
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chmod')
            ->once()
            ->with($file, $mode, 0000, false)
            ->andReturn(null);
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chmod($file, $mode);
    }

    public function test_that_chown_delegates_call_as_expected()
    {
        $file = __FILE__;
        $user = 'ec2-user';
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chown')
            ->once()
            ->with($file, $user, false)
            ->andReturn(null);
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chown($file, $user);
    }

    public function test_that_chgrp_delegates_call_as_expected()
    {
        $file = __FILE__;
        $group = 'ec2-user';
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chgrp')
            ->once()
            ->with($file, $group, false)
            ->andReturn(null);
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chgrp($file, $group);
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_mkdir_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('mkdir')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->mkdir('/tmp/foo');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_mkdir_throws_exception_on_error()
    {
        $vfs = $this->createVfs(['app' => []]);
        $vfs->getChild('app')->chown(vfsStream::OWNER_ROOT);
        $this->filesystem->mkdir($this->vfsPath('app/storage/files'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_touch_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('touch')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->touch('/tmp/foo');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_touch_throws_exception_on_error()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->touch($this->vfsPath('app/config/config.php'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_rename_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('rename')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->rename('/tmp/foo', '/tmp/bar');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_rename_throws_exception_on_error()
    {
        $vfs = $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $vfs->getChild('app')->chown(vfsStream::OWNER_ROOT);
        $this->filesystem->rename($this->vfsPath('app/test.txt'), $this->vfsPath('app/new.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_symlink_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('symlink')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->symlink('/tmp/foo', '/tmp/bar');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_symlink_throws_exception_on_error()
    {
        $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $this->filesystem->symlink($this->vfsPath('app/test.txt'), $this->vfsPath('app/new.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FileNotFoundException
     */
    public function test_that_copy_throws_exception_on_file_not_found()
    {
        $this->filesystem->copy(sprintf('/tmp/%s', uniqid('foo', true)), '/tmp/copy.txt');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_copy_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('copy')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->copy(__FILE__, '/tmp/bar');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_copy_throws_exception_on_error()
    {
        $vfs = $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $vfs->getChild('app')->chown(vfsStream::OWNER_ROOT);
        $this->filesystem->copy($this->vfsPath('app/test.txt'), $this->vfsPath('app/new.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_mirror_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('mirror')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->mirror(__DIR__, '/tmp/bar');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_mirror_throws_exception_on_error()
    {
        $vfs = $this->createVfs(['app' => ['storage' => ['test.txt' => 'Test content']]]);
        $vfs->getChild('app')->chown(vfsStream::OWNER_ROOT);
        $this->filesystem->mirror($this->vfsPath('app/storage'), $this->vfsPath('app/mirror'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_remove_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('remove')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->remove([]);
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_remove_throws_exception_on_error()
    {
        $vfs = $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $vfs->getChild('app')->chown(vfsStream::OWNER_ROOT);
        $this->filesystem->remove($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_get_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->get($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_get_throws_exception_when_file_is_not_readable()
    {
        $vfs = $this->createVfs(['app' => ['test.txt' => 'Test content']]);
        $appDir = $vfs->getChild('app');
        $testFile = $appDir->getChild('test.txt');
        $testFile->chown(vfsStream::OWNER_ROOT);
        $testFile->chmod(0600);
        $this->filesystem->get($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_put_throws_exception_on_error_std()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('dumpFile')
            ->once()
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->put('/tmp/file.txt', 'file');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_put_throws_exception_on_error_path()
    {
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('dumpFile')
            ->once()
            ->andThrow(new IOException('', 0, null, '/tmp/file.txt'));
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->put('/tmp/file.txt', 'file');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_put_throws_exception_when_file_is_not_writable()
    {
        $vfs = $this->createVfs(['app' => []]);
        $vfs->getChild('app')->chown(vfsStream::OWNER_ROOT);
        $this->filesystem->put($this->vfsPath('app/test.txt'), 'Test content');
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_last_modified_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->lastModified($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_file_size_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->fileSize($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_file_name_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->fileName($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_file_ext_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->fileExt($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_dir_name_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->dirName($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_base_name_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->baseName($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_file_type_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->fileType($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_mime_type_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->mimeType($this->vfsPath('app/test.txt'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_get_return_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->getReturn($this->vfsPath('app/config.php'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_require_once_throws_exception_when_file_does_not_exist()
    {
        $this->createVfs(['app' => []]);
        $this->filesystem->requireOnce($this->vfsPath('app/test.php'));
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_chmod_throws_exception_on_error()
    {
        $file = __FILE__;
        $mode = 0644;
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chmod')
            ->once()
            ->with($file, $mode, 0000, false)
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chmod($file, $mode);
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_chmod_throws_path_exception_on_error()
    {
        $file = __FILE__;
        $mode = 0644;
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chmod')
            ->once()
            ->with($file, $mode, 0000, false)
            ->andThrow(new IOException('', 0, null, $file));
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chmod($file, $mode);
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_chown_throws_exception_on_error()
    {
        $file = __FILE__;
        $user = 'ec2-user';
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chown')
            ->once()
            ->with($file, $user, false)
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chown($file, $user);
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_chown_throws_path_exception_on_error()
    {
        $file = __FILE__;
        $user = 'ec2-user';
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chown')
            ->once()
            ->with($file, $user, false)
            ->andThrow(new IOException('', 0, null, $file));
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chown($file, $user);
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_chgrp_throws_exception_on_error()
    {
        $file = __FILE__;
        $group = 'ec2-user';
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chgrp')
            ->once()
            ->with($file, $group, false)
            ->andThrow(new \Exception());
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chgrp($file, $group);
    }

    /**
     * @expectedException \Novuso\Common\Application\Filesystem\Exception\FilesystemException
     */
    public function test_that_chgrp_throws_path_exception_on_error()
    {
        $file = __FILE__;
        $group = 'ec2-user';
        $mockFs = $this->mock('Symfony\\Component\\Filesystem\\Filesystem');
        $mockFs
            ->shouldReceive('chgrp')
            ->once()
            ->with($file, $group, false)
            ->andThrow(new IOException('', 0, null, $file));
        $this->filesystem = new SymfonyFilesystem($mockFs);
        $this->filesystem->chgrp($file, $group);
    }
}
