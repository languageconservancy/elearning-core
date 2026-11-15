<?php

namespace App\Test\TestCase\Lib;

use App\Lib\ImageArchive;
use App\Lib\LearningWorkspace;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;

class ImageArchiveTest extends TestCase
{
    public function testArchiveOnlyExposesSupportedImagesUnderTheFixedPrefix(): void
    {
        $files = ImageArchive::parse("files:\n"
            . "- key: 'Images/Web/ANIMAL bird #1.png'\n"
            . "- key: Images/Web/animal.tif\n"
            . "- key: Images/Web/../private.jpg\n"
            . "- key: other/image.png\n"
            . "- key: Images/Web/script.svg\n");
        $this->assertCount(1, $files);
        $this->assertSame('ANIMAL bird #1.png', $files[0]['name']);
    }

    public function testImageUrlEncodesEveryPathSegment(): void
    {
        $files = ImageArchive::parse("files:\n- key: 'Images/Web/bird #1.png'\n");
        $this->assertSame(
            'https://s3.us-west-2.amazonaws.com/languageconservancy.org/Images/Web/bird%20%231.png',
            $files[0]['url']
        );
    }

    public function testInvalidArchiveIsNotPresentedAsAnEmptySuccess(): void
    {
        $this->expectException(\RuntimeException::class);
        ImageArchive::parse('unrelated: []');
    }

    public function testRevisionChangesWithRecordContentButNotKeyOrder(): void
    {
        $first = LearningWorkspace::revision(new Entity(['id' => 1, 'name' => 'First']));
        $this->assertSame($first, LearningWorkspace::revision(new Entity(['name' => 'First', 'id' => 1])));
        $this->assertNotSame($first, LearningWorkspace::revision(new Entity(['id' => 1, 'name' => 'Second'])));
    }
}
