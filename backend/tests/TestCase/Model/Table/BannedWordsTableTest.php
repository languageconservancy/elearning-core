<?php

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BannedWordsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BannedWordsTable Test Case
 */
class BannedWordsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\BannedWordsTable
     */
    public $BannedWords;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.BannedWords'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('BannedWords')
            ? [] : ['className' => BannedWordsTable::class];
        $this->BannedWords = TableRegistry::getTableLocator()->get('BannedWords', $config);
    }

    public function testRemoveDiacritics()
    {
        $from =
            'I want áéíóúǧšŋȟčö and üÁÉÍÓÚǦŠŊȞČÖÜ áéíóúǧšŋȟčöüÁÉÍÓÚǦŠŊȞČÖÜ, '
            . 'á é í ó ú ǧ š ŋ ȟ č ö ü Á É Í Ó Ú Ǧ Š Ŋ Ȟ Č Ö Ü ą į ą̄ ē ī į̄ ō ų ū ų̄ ž';
        $to =
            'i want aeiougsnhco and uaeiougsnhcou aeiougsnhcouaeiougsnhcou, '
            . 'a e i o u g s n h c o u a e i o u g s n h c o u a i a e i i o u u u z';
        $from = mb_strtolower($from, 'UTF-8');
        $result = $this->BannedWords->removeDiacritics($from);
        $this->assertEquals($result, $to);
    }

    public function testPresentInText()
    {
        $okText = [
            "hello", "wang", "cassandra", "johnson"
        ];
        $bannedText = [
            "dicks", "fuckme","šȟit", "fúck"
        ];

        // Make sure ok text is not banned
        foreach ($okText as $text) {
            if ($this->BannedWords->presentInText($text)) {
                fwrite(STDERR, print_r($text, true) . " was banned when it shouldn't be");
            }
            $this->assertFalse($this->BannedWords->presentInText($text));
        }

        // Make sure banned text gets banned
        foreach ($bannedText as $text) {
            if (!$this->BannedWords->presentInText($text)) {
                fwrite(STDERR, print_r($text, true) . " wasn't banned when it should be");
            }
            $this->assertTrue($this->BannedWords->presentInText($text));
        }
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->BannedWords);

        parent::tearDown();
    }
}
