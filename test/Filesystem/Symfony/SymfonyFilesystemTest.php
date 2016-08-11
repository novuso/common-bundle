<?php

namespace Novuso\Test\Common\Adapter\Filesystem\Symfony;

use Novuso\Common\Adapter\Filesystem\Symfony\SymfonyFilesystem;
use Novuso\Test\System\TestCase\UnitTestCase;

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
}
