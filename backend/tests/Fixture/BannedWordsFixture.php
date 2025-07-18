<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BannedWordsFixture
 *
 */
class BannedWordsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 11,
            'unsigned' => false,
            'null' => false,
            'default' => null,
            'comment' => '',
            'autoIncrement' => true,
            'precision' => null
        ],
        'word' => [
            'type' => 'string',
            'length' => 255,
            'null' => false,
            'default' => null,
            'collate' => 'utf8_general_ci',
            'comment' => '',
            'precision' => null,
            'fixed' => null],
        'isolated_only' => [
            'type' => 'boolean',
            'length' => null,
            'null' => false,
            'default' => '0',
            'comment' => '',
            'precision' => null],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
                'length' => []
            ],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'word' => '4r5e',
                'isolated_only' => false
            ],
            [
                'id' => 2,
                'word' => '5h1t',
                'isolated_only' => false
            ],
            [
                'id' => 3,
                'word' => '5hit',
                'isolated_only' => false
            ],
            [
                'id' => 4,
                'word' => 'a55',
                'isolated_only' => false
            ],
            [
                'id' => 5,
                'word' => 'anal',
                'isolated_only' => false
            ],
            [
                'id' => 6,
                'word' => 'anus',
                'isolated_only' => false
            ],
            [
                'id' => 7,
                'word' => 'ar5e',
                'isolated_only' => false
            ],
            [
                'id' => 8,
                'word' => 'arrse',
                'isolated_only' => false
            ],
            [
                'id' => 9,
                'word' => 'arse',
                'isolated_only' => false
            ],
            [
                'id' => 10,
                'word' => 'ass',
                'isolated_only' => true
            ],
            [
                'id' => 11,
                'word' => 'ass-fucker',
                'isolated_only' => false
            ],
            [
                'id' => 12,
                'word' => 'asses',
                'isolated_only' => false
            ],
            [
                'id' => 13,
                'word' => 'assfucker',
                'isolated_only' => false
            ],
            [
                'id' => 14,
                'word' => 'assfukka',
                'isolated_only' => false
            ],
            [
                'id' => 15,
                'word' => 'asshole',
                'isolated_only' => false
            ],
            [
                'id' => 16,
                'word' => 'assholes',
                'isolated_only' => false
            ],
            [
                'id' => 17,
                'word' => 'asswhole',
                'isolated_only' => false
            ],
            [
                'id' => 18,
                'word' => 'a_s_s',
                'isolated_only' => false
            ],
            [
                'id' => 19,
                'word' => 'b!tch',
                'isolated_only' => false
            ],
            [
                'id' => 20,
                'word' => 'b00bs',
                'isolated_only' => false
            ],
            [
                'id' => 21,
                'word' => 'b17ch',
                'isolated_only' => false
            ],
            [
                'id' => 22,
                'word' => 'b1tch',
                'isolated_only' => false
            ],
            [
                'id' => 23,
                'word' => 'ballbag',
                'isolated_only' => false
            ],
            [
                'id' => 24,
                'word' => 'balls',
                'isolated_only' => false
            ],
            [
                'id' => 25,
                'word' => 'ballsack',
                'isolated_only' => false
            ],
            [
                'id' => 26,
                'word' => 'bastard',
                'isolated_only' => false
            ],
            [
                'id' => 27,
                'word' => 'beastial',
                'isolated_only' => false
            ],
            [
                'id' => 28,
                'word' => 'beastiality',
                'isolated_only' => false
            ],
            [
                'id' => 29,
                'word' => 'bellend',
                'isolated_only' => false
            ],
            [
                'id' => 30,
                'word' => 'bestial',
                'isolated_only' => false
            ],
            [
                'id' => 31,
                'word' => 'bestiality',
                'isolated_only' => false
            ],
            [
                'id' => 32,
                'word' => 'bi+ch',
                'isolated_only' => false
            ],
            [
                'id' => 33,
                'word' => 'biatch',
                'isolated_only' => false
            ],
            [
                'id' => 34,
                'word' => 'bitch',
                'isolated_only' => false
            ],
            [
                'id' => 35,
                'word' => 'bitcher',
                'isolated_only' => false
            ],
            [
                'id' => 36,
                'word' => 'bitchers',
                'isolated_only' => false
            ],
            [
                'id' => 37,
                'word' => 'bitches',
                'isolated_only' => false
            ],
            [
                'id' => 38,
                'word' => 'bitchin',
                'isolated_only' => false
            ],
            [
                'id' => 39,
                'word' => 'bitching',
                'isolated_only' => false
            ],
            [
                'id' => 40,
                'word' => 'bloody',
                'isolated_only' => false
            ],
            [
                'id' => 41,
                'word' => 'blow job',
                'isolated_only' => false
            ],
            [
                'id' => 42,
                'word' => 'blowjob',
                'isolated_only' => false
            ],
            [
                'id' => 43,
                'word' => 'blowjobs',
                'isolated_only' => false
            ],
            [
                'id' => 44,
                'word' => 'boiolas',
                'isolated_only' => false
            ],
            [
                'id' => 45,
                'word' => 'bollock',
                'isolated_only' => false
            ],
            [
                'id' => 46,
                'word' => 'bollok',
                'isolated_only' => false
            ],
            [
                'id' => 47,
                'word' => 'boner',
                'isolated_only' => false
            ],
            [
                'id' => 48,
                'word' => 'boob',
                'isolated_only' => false
            ],
            [
                'id' => 49,
                'word' => 'boobs',
                'isolated_only' => false
            ],
            [
                'id' => 50,
                'word' => 'booobs',
                'isolated_only' => false
            ],
            [
                'id' => 51,
                'word' => 'boooobs',
                'isolated_only' => false
            ],
            [
                'id' => 52,
                'word' => 'booooobs',
                'isolated_only' => false
            ],
            [
                'id' => 53,
                'word' => 'booooooobs',
                'isolated_only' => false
            ],
            [
                'id' => 54,
                'word' => 'breasts',
                'isolated_only' => false
            ],
            [
                'id' => 55,
                'word' => 'buceta',
                'isolated_only' => false
            ],
            [
                'id' => 56,
                'word' => 'bugger',
                'isolated_only' => false
            ],
            [
                'id' => 57,
                'word' => 'bum',
                'isolated_only' => true
            ],
            [
                'id' => 58,
                'word' => 'bunny fucker',
                'isolated_only' => false
            ],
            [
                'id' => 59,
                'word' => 'butt',
                'isolated_only' => false
            ],
            [
                'id' => 60,
                'word' => 'butthole',
                'isolated_only' => false
            ],
            [
                'id' => 61,
                'word' => 'buttmuch',
                'isolated_only' => false
            ],
            [
                'id' => 62,
                'word' => 'buttplug',
                'isolated_only' => false
            ],
            [
                'id' => 63,
                'word' => 'c0ck',
                'isolated_only' => false
            ],
            [
                'id' => 64,
                'word' => 'c0cksucker',
                'isolated_only' => false
            ],
            [
                'id' => 65,
                'word' => 'carpet muncher',
                'isolated_only' => false
            ],
            [
                'id' => 66,
                'word' => 'cawk',
                'isolated_only' => false
            ],
            [
                'id' => 67,
                'word' => 'chink',
                'isolated_only' => false
            ],
            [
                'id' => 68,
                'word' => 'cipa',
                'isolated_only' => false
            ],
            [
                'id' => 69,
                'word' => 'cl1t',
                'isolated_only' => false
            ],
            [
                'id' => 70,
                'word' => 'clit',
                'isolated_only' => false
            ],
            [
                'id' => 71,
                'word' => 'clitoris',
                'isolated_only' => false
            ],
            [
                'id' => 72,
                'word' => 'clits',
                'isolated_only' => false
            ],
            [
                'id' => 73,
                'word' => 'cnut',
                'isolated_only' => false
            ],
            [
                'id' => 74,
                'word' => 'cock',
                'isolated_only' => false
            ],
            [
                'id' => 75,
                'word' => 'cock-sucker',
                'isolated_only' => false
            ],
            [
                'id' => 76,
                'word' => 'cockface',
                'isolated_only' => false
            ],
            [
                'id' => 77,
                'word' => 'cockhead',
                'isolated_only' => false
            ],
            [
                'id' => 78,
                'word' => 'cockmunch',
                'isolated_only' => false
            ],
            [
                'id' => 79,
                'word' => 'cockmuncher',
                'isolated_only' => false
            ],
            [
                'id' => 80,
                'word' => 'cocks',
                'isolated_only' => false
            ],
            [
                'id' => 81,
                'word' => 'cocksuck',
                'isolated_only' => false
            ],
            [
                'id' => 82,
                'word' => 'cocksucked',
                'isolated_only' => false
            ],
            [
                'id' => 83,
                'word' => 'cocksucker',
                'isolated_only' => false
            ],
            [
                'id' => 84,
                'word' => 'cocksucking',
                'isolated_only' => false
            ],
            [
                'id' => 85,
                'word' => 'cocksucks',
                'isolated_only' => false
            ],
            [
                'id' => 86,
                'word' => 'cocksuka',
                'isolated_only' => false
            ],
            [
                'id' => 87,
                'word' => 'cocksukka',
                'isolated_only' => false
            ],
            [
                'id' => 88,
                'word' => 'cok',
                'isolated_only' => false
            ],
            [
                'id' => 89,
                'word' => 'cokmuncher',
                'isolated_only' => false
            ],
            [
                'id' => 90,
                'word' => 'coksucka',
                'isolated_only' => false
            ],
            [
                'id' => 91,
                'word' => 'coon',
                'isolated_only' => false
            ],
            [
                'id' => 92,
                'word' => 'cox',
                'isolated_only' => false
            ],
            [
                'id' => 93,
                'word' => 'crap',
                'isolated_only' => false
            ],
            [
                'id' => 94,
                'word' => 'cum',
                'isolated_only' => false
            ],
            [
                'id' => 95,
                'word' => 'cummer',
                'isolated_only' => false
            ],
            [
                'id' => 96,
                'word' => 'cumming',
                'isolated_only' => false
            ],
            [
                'id' => 97,
                'word' => 'cums',
                'isolated_only' => false
            ],
            [
                'id' => 98,
                'word' => 'cumshot',
                'isolated_only' => false
            ],
            [
                'id' => 99,
                'word' => 'cunilingus',
                'isolated_only' => false
            ],
            [
                'id' => 100,
                'word' => 'cunillingus',
                'isolated_only' => false
            ],
            [
                'id' => 101,
                'word' => 'cunnilingus',
                'isolated_only' => false
            ],
            [
                'id' => 102,
                'word' => 'cunt',
                'isolated_only' => false
            ],
            [
                'id' => 103,
                'word' => 'cuntlick',
                'isolated_only' => false
            ],
            [
                'id' => 104,
                'word' => 'cuntlicker',
                'isolated_only' => false
            ],
            [
                'id' => 105,
                'word' => 'cuntlicking',
                'isolated_only' => false
            ],
            [
                'id' => 106,
                'word' => 'cunts',
                'isolated_only' => false
            ],
            [
                'id' => 107,
                'word' => 'cyalis',
                'isolated_only' => false
            ],
            [
                'id' => 108,
                'word' => 'cyberfuc',
                'isolated_only' => false
            ],
            [
                'id' => 109,
                'word' => 'cyberfuck',
                'isolated_only' => false
            ],
            [
                'id' => 110,
                'word' => 'cyberfucked',
                'isolated_only' => false
            ],
            [
                'id' => 111,
                'word' => 'cyberfucker',
                'isolated_only' => false
            ],
            [
                'id' => 112,
                'word' => 'cyberfuckers',
                'isolated_only' => false
            ],
            [
                'id' => 113,
                'word' => 'cyberfucking',
                'isolated_only' => false
            ],
            [
                'id' => 114,
                'word' => 'd1ck',
                'isolated_only' => false
            ],
            [
                'id' => 115,
                'word' => 'damn',
                'isolated_only' => false
            ],
            [
                'id' => 116,
                'word' => 'dick',
                'isolated_only' => false
            ],
            [
                'id' => 117,
                'word' => 'dickhead',
                'isolated_only' => false
            ],
            [
                'id' => 118,
                'word' => 'dildo',
                'isolated_only' => false
            ],
            [
                'id' => 119,
                'word' => 'dildos',
                'isolated_only' => false
            ],
            [
                'id' => 120,
                'word' => 'dink',
                'isolated_only' => false
            ],
            [
                'id' => 121,
                'word' => 'dinks',
                'isolated_only' => false
            ],
            [
                'id' => 122,
                'word' => 'dirsa',
                'isolated_only' => false
            ],
            [
                'id' => 123,
                'word' => 'dlck',
                'isolated_only' => false
            ],
            [
                'id' => 124,
                'word' => 'dog-fucker',
                'isolated_only' => false
            ],
            [
                'id' => 125,
                'word' => 'doggin',
                'isolated_only' => false
            ],
            [
                'id' => 126,
                'word' => 'dogging',
                'isolated_only' => false
            ],
            [
                'id' => 127,
                'word' => 'donkeyribber',
                'isolated_only' => false
            ],
            [
                'id' => 128,
                'word' => 'doosh',
                'isolated_only' => false
            ],
            [
                'id' => 129,
                'word' => 'duche',
                'isolated_only' => false
            ],
            [
                'id' => 130,
                'word' => 'dyke',
                'isolated_only' => false
            ],
            [
                'id' => 131,
                'word' => 'ejaculate',
                'isolated_only' => false
            ],
            [
                'id' => 132,
                'word' => 'ejaculated',
                'isolated_only' => false
            ],
            [
                'id' => 133,
                'word' => 'ejaculates',
                'isolated_only' => false
            ],
            [
                'id' => 134,
                'word' => 'ejaculating',
                'isolated_only' => false
            ],
            [
                'id' => 135,
                'word' => 'ejaculatings',
                'isolated_only' => false
            ],
            [
                'id' => 136,
                'word' => 'ejaculation',
                'isolated_only' => false
            ],
            [
                'id' => 137,
                'word' => 'ejakulate',
                'isolated_only' => false
            ],
            [
                'id' => 138,
                'word' => 'f u c k',
                'isolated_only' => false
            ],
            [
                'id' => 139,
                'word' => 'f u c k e r',
                'isolated_only' => false
            ],
            [
                'id' => 140,
                'word' => 'f4nny',
                'isolated_only' => false
            ],
            [
                'id' => 141,
                'word' => 'fag',
                'isolated_only' => false
            ],
            [
                'id' => 142,
                'word' => 'fagging',
                'isolated_only' => false
            ],
            [
                'id' => 143,
                'word' => 'faggitt',
                'isolated_only' => false
            ],
            [
                'id' => 144,
                'word' => 'faggot',
                'isolated_only' => false
            ],
            [
                'id' => 145,
                'word' => 'faggs',
                'isolated_only' => false
            ],
            [
                'id' => 146,
                'word' => 'fagot',
                'isolated_only' => false
            ],
            [
                'id' => 147,
                'word' => 'fagots',
                'isolated_only' => false
            ],
            [
                'id' => 148,
                'word' => 'fags',
                'isolated_only' => false
            ],
            [
                'id' => 149,
                'word' => 'fanny',
                'isolated_only' => false
            ],
            [
                'id' => 150,
                'word' => 'fannyflaps',
                'isolated_only' => false
            ],
            [
                'id' => 151,
                'word' => 'fannyfucker',
                'isolated_only' => false
            ],
            [
                'id' => 152,
                'word' => 'fanyy',
                'isolated_only' => false
            ],
            [
                'id' => 153,
                'word' => 'fatass',
                'isolated_only' => false
            ],
            [
                'id' => 154,
                'word' => 'fcuk',
                'isolated_only' => false
            ],
            [
                'id' => 155,
                'word' => 'fcuker',
                'isolated_only' => false
            ],
            [
                'id' => 156,
                'word' => 'fcuking',
                'isolated_only' => false
            ],
            [
                'id' => 157,
                'word' => 'feck',
                'isolated_only' => false
            ],
            [
                'id' => 158,
                'word' => 'fecker',
                'isolated_only' => false
            ],
            [
                'id' => 159,
                'word' => 'felching',
                'isolated_only' => false
            ],
            [
                'id' => 160,
                'word' => 'fellate',
                'isolated_only' => false
            ],
            [
                'id' => 161,
                'word' => 'fellatio',
                'isolated_only' => false
            ],
            [
                'id' => 162,
                'word' => 'fingerfuck',
                'isolated_only' => false
            ],
            [
                'id' => 163,
                'word' => 'fingerfucked',
                'isolated_only' => false
            ],
            [
                'id' => 164,
                'word' => 'fingerfucker',
                'isolated_only' => false
            ],
            [
                'id' => 165,
                'word' => 'fingerfuckers',
                'isolated_only' => false
            ],
            [
                'id' => 166,
                'word' => 'fingerfucking',
                'isolated_only' => false
            ],
            [
                'id' => 167,
                'word' => 'fingerfucks',
                'isolated_only' => false
            ],
            [
                'id' => 168,
                'word' => 'fistfuck',
                'isolated_only' => false
            ],
            [
                'id' => 169,
                'word' => 'fistfucked',
                'isolated_only' => false
            ],
            [
                'id' => 170,
                'word' => 'fistfucker',
                'isolated_only' => false
            ],
            [
                'id' => 171,
                'word' => 'fistfuckers',
                'isolated_only' => false
            ],
            [
                'id' => 172,
                'word' => 'fistfucking',
                'isolated_only' => false
            ],
            [
                'id' => 173,
                'word' => 'fistfuckings',
                'isolated_only' => false
            ],
            [
                'id' => 174,
                'word' => 'fistfucks',
                'isolated_only' => false
            ],
            [
                'id' => 175,
                'word' => 'flange',
                'isolated_only' => false
            ],
            [
                'id' => 176,
                'word' => 'fook',
                'isolated_only' => false
            ],
            [
                'id' => 177,
                'word' => 'fooker',
                'isolated_only' => false
            ],
            [
                'id' => 178,
                'word' => 'fuck',
                'isolated_only' => false
            ],
            [
                'id' => 179,
                'word' => 'fucka',
                'isolated_only' => false
            ],
            [
                'id' => 180,
                'word' => 'fucked',
                'isolated_only' => false
            ],
            [
                'id' => 181,
                'word' => 'fucker',
                'isolated_only' => false
            ],
            [
                'id' => 182,
                'word' => 'fuckers',
                'isolated_only' => false
            ],
            [
                'id' => 183,
                'word' => 'fuckhead',
                'isolated_only' => false
            ],
            [
                'id' => 184,
                'word' => 'fuckheads',
                'isolated_only' => false
            ],
            [
                'id' => 185,
                'word' => 'fuckin',
                'isolated_only' => false
            ],
            [
                'id' => 186,
                'word' => 'fucking',
                'isolated_only' => false
            ],
            [
                'id' => 187,
                'word' => 'fuckings',
                'isolated_only' => false
            ],
            [
                'id' => 188,
                'word' => 'fuckingshitmotherfucker',
                'isolated_only' => false
            ],
            [
                'id' => 189,
                'word' => 'fuckme',
                'isolated_only' => false
            ],
            [
                'id' => 190,
                'word' => 'fucks',
                'isolated_only' => false
            ],
            [
                'id' => 191,
                'word' => 'fuckwhit',
                'isolated_only' => false
            ],
            [
                'id' => 192,
                'word' => 'fuckwit',
                'isolated_only' => false
            ],
            [
                'id' => 193,
                'word' => 'fudge packer',
                'isolated_only' => false
            ],
            [
                'id' => 194,
                'word' => 'fudgepacker',
                'isolated_only' => false
            ],
            [
                'id' => 195,
                'word' => 'fuk',
                'isolated_only' => false
            ],
            [
                'id' => 196,
                'word' => 'fuker',
                'isolated_only' => false
            ],
            [
                'id' => 197,
                'word' => 'fukker',
                'isolated_only' => false
            ],
            [
                'id' => 198,
                'word' => 'fukkin',
                'isolated_only' => false
            ],
            [
                'id' => 199,
                'word' => 'fuks',
                'isolated_only' => false
            ],
            [
                'id' => 200,
                'word' => 'fukwhit',
                'isolated_only' => false
            ],
            [
                'id' => 201,
                'word' => 'fukwit',
                'isolated_only' => false
            ],
            [
                'id' => 202,
                'word' => 'fux',
                'isolated_only' => false
            ],
            [
                'id' => 203,
                'word' => 'fux0r',
                'isolated_only' => false
            ],
            [
                'id' => 204,
                'word' => 'f_u_c_k',
                'isolated_only' => false
            ],
            [
                'id' => 205,
                'word' => 'gangbang',
                'isolated_only' => false
            ],
            [
                'id' => 206,
                'word' => 'gangbanged',
                'isolated_only' => false
            ],
            [
                'id' => 207,
                'word' => 'gangbangs',
                'isolated_only' => false
            ],
            [
                'id' => 208,
                'word' => 'gaylord',
                'isolated_only' => false
            ],
            [
                'id' => 209,
                'word' => 'gaysex',
                'isolated_only' => false
            ],
            [
                'id' => 210,
                'word' => 'goatse',
                'isolated_only' => false
            ],
            [
                'id' => 211,
                'word' => 'god-dam',
                'isolated_only' => false
            ],
            [
                'id' => 212,
                'word' => 'god-damned',
                'isolated_only' => false
            ],
            [
                'id' => 213,
                'word' => 'goddamn',
                'isolated_only' => false
            ],
            [
                'id' => 214,
                'word' => 'goddamned',
                'isolated_only' => false
            ],
            [
                'id' => 215,
                'word' => 'hardcoresex',
                'isolated_only' => false
            ],
            [
                'id' => 216,
                'word' => 'hell',
                'isolated_only' => true
            ],
            [
                'id' => 217,
                'word' => 'heshe',
                'isolated_only' => false
            ],
            [
                'id' => 218,
                'word' => 'hoar',
                'isolated_only' => false
            ],
            [
                'id' => 219,
                'word' => 'hoare',
                'isolated_only' => false
            ],
            [
                'id' => 220,
                'word' => 'hoer',
                'isolated_only' => false
            ],
            [
                'id' => 221,
                'word' => 'homo',
                'isolated_only' => true
            ],
            [
                'id' => 222,
                'word' => 'hore',
                'isolated_only' => false
            ],
            [
                'id' => 223,
                'word' => 'horniest',
                'isolated_only' => false
            ],
            [
                'id' => 224,
                'word' => 'horny',
                'isolated_only' => false
            ],
            [
                'id' => 225,
                'word' => 'hotsex',
                'isolated_only' => false
            ],
            [
                'id' => 226,
                'word' => 'jack-off',
                'isolated_only' => false
            ],
            [
                'id' => 227,
                'word' => 'jackoff',
                'isolated_only' => false
            ],
            [
                'id' => 228,
                'word' => 'jap',
                'isolated_only' => false
            ],
            [
                'id' => 229,
                'word' => 'jerk-off',
                'isolated_only' => false
            ],
            [
                'id' => 230,
                'word' => 'jism',
                'isolated_only' => false
            ],
            [
                'id' => 231,
                'word' => 'jiz',
                'isolated_only' => false
            ],
            [
                'id' => 232,
                'word' => 'jizm',
                'isolated_only' => false
            ],
            [
                'id' => 233,
                'word' => 'jizz',
                'isolated_only' => false
            ],
            [
                'id' => 234,
                'word' => 'kawk',
                'isolated_only' => false
            ],
            [
                'id' => 235,
                'word' => 'knob',
                'isolated_only' => false
            ],
            [
                'id' => 236,
                'word' => 'knobead',
                'isolated_only' => false
            ],
            [
                'id' => 237,
                'word' => 'knobed',
                'isolated_only' => false
            ],
            [
                'id' => 238,
                'word' => 'knobend',
                'isolated_only' => false
            ],
            [
                'id' => 239,
                'word' => 'knobhead',
                'isolated_only' => false
            ],
            [
                'id' => 240,
                'word' => 'knobjocky',
                'isolated_only' => false
            ],
            [
                'id' => 241,
                'word' => 'knobjokey',
                'isolated_only' => false
            ],
            [
                'id' => 242,
                'word' => 'kock',
                'isolated_only' => false
            ],
            [
                'id' => 243,
                'word' => 'kondum',
                'isolated_only' => false
            ],
            [
                'id' => 244,
                'word' => 'kondums',
                'isolated_only' => false
            ],
            [
                'id' => 245,
                'word' => 'kum',
                'isolated_only' => false
            ],
            [
                'id' => 246,
                'word' => 'kummer',
                'isolated_only' => false
            ],
            [
                'id' => 247,
                'word' => 'kumming',
                'isolated_only' => false
            ],
            [
                'id' => 248,
                'word' => 'kums',
                'isolated_only' => false
            ],
            [
                'id' => 249,
                'word' => 'kunilingus',
                'isolated_only' => false
            ],
            [
                'id' => 250,
                'word' => 'l3i+ch',
                'isolated_only' => false
            ],
            [
                'id' => 251,
                'word' => 'l3itch',
                'isolated_only' => false
            ],
            [
                'id' => 252,
                'word' => 'labia',
                'isolated_only' => false
            ],
            [
                'id' => 253,
                'word' => 'lmfao',
                'isolated_only' => false
            ],
            [
                'id' => 254,
                'word' => 'lust',
                'isolated_only' => false
            ],
            [
                'id' => 255,
                'word' => 'lusting',
                'isolated_only' => false
            ],
            [
                'id' => 256,
                'word' => 'm0f0',
                'isolated_only' => false
            ],
            [
                'id' => 257,
                'word' => 'm0fo',
                'isolated_only' => false
            ],
            [
                'id' => 258,
                'word' => 'm45terbate',
                'isolated_only' => false
            ],
            [
                'id' => 259,
                'word' => 'ma5terb8',
                'isolated_only' => false
            ],
            [
                'id' => 260,
                'word' => 'ma5terbate',
                'isolated_only' => false
            ],
            [
                'id' => 261,
                'word' => 'masochist',
                'isolated_only' => false
            ],
            [
                'id' => 262,
                'word' => 'master-bate',
                'isolated_only' => false
            ],
            [
                'id' => 263,
                'word' => 'masterb8',
                'isolated_only' => false
            ],
            [
                'id' => 264,
                'word' => 'masterbat',
                'isolated_only' => false
            ],
            [
                'id' => 265,
                'word' => 'masterbat3',
                'isolated_only' => false
            ],
            [
                'id' => 266,
                'word' => 'masterbate',
                'isolated_only' => false
            ],
            [
                'id' => 267,
                'word' => 'masterbation',
                'isolated_only' => false
            ],
            [
                'id' => 268,
                'word' => 'masterbations',
                'isolated_only' => false
            ],
            [
                'id' => 269,
                'word' => 'masturbate',
                'isolated_only' => false
            ],
            [
                'id' => 270,
                'word' => 'mo-fo',
                'isolated_only' => false
            ],
            [
                'id' => 271,
                'word' => 'mof0',
                'isolated_only' => false
            ],
            [
                'id' => 272,
                'word' => 'mofo',
                'isolated_only' => false
            ],
            [
                'id' => 273,
                'word' => 'mothafuck',
                'isolated_only' => false
            ],
            [
                'id' => 274,
                'word' => 'mothafucka',
                'isolated_only' => false
            ],
            [
                'id' => 275,
                'word' => 'mothafuckas',
                'isolated_only' => false
            ],
            [
                'id' => 276,
                'word' => 'mothafuckaz',
                'isolated_only' => false
            ],
            [
                'id' => 277,
                'word' => 'mothafucked',
                'isolated_only' => false
            ],
            [
                'id' => 278,
                'word' => 'mothafucker',
                'isolated_only' => false
            ],
            [
                'id' => 279,
                'word' => 'mothafuckers',
                'isolated_only' => false
            ],
            [
                'id' => 280,
                'word' => 'mothafuckin',
                'isolated_only' => false
            ],
            [
                'id' => 281,
                'word' => 'mothafucking',
                'isolated_only' => false
            ],
            [
                'id' => 282,
                'word' => 'mothafuckings',
                'isolated_only' => false
            ],
            [
                'id' => 283,
                'word' => 'mothafucks',
                'isolated_only' => false
            ],
            [
                'id' => 284,
                'word' => 'mother fucker',
                'isolated_only' => false
            ],
            [
                'id' => 285,
                'word' => 'motherfuck',
                'isolated_only' => false
            ],
            [
                'id' => 286,
                'word' => 'motherfucked',
                'isolated_only' => false
            ],
            [
                'id' => 287,
                'word' => 'motherfucker',
                'isolated_only' => false
            ],
            [
                'id' => 288,
                'word' => 'motherfuckers',
                'isolated_only' => false
            ],
            [
                'id' => 289,
                'word' => 'motherfuckin',
                'isolated_only' => false
            ],
            [
                'id' => 290,
                'word' => 'motherfucking',
                'isolated_only' => false
            ],
            [
                'id' => 291,
                'word' => 'motherfuckings',
                'isolated_only' => false
            ],
            [
                'id' => 292,
                'word' => 'motherfuckka',
                'isolated_only' => false
            ],
            [
                'id' => 293,
                'word' => 'motherfucks',
                'isolated_only' => false
            ],
            [
                'id' => 294,
                'word' => 'muff',
                'isolated_only' => false
            ],
            [
                'id' => 295,
                'word' => 'mutha',
                'isolated_only' => false
            ],
            [
                'id' => 296,
                'word' => 'muthafecker',
                'isolated_only' => false
            ],
            [
                'id' => 297,
                'word' => 'muthafuckker',
                'isolated_only' => false
            ],
            [
                'id' => 298,
                'word' => 'muther',
                'isolated_only' => false
            ],
            [
                'id' => 299,
                'word' => 'mutherfucker',
                'isolated_only' => false
            ],
            [
                'id' => 300,
                'word' => 'n1gga',
                'isolated_only' => false
            ],
            [
                'id' => 301,
                'word' => 'n1gger',
                'isolated_only' => false
            ],
            [
                'id' => 302,
                'word' => 'nazi',
                'isolated_only' => false
            ],
            [
                'id' => 303,
                'word' => 'nigg3r',
                'isolated_only' => false
            ],
            [
                'id' => 304,
                'word' => 'nigg4h',
                'isolated_only' => false
            ],
            [
                'id' => 305,
                'word' => 'nigga',
                'isolated_only' => false
            ],
            [
                'id' => 306,
                'word' => 'niggah',
                'isolated_only' => false
            ],
            [
                'id' => 307,
                'word' => 'niggas',
                'isolated_only' => false
            ],
            [
                'id' => 308,
                'word' => 'niggaz',
                'isolated_only' => false
            ],
            [
                'id' => 309,
                'word' => 'nigger',
                'isolated_only' => false
            ],
            [
                'id' => 310,
                'word' => 'niggers',
                'isolated_only' => false
            ],
            [
                'id' => 311,
                'word' => 'nob',
                'isolated_only' => true
            ],
            [
                'id' => 312,
                'word' => 'nob jokey',
                'isolated_only' => false
            ],
            [
                'id' => 313,
                'word' => 'nobhead',
                'isolated_only' => false
            ],
            [
                'id' => 314,
                'word' => 'nobjocky',
                'isolated_only' => false
            ],
            [
                'id' => 315,
                'word' => 'nobjokey',
                'isolated_only' => false
            ],
            [
                'id' => 316,
                'word' => 'numbnuts',
                'isolated_only' => false
            ],
            [
                'id' => 317,
                'word' => 'nutsack',
                'isolated_only' => false
            ],
            [
                'id' => 318,
                'word' => 'orgasim',
                'isolated_only' => false
            ],
            [
                'id' => 319,
                'word' => 'orgasims',
                'isolated_only' => false
            ],
            [
                'id' => 320,
                'word' => 'orgasm',
                'isolated_only' => false
            ],
            [
                'id' => 321,
                'word' => 'orgasms',
                'isolated_only' => false
            ],
            [
                'id' => 322,
                'word' => 'p0rn',
                'isolated_only' => false
            ],
            [
                'id' => 323,
                'word' => 'pawn',
                'isolated_only' => false
            ],
            [
                'id' => 324,
                'word' => 'pecker',
                'isolated_only' => false
            ],
            [
                'id' => 325,
                'word' => 'penis',
                'isolated_only' => false
            ],
            [
                'id' => 326,
                'word' => 'penisfucker',
                'isolated_only' => false
            ],
            [
                'id' => 327,
                'word' => 'phonesex',
                'isolated_only' => false
            ],
            [
                'id' => 328,
                'word' => 'phuck',
                'isolated_only' => false
            ],
            [
                'id' => 329,
                'word' => 'phuk',
                'isolated_only' => false
            ],
            [
                'id' => 330,
                'word' => 'phuked',
                'isolated_only' => false
            ],
            [
                'id' => 331,
                'word' => 'phuking',
                'isolated_only' => false
            ],
            [
                'id' => 332,
                'word' => 'phukked',
                'isolated_only' => false
            ],
            [
                'id' => 333,
                'word' => 'phukking',
                'isolated_only' => false
            ],
            [
                'id' => 334,
                'word' => 'phuks',
                'isolated_only' => false
            ],
            [
                'id' => 335,
                'word' => 'phuq',
                'isolated_only' => false
            ],
            [
                'id' => 336,
                'word' => 'pigfucker',
                'isolated_only' => false
            ],
            [
                'id' => 337,
                'word' => 'pimpis',
                'isolated_only' => false
            ],
            [
                'id' => 338,
                'word' => 'piss',
                'isolated_only' => false
            ],
            [
                'id' => 339,
                'word' => 'pissed',
                'isolated_only' => false
            ],
            [
                'id' => 340,
                'word' => 'pisser',
                'isolated_only' => false
            ],
            [
                'id' => 341,
                'word' => 'pissers',
                'isolated_only' => false
            ],
            [
                'id' => 342,
                'word' => 'pisses',
                'isolated_only' => false
            ],
            [
                'id' => 343,
                'word' => 'pissflaps',
                'isolated_only' => false
            ],
            [
                'id' => 344,
                'word' => 'pissin',
                'isolated_only' => false
            ],
            [
                'id' => 345,
                'word' => 'pissing',
                'isolated_only' => false
            ],
            [
                'id' => 346,
                'word' => 'pissoff',
                'isolated_only' => false
            ],
            [
                'id' => 347,
                'word' => 'poop',
                'isolated_only' => false
            ],
            [
                'id' => 348,
                'word' => 'porn',
                'isolated_only' => false
            ],
            [
                'id' => 349,
                'word' => 'porno',
                'isolated_only' => false
            ],
            [
                'id' => 350,
                'word' => 'pornography',
                'isolated_only' => false
            ],
            [
                'id' => 351,
                'word' => 'pornos',
                'isolated_only' => false
            ],
            [
                'id' => 352,
                'word' => 'prick',
                'isolated_only' => false
            ],
            [
                'id' => 353,
                'word' => 'pricks',
                'isolated_only' => false
            ],
            [
                'id' => 354,
                'word' => 'pron',
                'isolated_only' => false
            ],
            [
                'id' => 355,
                'word' => 'pube',
                'isolated_only' => false
            ],
            [
                'id' => 356,
                'word' => 'pusse',
                'isolated_only' => false
            ],
            [
                'id' => 357,
                'word' => 'pussi',
                'isolated_only' => false
            ],
            [
                'id' => 358,
                'word' => 'pussies',
                'isolated_only' => false
            ],
            [
                'id' => 359,
                'word' => 'pussy',
                'isolated_only' => false
            ],
            [
                'id' => 360,
                'word' => 'pussys',
                'isolated_only' => false
            ],
            [
                'id' => 361,
                'word' => 'rectum',
                'isolated_only' => false
            ],
            [
                'id' => 362,
                'word' => 'retard',
                'isolated_only' => false
            ],
            [
                'id' => 363,
                'word' => 'rimjaw',
                'isolated_only' => false
            ],
            [
                'id' => 364,
                'word' => 'rimming',
                'isolated_only' => false
            ],
            [
                'id' => 365,
                'word' => 's hit',
                'isolated_only' => false
            ],
            [
                'id' => 366,
                'word' => 's.o.b.',
                'isolated_only' => false
            ],
            [
                'id' => 367,
                'word' => 'sadist',
                'isolated_only' => false
            ],
            [
                'id' => 368,
                'word' => 'schlong',
                'isolated_only' => false
            ],
            [
                'id' => 369,
                'word' => 'screwing',
                'isolated_only' => false
            ],
            [
                'id' => 370,
                'word' => 'scroat',
                'isolated_only' => false
            ],
            [
                'id' => 371,
                'word' => 'scrote',
                'isolated_only' => false
            ],
            [
                'id' => 372,
                'word' => 'scrotum',
                'isolated_only' => false
            ],
            [
                'id' => 373,
                'word' => 'semen',
                'isolated_only' => false
            ],
            [
                'id' => 374,
                'word' => 'sex',
                'isolated_only' => false
            ],
            [
                'id' => 375,
                'word' => 'sh!+',
                'isolated_only' => false
            ],
            [
                'id' => 376,
                'word' => 'sh!t',
                'isolated_only' => false
            ],
            [
                'id' => 377,
                'word' => 'sh1t',
                'isolated_only' => false
            ],
            [
                'id' => 378,
                'word' => 'shag',
                'isolated_only' => false
            ],
            [
                'id' => 379,
                'word' => 'shagger',
                'isolated_only' => false
            ],
            [
                'id' => 380,
                'word' => 'shaggin',
                'isolated_only' => false
            ],
            [
                'id' => 381,
                'word' => 'shagging',
                'isolated_only' => false
            ],
            [
                'id' => 382,
                'word' => 'shemale',
                'isolated_only' => false
            ],
            [
                'id' => 383,
                'word' => 'shi+',
                'isolated_only' => false
            ],
            [
                'id' => 384,
                'word' => 'shit',
                'isolated_only' => false
            ],
            [
                'id' => 385,
                'word' => 'shitdick',
                'isolated_only' => false
            ],
            [
                'id' => 386,
                'word' => 'shite',
                'isolated_only' => false
            ],
            [
                'id' => 387,
                'word' => 'shited',
                'isolated_only' => false
            ],
            [
                'id' => 388,
                'word' => 'shitey',
                'isolated_only' => false
            ],
            [
                'id' => 389,
                'word' => 'shitfuck',
                'isolated_only' => false
            ],
            [
                'id' => 390,
                'word' => 'shitfull',
                'isolated_only' => false
            ],
            [
                'id' => 391,
                'word' => 'shithead',
                'isolated_only' => false
            ],
            [
                'id' => 392,
                'word' => 'shiting',
                'isolated_only' => false
            ],
            [
                'id' => 393,
                'word' => 'shitings',
                'isolated_only' => false
            ],
            [
                'id' => 394,
                'word' => 'shits',
                'isolated_only' => false
            ],
            [
                'id' => 395,
                'word' => 'shitted',
                'isolated_only' => false
            ],
            [
                'id' => 396,
                'word' => 'shitter',
                'isolated_only' => false
            ],
            [
                'id' => 397,
                'word' => 'shitters',
                'isolated_only' => false
            ],
            [
                'id' => 398,
                'word' => 'shitting',
                'isolated_only' => false
            ],
            [
                'id' => 399,
                'word' => 'shittings',
                'isolated_only' => false
            ],
            [
                'id' => 400,
                'word' => 'shitty',
                'isolated_only' => false
            ],
            [
                'id' => 401,
                'word' => 'skank',
                'isolated_only' => false
            ],
            [
                'id' => 402,
                'word' => 'slut',
                'isolated_only' => false
            ],
            [
                'id' => 403,
                'word' => 'sluts',
                'isolated_only' => false
            ],
            [
                'id' => 404,
                'word' => 'smegma',
                'isolated_only' => false
            ],
            [
                'id' => 405,
                'word' => 'smut',
                'isolated_only' => false
            ],
            [
                'id' => 406,
                'word' => 'snatch',
                'isolated_only' => false
            ],
            [
                'id' => 407,
                'word' => 'son-of-a-bitch',
                'isolated_only' => false
            ],
            [
                'id' => 408,
                'word' => 'spac',
                'isolated_only' => true
            ],
            [
                'id' => 409,
                'word' => 'spunk',
                'isolated_only' => false
            ],
            [
                'id' => 410,
                'word' => 's_h_i_t',
                'isolated_only' => false
            ],
            [
                'id' => 411,
                'word' => 't1tt1e5',
                'isolated_only' => false
            ],
            [
                'id' => 412,
                'word' => 't1tties',
                'isolated_only' => false
            ],
            [
                'id' => 413,
                'word' => 'teets',
                'isolated_only' => false
            ],
            [
                'id' => 414,
                'word' => 'teez',
                'isolated_only' => false
            ],
            [
                'id' => 415,
                'word' => 'testical',
                'isolated_only' => false
            ],
            [
                'id' => 416,
                'word' => 'testicle',
                'isolated_only' => false
            ],
            [
                'id' => 417,
                'word' => 'tit',
                'isolated_only' => true
            ],
            [
                'id' => 418,
                'word' => 'titfuck',
                'isolated_only' => false
            ],
            [
                'id' => 419,
                'word' => 'tits',
                'isolated_only' => false
            ],
            [
                'id' => 420,
                'word' => 'titt',
                'isolated_only' => false
            ],
            [
                'id' => 421,
                'word' => 'tittie5',
                'isolated_only' => false
            ],
            [
                'id' => 422,
                'word' => 'tittiefucker',
                'isolated_only' => false
            ],
            [
                'id' => 423,
                'word' => 'titties',
                'isolated_only' => false
            ],
            [
                'id' => 424,
                'word' => 'tittyfuck',
                'isolated_only' => false
            ],
            [
                'id' => 425,
                'word' => 'tittywank',
                'isolated_only' => false
            ],
            [
                'id' => 426,
                'word' => 'titwank',
                'isolated_only' => false
            ],
            [
                'id' => 427,
                'word' => 'tosser',
                'isolated_only' => false
            ],
            [
                'id' => 428,
                'word' => 'turd',
                'isolated_only' => false
            ],
            [
                'id' => 429,
                'word' => 'tw4t',
                'isolated_only' => false
            ],
            [
                'id' => 430,
                'word' => 'twat',
                'isolated_only' => false
            ],
            [
                'id' => 431,
                'word' => 'twathead',
                'isolated_only' => false
            ],
            [
                'id' => 432,
                'word' => 'twatty',
                'isolated_only' => false
            ],
            [
                'id' => 433,
                'word' => 'twunt',
                'isolated_only' => false
            ],
            [
                'id' => 434,
                'word' => 'twunter',
                'isolated_only' => false
            ],
            [
                'id' => 435,
                'word' => 'v14gra',
                'isolated_only' => false
            ],
            [
                'id' => 436,
                'word' => 'v1gra',
                'isolated_only' => false
            ],
            [
                'id' => 437,
                'word' => 'vagina',
                'isolated_only' => false
            ],
            [
                'id' => 438,
                'word' => 'viagra',
                'isolated_only' => false
            ],
            [
                'id' => 439,
                'word' => 'vulva',
                'isolated_only' => false
            ],
            [
                'id' => 440,
                'word' => 'w00se',
                'isolated_only' => false
            ],
            [
                'id' => 441,
                'word' => 'wang',
                'isolated_only' => true
            ],
            [
                'id' => 442,
                'word' => 'wank',
                'isolated_only' => false
            ],
            [
                'id' => 443,
                'word' => 'wanker',
                'isolated_only' => false
            ],
            [
                'id' => 444,
                'word' => 'wanky',
                'isolated_only' => false
            ],
            [
                'id' => 445,
                'word' => 'whoar',
                'isolated_only' => false
            ],
            [
                'id' => 446,
                'word' => 'whore',
                'isolated_only' => false
            ],
            [
                'id' => 447,
                'word' => 'willies',
                'isolated_only' => false
            ],
            [
                'id' => 448,
                'word' => 'willy',
                'isolated_only' => false
            ],
            [
                'id' => 449,
                'word' => 'xrated',
                'isolated_only' => false
            ],
            [
                'id' => 450,
                'word' => 'xxx',
                'isolated_only' => false
            ],
            [
                'id' => 451,
                'word' => 'arsehole',
                'isolated_only' => false
            ],
            [
                'id' => 452,
                'word' => 'assbag',
                'isolated_only' => false
            ],
            [
                'id' => 453,
                'word' => 'assbandit',
                'isolated_only' => false
            ],
            [
                'id' => 454,
                'word' => 'assbanger',
                'isolated_only' => false
            ],
            [
                'id' => 455,
                'word' => 'assbite',
                'isolated_only' => false
            ],
            [
                'id' => 456,
                'word' => 'assclown',
                'isolated_only' => false
            ],
            [
                'id' => 457,
                'word' => 'asscock',
                'isolated_only' => false
            ],
            [
                'id' => 458,
                'word' => 'asscracker',
                'isolated_only' => false
            ],
            [
                'id' => 459,
                'word' => 'assface',
                'isolated_only' => false
            ],
            [
                'id' => 460,
                'word' => 'assfuck',
                'isolated_only' => false
            ],
            [
                'id' => 461,
                'word' => 'assgoblin',
                'isolated_only' => false
            ],
            [
                'id' => 462,
                'word' => 'asshat',
                'isolated_only' => false
            ],
            [
                'id' => 463,
                'word' => 'ass-hat',
                'isolated_only' => false
            ],
            [
                'id' => 464,
                'word' => 'asshead',
                'isolated_only' => false
            ],
            [
                'id' => 465,
                'word' => 'asshopper',
                'isolated_only' => false
            ],
            [
                'id' => 466,
                'word' => 'ass-jabber',
                'isolated_only' => false
            ],
            [
                'id' => 467,
                'word' => 'assjacker',
                'isolated_only' => false
            ],
            [
                'id' => 468,
                'word' => 'asslick',
                'isolated_only' => false
            ],
            [
                'id' => 469,
                'word' => 'asslicker',
                'isolated_only' => false
            ],
            [
                'id' => 470,
                'word' => 'assmonkey',
                'isolated_only' => false
            ],
            [
                'id' => 471,
                'word' => 'assmunch',
                'isolated_only' => false
            ],
            [
                'id' => 472,
                'word' => 'assmuncher',
                'isolated_only' => false
            ],
            [
                'id' => 473,
                'word' => 'assnigger',
                'isolated_only' => false
            ],
            [
                'id' => 474,
                'word' => 'asspirate',
                'isolated_only' => false
            ],
            [
                'id' => 475,
                'word' => 'ass-pirate',
                'isolated_only' => false
            ],
            [
                'id' => 476,
                'word' => 'assshit',
                'isolated_only' => false
            ],
            [
                'id' => 477,
                'word' => 'assshole',
                'isolated_only' => false
            ],
            [
                'id' => 478,
                'word' => 'asssucker',
                'isolated_only' => false
            ],
            [
                'id' => 479,
                'word' => 'asswad',
                'isolated_only' => false
            ],
            [
                'id' => 480,
                'word' => 'asswipe',
                'isolated_only' => false
            ],
            [
                'id' => 481,
                'word' => 'axwound',
                'isolated_only' => false
            ],
            [
                'id' => 482,
                'word' => 'bampot',
                'isolated_only' => false
            ],
            [
                'id' => 483,
                'word' => 'beaner',
                'isolated_only' => false
            ],
            [
                'id' => 484,
                'word' => 'bitchass',
                'isolated_only' => false
            ],
            [
                'id' => 485,
                'word' => 'bitchtits',
                'isolated_only' => false
            ],
            [
                'id' => 486,
                'word' => 'bitchy',
                'isolated_only' => false
            ],
            [
                'id' => 487,
                'word' => 'bollocks',
                'isolated_only' => false
            ],
            [
                'id' => 488,
                'word' => 'bollox',
                'isolated_only' => false
            ],
            [
                'id' => 489,
                'word' => 'brotherfucker',
                'isolated_only' => false
            ],
            [
                'id' => 490,
                'word' => 'bullshit',
                'isolated_only' => false
            ],
            [
                'id' => 491,
                'word' => 'bumblefuck',
                'isolated_only' => false
            ],
            [
                'id' => 492,
                'word' => 'butt plug',
                'isolated_only' => false
            ],
            [
                'id' => 493,
                'word' => 'buttfucka',
                'isolated_only' => false
            ],
            [
                'id' => 494,
                'word' => 'butt-pirate',
                'isolated_only' => false
            ],
            [
                'id' => 495,
                'word' => 'buttfucker',
                'isolated_only' => false
            ],
            [
                'id' => 496,
                'word' => 'camel toe',
                'isolated_only' => false
            ],
            [
                'id' => 497,
                'word' => 'carpetmuncher',
                'isolated_only' => false
            ],
            [
                'id' => 498,
                'word' => 'chesticle',
                'isolated_only' => false
            ],
            [
                'id' => 499,
                'word' => 'chinc',
                'isolated_only' => false
            ],
            [
                'id' => 500,
                'word' => 'choad',
                'isolated_only' => false
            ],
            [
                'id' => 501,
                'word' => 'chode',
                'isolated_only' => false
            ],
            [
                'id' => 502,
                'word' => 'clitface',
                'isolated_only' => false
            ],
            [
                'id' => 503,
                'word' => 'clitfuck',
                'isolated_only' => false
            ],
            [
                'id' => 504,
                'word' => 'clusterfuck',
                'isolated_only' => false
            ],
            [
                'id' => 505,
                'word' => 'cockass',
                'isolated_only' => false
            ],
            [
                'id' => 506,
                'word' => 'cockbite',
                'isolated_only' => false
            ],
            [
                'id' => 507,
                'word' => 'cockburger',
                'isolated_only' => false
            ],
            [
                'id' => 508,
                'word' => 'cockfucker',
                'isolated_only' => false
            ],
            [
                'id' => 509,
                'word' => 'cockjockey',
                'isolated_only' => false
            ],
            [
                'id' => 510,
                'word' => 'cockknoker',
                'isolated_only' => false
            ],
            [
                'id' => 511,
                'word' => 'cockmaster',
                'isolated_only' => false
            ],
            [
                'id' => 512,
                'word' => 'cockmongler',
                'isolated_only' => false
            ],
            [
                'id' => 513,
                'word' => 'cockmongruel',
                'isolated_only' => false
            ],
            [
                'id' => 514,
                'word' => 'cockmonkey',
                'isolated_only' => false
            ],
            [
                'id' => 515,
                'word' => 'cocknose',
                'isolated_only' => false
            ],
            [
                'id' => 516,
                'word' => 'cocknugget',
                'isolated_only' => false
            ],
            [
                'id' => 517,
                'word' => 'cockshit',
                'isolated_only' => false
            ],
            [
                'id' => 518,
                'word' => 'cocksmith',
                'isolated_only' => false
            ],
            [
                'id' => 519,
                'word' => 'cocksmoke',
                'isolated_only' => false
            ],
            [
                'id' => 520,
                'word' => 'cocksmoker',
                'isolated_only' => false
            ],
            [
                'id' => 521,
                'word' => 'cocksniffer',
                'isolated_only' => false
            ],
            [
                'id' => 522,
                'word' => 'cockwaffle',
                'isolated_only' => false
            ],
            [
                'id' => 523,
                'word' => 'coochie',
                'isolated_only' => false
            ],
            [
                'id' => 524,
                'word' => 'coochy',
                'isolated_only' => false
            ],
            [
                'id' => 525,
                'word' => 'cooter',
                'isolated_only' => false
            ],
            [
                'id' => 526,
                'word' => 'cracker',
                'isolated_only' => false
            ],
            [
                'id' => 527,
                'word' => 'cumbubble',
                'isolated_only' => false
            ],
            [
                'id' => 528,
                'word' => 'cumdumpster',
                'isolated_only' => false
            ],
            [
                'id' => 529,
                'word' => 'cumguzzler',
                'isolated_only' => false
            ],
            [
                'id' => 530,
                'word' => 'cumjockey',
                'isolated_only' => false
            ],
            [
                'id' => 531,
                'word' => 'cumslut',
                'isolated_only' => false
            ],
            [
                'id' => 532,
                'word' => 'cumtart',
                'isolated_only' => false
            ],
            [
                'id' => 533,
                'word' => 'cunnie',
                'isolated_only' => false
            ],
            [
                'id' => 534,
                'word' => 'cuntass',
                'isolated_only' => false
            ],
            [
                'id' => 535,
                'word' => 'cuntface',
                'isolated_only' => false
            ],
            [
                'id' => 536,
                'word' => 'cunthole',
                'isolated_only' => false
            ],
            [
                'id' => 537,
                'word' => 'cuntrag',
                'isolated_only' => false
            ],
            [
                'id' => 538,
                'word' => 'cuntslut',
                'isolated_only' => false
            ],
            [
                'id' => 539,
                'word' => 'dago',
                'isolated_only' => false
            ],
            [
                'id' => 540,
                'word' => 'deggo',
                'isolated_only' => false
            ],
            [
                'id' => 541,
                'word' => 'dickbag',
                'isolated_only' => false
            ],
            [
                'id' => 542,
                'word' => 'dickbeaters',
                'isolated_only' => false
            ],
            [
                'id' => 543,
                'word' => 'dickface',
                'isolated_only' => false
            ],
            [
                'id' => 544,
                'word' => 'dickfuck',
                'isolated_only' => false
            ],
            [
                'id' => 545,
                'word' => 'dickfucker',
                'isolated_only' => false
            ],
            [
                'id' => 546,
                'word' => 'dickhole',
                'isolated_only' => false
            ],
            [
                'id' => 547,
                'word' => 'dickjuice',
                'isolated_only' => false
            ],
            [
                'id' => 548,
                'word' => 'dickmilk ',
                'isolated_only' => false
            ],
            [
                'id' => 549,
                'word' => 'dickmonger',
                'isolated_only' => false
            ],
            [
                'id' => 550,
                'word' => 'dicks',
                'isolated_only' => false
            ],
            [
                'id' => 551,
                'word' => 'dickslap',
                'isolated_only' => false
            ],
            [
                'id' => 552,
                'word' => 'dick-sneeze',
                'isolated_only' => false
            ],
            [
                'id' => 553,
                'word' => 'dicksucker',
                'isolated_only' => false
            ],
            [
                'id' => 554,
                'word' => 'dicksucking',
                'isolated_only' => false
            ],
            [
                'id' => 555,
                'word' => 'dicktickler',
                'isolated_only' => false
            ],
            [
                'id' => 556,
                'word' => 'dickwad',
                'isolated_only' => false
            ],
            [
                'id' => 557,
                'word' => 'dickweasel',
                'isolated_only' => false
            ],
            [
                'id' => 558,
                'word' => 'dickweed',
                'isolated_only' => false
            ],
            [
                'id' => 559,
                'word' => 'dickwod',
                'isolated_only' => false
            ],
            [
                'id' => 560,
                'word' => 'dike',
                'isolated_only' => false
            ],
            [
                'id' => 561,
                'word' => 'dipshit',
                'isolated_only' => false
            ],
            [
                'id' => 562,
                'word' => 'doochbag',
                'isolated_only' => false
            ],
            [
                'id' => 563,
                'word' => 'dookie',
                'isolated_only' => false
            ],
            [
                'id' => 564,
                'word' => 'douche',
                'isolated_only' => false
            ],
            [
                'id' => 565,
                'word' => 'douchebag',
                'isolated_only' => false
            ],
            [
                'id' => 566,
                'word' => 'douche-fag',
                'isolated_only' => false
            ],
            [
                'id' => 567,
                'word' => 'douchewaffle',
                'isolated_only' => false
            ],
            [
                'id' => 568,
                'word' => 'dumass',
                'isolated_only' => false
            ],
            [
                'id' => 569,
                'word' => 'dumb ass',
                'isolated_only' => false
            ],
            [
                'id' => 570,
                'word' => 'dumbass',
                'isolated_only' => false
            ],
            [
                'id' => 571,
                'word' => 'dumbfuck',
                'isolated_only' => false
            ],
            [
                'id' => 572,
                'word' => 'dumbshit',
                'isolated_only' => false
            ],
            [
                'id' => 573,
                'word' => 'dumshit',
                'isolated_only' => false
            ],
            [
                'id' => 574,
                'word' => 'fagbag',
                'isolated_only' => false
            ],
            [
                'id' => 575,
                'word' => 'fagfucker',
                'isolated_only' => false
            ],
            [
                'id' => 576,
                'word' => 'faggit',
                'isolated_only' => false
            ],
            [
                'id' => 577,
                'word' => 'faggotcock',
                'isolated_only' => false
            ],
            [
                'id' => 578,
                'word' => 'fagtard',
                'isolated_only' => false
            ],
            [
                'id' => 579,
                'word' => 'feltch',
                'isolated_only' => false
            ],
            [
                'id' => 580,
                'word' => 'flamer',
                'isolated_only' => false
            ],
            [
                'id' => 581,
                'word' => 'fuckass',
                'isolated_only' => false
            ],
            [
                'id' => 582,
                'word' => 'fuckbag',
                'isolated_only' => false
            ],
            [
                'id' => 583,
                'word' => 'fuckboy',
                'isolated_only' => false
            ],
            [
                'id' => 584,
                'word' => 'fuckbrain',
                'isolated_only' => false
            ],
            [
                'id' => 585,
                'word' => 'fuckbutt',
                'isolated_only' => false
            ],
            [
                'id' => 586,
                'word' => 'fuckbutter',
                'isolated_only' => false
            ],
            [
                'id' => 587,
                'word' => 'fuckersucker',
                'isolated_only' => false
            ],
            [
                'id' => 588,
                'word' => 'fuckface',
                'isolated_only' => false
            ],
            [
                'id' => 589,
                'word' => 'fuckhole',
                'isolated_only' => false
            ],
            [
                'id' => 590,
                'word' => 'fucknut',
                'isolated_only' => false
            ],
            [
                'id' => 591,
                'word' => 'fucknutt',
                'isolated_only' => false
            ],
            [
                'id' => 592,
                'word' => 'fuckoff',
                'isolated_only' => false
            ],
            [
                'id' => 593,
                'word' => 'fuckstick',
                'isolated_only' => false
            ],
            [
                'id' => 594,
                'word' => 'fucktard',
                'isolated_only' => false
            ],
            [
                'id' => 595,
                'word' => 'fucktart',
                'isolated_only' => false
            ],
            [
                'id' => 596,
                'word' => 'fuckup',
                'isolated_only' => false
            ],
            [
                'id' => 597,
                'word' => 'fuckwad',
                'isolated_only' => false
            ],
            [
                'id' => 598,
                'word' => 'fuckwitt',
                'isolated_only' => false
            ],
            [
                'id' => 599,
                'word' => 'gay',
                'isolated_only' => false
            ],
            [
                'id' => 600,
                'word' => 'gayass',
                'isolated_only' => false
            ],
            [
                'id' => 601,
                'word' => 'gaybob',
                'isolated_only' => false
            ],
            [
                'id' => 602,
                'word' => 'gaydo',
                'isolated_only' => false
            ],
            [
                'id' => 603,
                'word' => 'gayfuck',
                'isolated_only' => false
            ],
            [
                'id' => 604,
                'word' => 'gayfuckist',
                'isolated_only' => false
            ],
            [
                'id' => 605,
                'word' => 'gaytard',
                'isolated_only' => false
            ],
            [
                'id' => 606,
                'word' => 'gaywad',
                'isolated_only' => false
            ],
            [
                'id' => 607,
                'word' => 'goddamnit',
                'isolated_only' => false
            ],
            [
                'id' => 608,
                'word' => 'gooch',
                'isolated_only' => false
            ],
            [
                'id' => 609,
                'word' => 'gook',
                'isolated_only' => false
            ],
            [
                'id' => 610,
                'word' => 'gringo',
                'isolated_only' => false
            ],
            [
                'id' => 611,
                'word' => 'guido',
                'isolated_only' => false
            ],
            [
                'id' => 612,
                'word' => 'handjob',
                'isolated_only' => false
            ],
            [
                'id' => 613,
                'word' => 'hard on',
                'isolated_only' => false
            ],
            [
                'id' => 614,
                'word' => 'heeb',
                'isolated_only' => false
            ],
            [
                'id' => 615,
                'word' => 'ho',
                'isolated_only' => false
            ],
            [
                'id' => 616,
                'word' => 'hoe',
                'isolated_only' => false
            ],
            [
                'id' => 617,
                'word' => 'homodumbshit',
                'isolated_only' => false
            ],
            [
                'id' => 618,
                'word' => 'honkey',
                'isolated_only' => false
            ],
            [
                'id' => 619,
                'word' => 'humping',
                'isolated_only' => false
            ],
            [
                'id' => 620,
                'word' => 'jackass',
                'isolated_only' => false
            ],
            [
                'id' => 621,
                'word' => 'jagoff',
                'isolated_only' => false
            ],
            [
                'id' => 622,
                'word' => 'jerk off',
                'isolated_only' => false
            ],
            [
                'id' => 623,
                'word' => 'jerkass',
                'isolated_only' => false
            ],
            [
                'id' => 624,
                'word' => 'jigaboo',
                'isolated_only' => false
            ],
            [
                'id' => 625,
                'word' => 'jungle bunny',
                'isolated_only' => false
            ],
            [
                'id' => 626,
                'word' => 'junglebunny',
                'isolated_only' => false
            ],
            [
                'id' => 627,
                'word' => 'kike',
                'isolated_only' => false
            ],
            [
                'id' => 628,
                'word' => 'kooch',
                'isolated_only' => false
            ],
            [
                'id' => 629,
                'word' => 'kootch',
                'isolated_only' => false
            ],
            [
                'id' => 630,
                'word' => 'kraut',
                'isolated_only' => false
            ],
            [
                'id' => 631,
                'word' => 'kunt',
                'isolated_only' => false
            ],
            [
                'id' => 632,
                'word' => 'kyke',
                'isolated_only' => false
            ],
            [
                'id' => 633,
                'word' => 'lameass',
                'isolated_only' => false
            ],
            [
                'id' => 634,
                'word' => 'lardass',
                'isolated_only' => false
            ],
            [
                'id' => 635,
                'word' => 'lesbian',
                'isolated_only' => false
            ],
            [
                'id' => 636,
                'word' => 'lesbo',
                'isolated_only' => false
            ],
            [
                'id' => 637,
                'word' => 'lezzie',
                'isolated_only' => false
            ],
            [
                'id' => 638,
                'word' => 'mcfagget',
                'isolated_only' => false
            ],
            [
                'id' => 639,
                'word' => 'mick',
                'isolated_only' => false
            ],
            [
                'id' => 640,
                'word' => 'minge',
                'isolated_only' => false
            ],
            [
                'id' => 641,
                'word' => 'muffdiver',
                'isolated_only' => false
            ],
            [
                'id' => 642,
                'word' => 'munging',
                'isolated_only' => false
            ],
            [
                'id' => 643,
                'word' => 'negro',
                'isolated_only' => false
            ],
            [
                'id' => 644,
                'word' => 'nigaboo',
                'isolated_only' => false
            ],
            [
                'id' => 645,
                'word' => 'niglet',
                'isolated_only' => false
            ],
            [
                'id' => 646,
                'word' => 'nut sack',
                'isolated_only' => false
            ],
            [
                'id' => 647,
                'word' => 'paki',
                'isolated_only' => false
            ],
            [
                'id' => 648,
                'word' => 'panooch',
                'isolated_only' => false
            ],
            [
                'id' => 649,
                'word' => 'peckerhead',
                'isolated_only' => false
            ],
            [
                'id' => 650,
                'word' => 'penisbanger',
                'isolated_only' => false
            ],
            [
                'id' => 651,
                'word' => 'penispuffer',
                'isolated_only' => false
            ],
            [
                'id' => 652,
                'word' => 'pissed off',
                'isolated_only' => false
            ],
            [
                'id' => 653,
                'word' => 'polesmoker',
                'isolated_only' => false
            ],
            [
                'id' => 654,
                'word' => 'pollock',
                'isolated_only' => false
            ],
            [
                'id' => 655,
                'word' => 'poon',
                'isolated_only' => false
            ],
            [
                'id' => 656,
                'word' => 'poonani',
                'isolated_only' => false
            ],
            [
                'id' => 657,
                'word' => 'poonany',
                'isolated_only' => false
            ],
            [
                'id' => 658,
                'word' => 'poontang',
                'isolated_only' => false
            ],
            [
                'id' => 659,
                'word' => 'porch monkey',
                'isolated_only' => false
            ],
            [
                'id' => 660,
                'word' => 'porchmonkey',
                'isolated_only' => false
            ],
            [
                'id' => 661,
                'word' => 'punanny',
                'isolated_only' => false
            ],
            [
                'id' => 662,
                'word' => 'punta',
                'isolated_only' => false
            ],
            [
                'id' => 663,
                'word' => 'pussylicking',
                'isolated_only' => false
            ],
            [
                'id' => 664,
                'word' => 'puto',
                'isolated_only' => false
            ],
            [
                'id' => 665,
                'word' => 'queef',
                'isolated_only' => false
            ],
            [
                'id' => 666,
                'word' => 'queer',
                'isolated_only' => false
            ],
            [
                'id' => 667,
                'word' => 'queerbait',
                'isolated_only' => false
            ],
            [
                'id' => 668,
                'word' => 'queerhole',
                'isolated_only' => false
            ],
            [
                'id' => 669,
                'word' => 'renob',
                'isolated_only' => false
            ],
            [
                'id' => 670,
                'word' => 'rimjob',
                'isolated_only' => false
            ],
            [
                'id' => 671,
                'word' => 'ruski',
                'isolated_only' => false
            ],
            [
                'id' => 672,
                'word' => 'sand nigger',
                'isolated_only' => false
            ],
            [
                'id' => 673,
                'word' => 'sandnigger',
                'isolated_only' => false
            ],
            [
                'id' => 674,
                'word' => 'shitass',
                'isolated_only' => false
            ],
            [
                'id' => 675,
                'word' => 'shitbag',
                'isolated_only' => false
            ],
            [
                'id' => 676,
                'word' => 'shitbagger',
                'isolated_only' => false
            ],
            [
                'id' => 677,
                'word' => 'shitbrains',
                'isolated_only' => false
            ],
            [
                'id' => 678,
                'word' => 'shitbreath',
                'isolated_only' => false
            ],
            [
                'id' => 679,
                'word' => 'shitcanned',
                'isolated_only' => false
            ],
            [
                'id' => 680,
                'word' => 'shitcunt',
                'isolated_only' => false
            ],
            [
                'id' => 681,
                'word' => 'shitface',
                'isolated_only' => false
            ],
            [
                'id' => 682,
                'word' => 'shitfaced',
                'isolated_only' => false
            ],
            [
                'id' => 683,
                'word' => 'shithole',
                'isolated_only' => false
            ],
            [
                'id' => 684,
                'word' => 'shithouse',
                'isolated_only' => false
            ],
            [
                'id' => 685,
                'word' => 'shitspitter',
                'isolated_only' => false
            ],
            [
                'id' => 686,
                'word' => 'shitstain',
                'isolated_only' => false
            ],
            [
                'id' => 687,
                'word' => 'shittiest',
                'isolated_only' => false
            ],
            [
                'id' => 688,
                'word' => 'shiz',
                'isolated_only' => false
            ],
            [
                'id' => 689,
                'word' => 'shiznit',
                'isolated_only' => false
            ],
            [
                'id' => 690,
                'word' => 'skeet',
                'isolated_only' => false
            ],
            [
                'id' => 691,
                'word' => 'skullfuck',
                'isolated_only' => false
            ],
            [
                'id' => 692,
                'word' => 'slutbag',
                'isolated_only' => false
            ],
            [
                'id' => 693,
                'word' => 'smeg',
                'isolated_only' => false
            ],
            [
                'id' => 694,
                'word' => 'spic',
                'isolated_only' => false
            ],
            [
                'id' => 695,
                'word' => 'spick',
                'isolated_only' => false
            ],
            [
                'id' => 696,
                'word' => 'splooge',
                'isolated_only' => false
            ],
            [
                'id' => 697,
                'word' => 'spook',
                'isolated_only' => false
            ],
            [
                'id' => 698,
                'word' => 'suckass',
                'isolated_only' => false
            ],
            [
                'id' => 699,
                'word' => 'tard',
                'isolated_only' => false
            ],
            [
                'id' => 700,
                'word' => 'thundercunt',
                'isolated_only' => false
            ],
            [
                'id' => 701,
                'word' => 'twatlips',
                'isolated_only' => false
            ],
            [
                'id' => 702,
                'word' => 'twats',
                'isolated_only' => false
            ],
            [
                'id' => 703,
                'word' => 'twatwaffle',
                'isolated_only' => false
            ],
            [
                'id' => 704,
                'word' => 'unclefucker',
                'isolated_only' => false
            ],
            [
                'id' => 705,
                'word' => 'vag',
                'isolated_only' => false
            ],
            [
                'id' => 706,
                'word' => 'vajayjay',
                'isolated_only' => false
            ],
            [
                'id' => 707,
                'word' => 'va-j-j',
                'isolated_only' => false
            ],
            [
                'id' => 708,
                'word' => 'vjayjay',
                'isolated_only' => false
            ],
            [
                'id' => 709,
                'word' => 'wankjob',
                'isolated_only' => false
            ],
            [
                'id' => 710,
                'word' => 'wetback',
                'isolated_only' => false
            ],
            [
                'id' => 711,
                'word' => 'whorebag',
                'isolated_only' => false
            ],
            [
                'id' => 712,
                'word' => 'whoreface',
                'isolated_only' => false
            ],
            [
                'id' => 713,
                'word' => 'wop',
                'isolated_only' => false
            ],
            [
                'id' => 714,
                'word' => 'breeder',
                'isolated_only' => false
            ],
            [
                'id' => 715,
                'word' => 'cocklump',
                'isolated_only' => false
            ],
            [
                'id' => 716,
                'word' => 'creampie',
                'isolated_only' => false
            ],
            [
                'id' => 717,
                'word' => 'doublelift',
                'isolated_only' => false
            ],
            [
                'id' => 718,
                'word' => 'dumbcunt',
                'isolated_only' => false
            ],
            [
                'id' => 719,
                'word' => 'fuck off',
                'isolated_only' => false
            ],
            [
                'id' => 720,
                'word' => 'incest',
                'isolated_only' => false
            ],
            [
                'id' => 721,
                'word' => 'jack Off',
                'isolated_only' => false
            ],
            [
                'id' => 722,
                'word' => 'poopuncher',
                'isolated_only' => false
            ],
            [
                'id' => 723,
                'word' => 'sandler',
                'isolated_only' => false
            ],
            [
                'id' => 724,
                'word' => 'cockeye',
                'isolated_only' => false
            ],
            [
                'id' => 725,
                'word' => 'crotte',
                'isolated_only' => false
            ],
            [
                'id' => 726,
                'word' => 'foah',
                'isolated_only' => false
            ],
            [
                'id' => 727,
                'word' => 'fucktwat',
                'isolated_only' => false
            ],
            [
                'id' => 728,
                'word' => 'jaggi',
                'isolated_only' => false
            ],
            [
                'id' => 729,
                'word' => 'kunja',
                'isolated_only' => false
            ],
            [
                'id' => 730,
                'word' => 'pust',
                'isolated_only' => false
            ],
            [
                'id' => 731,
                'word' => 'sanger',
                'isolated_only' => false
            ],
            [
                'id' => 732,
                'word' => 'seks',
                'isolated_only' => false
            ],
            [
                'id' => 733,
                'word' => 'slag',
                'isolated_only' => false
            ],
            [
                'id' => 734,
                'word' => 'zubb',
                'isolated_only' => false
            ],
            [
                'id' => 735,
                'word' => '2g1c',
                'isolated_only' => false
            ],
            [
                'id' => 736,
                'word' => '2 girls 1 cup',
                'isolated_only' => false
            ],
            [
                'id' => 737,
                'word' => 'acrotomophilia',
                'isolated_only' => false
            ],
            [
                'id' => 738,
                'word' => 'alabama hot pocket',
                'isolated_only' => false
            ],
            [
                'id' => 739,
                'word' => 'alaskan pipeline',
                'isolated_only' => false
            ],
            [
                'id' => 740,
                'word' => 'anilingus',
                'isolated_only' => false
            ],
            [
                'id' => 741,
                'word' => 'apeshit',
                'isolated_only' => false
            ],
            [
                'id' => 742,
                'word' => 'auto erotic',
                'isolated_only' => false
            ],
            [
                'id' => 743,
                'word' => 'autoerotic',
                'isolated_only' => false
            ],
            [
                'id' => 744,
                'word' => 'babeland',
                'isolated_only' => false
            ],
            [
                'id' => 745,
                'word' => 'baby batter',
                'isolated_only' => false
            ],
            [
                'id' => 746,
                'word' => 'baby juice',
                'isolated_only' => false
            ],
            [
                'id' => 747,
                'word' => 'ball gag',
                'isolated_only' => false
            ],
            [
                'id' => 748,
                'word' => 'ball gravy',
                'isolated_only' => false
            ],
            [
                'id' => 749,
                'word' => 'ball kicking',
                'isolated_only' => false
            ],
            [
                'id' => 750,
                'word' => 'ball licking',
                'isolated_only' => false
            ],
            [
                'id' => 751,
                'word' => 'ball sack',
                'isolated_only' => false
            ],
            [
                'id' => 752,
                'word' => 'ball sucking',
                'isolated_only' => false
            ],
            [
                'id' => 753,
                'word' => 'bangbros',
                'isolated_only' => false
            ],
            [
                'id' => 754,
                'word' => 'bareback',
                'isolated_only' => false
            ],
            [
                'id' => 755,
                'word' => 'barely legal',
                'isolated_only' => false
            ],
            [
                'id' => 756,
                'word' => 'barenaked',
                'isolated_only' => false
            ],
            [
                'id' => 757,
                'word' => 'bastardo',
                'isolated_only' => false
            ],
            [
                'id' => 758,
                'word' => 'bastinado',
                'isolated_only' => false
            ],
            [
                'id' => 759,
                'word' => 'bbw',
                'isolated_only' => false
            ],
            [
                'id' => 760,
                'word' => 'bdsm',
                'isolated_only' => false
            ],
            [
                'id' => 761,
                'word' => 'beaners',
                'isolated_only' => false
            ],
            [
                'id' => 762,
                'word' => 'beaver cleaver',
                'isolated_only' => false
            ],
            [
                'id' => 763,
                'word' => 'beaver lips',
                'isolated_only' => false
            ],
            [
                'id' => 764,
                'word' => 'big black',
                'isolated_only' => false
            ],
            [
                'id' => 765,
                'word' => 'big breasts',
                'isolated_only' => false
            ],
            [
                'id' => 766,
                'word' => 'big knockers',
                'isolated_only' => false
            ],
            [
                'id' => 767,
                'word' => 'big tits',
                'isolated_only' => false
            ],
            [
                'id' => 768,
                'word' => 'bimbos',
                'isolated_only' => false
            ],
            [
                'id' => 769,
                'word' => 'birdlock',
                'isolated_only' => false
            ],
            [
                'id' => 770,
                'word' => 'black cock',
                'isolated_only' => false
            ],
            [
                'id' => 771,
                'word' => 'blonde action',
                'isolated_only' => false
            ],
            [
                'id' => 772,
                'word' => 'blonde on blonde action',
                'isolated_only' => false
            ],
            [
                'id' => 773,
                'word' => 'blow your load',
                'isolated_only' => false
            ],
            [
                'id' => 774,
                'word' => 'blue waffle',
                'isolated_only' => false
            ],
            [
                'id' => 775,
                'word' => 'blumpkin',
                'isolated_only' => false
            ],
            [
                'id' => 776,
                'word' => 'bondage',
                'isolated_only' => false
            ],
            [
                'id' => 777,
                'word' => 'booty call',
                'isolated_only' => false
            ],
            [
                'id' => 778,
                'word' => 'brown showers',
                'isolated_only' => false
            ],
            [
                'id' => 779,
                'word' => 'brunette action',
                'isolated_only' => false
            ],
            [
                'id' => 780,
                'word' => 'bukkake',
                'isolated_only' => false
            ],
            [
                'id' => 781,
                'word' => 'bulldyke',
                'isolated_only' => false
            ],
            [
                'id' => 782,
                'word' => 'bullet vibe',
                'isolated_only' => false
            ],
            [
                'id' => 783,
                'word' => 'bung hole',
                'isolated_only' => false
            ],
            [
                'id' => 784,
                'word' => 'bunghole',
                'isolated_only' => false
            ],
            [
                'id' => 785,
                'word' => 'busty',
                'isolated_only' => false
            ],
            [
                'id' => 786,
                'word' => 'buttcheeks',
                'isolated_only' => false
            ],
            [
                'id' => 787,
                'word' => 'camgirl',
                'isolated_only' => false
            ],
            [
                'id' => 788,
                'word' => 'camslut',
                'isolated_only' => false
            ],
            [
                'id' => 789,
                'word' => 'camwhore',
                'isolated_only' => false
            ],
            [
                'id' => 790,
                'word' => 'chocolate rosebuds',
                'isolated_only' => false
            ],
            [
                'id' => 791,
                'word' => 'circlejerk',
                'isolated_only' => false
            ],
            [
                'id' => 792,
                'word' => 'cleveland steamer',
                'isolated_only' => false
            ],
            [
                'id' => 793,
                'word' => 'clover clamps',
                'isolated_only' => false
            ],
            [
                'id' => 794,
                'word' => 'coprolagnia',
                'isolated_only' => false
            ],
            [
                'id' => 795,
                'word' => 'coprophilia',
                'isolated_only' => false
            ],
            [
                'id' => 796,
                'word' => 'cornhole',
                'isolated_only' => false
            ],
            [
                'id' => 797,
                'word' => 'coons',
                'isolated_only' => false
            ],
            [
                'id' => 798,
                'word' => 'darkie',
                'isolated_only' => false
            ],
            [
                'id' => 799,
                'word' => 'date rape',
                'isolated_only' => false
            ],
            [
                'id' => 800,
                'word' => 'daterape',
                'isolated_only' => false
            ],
            [
                'id' => 801,
                'word' => 'deep throat',
                'isolated_only' => false
            ],
            [
                'id' => 802,
                'word' => 'deepthroat',
                'isolated_only' => false
            ],
            [
                'id' => 803,
                'word' => 'dendrophilia',
                'isolated_only' => false
            ],
            [
                'id' => 804,
                'word' => 'dingleberry',
                'isolated_only' => false
            ],
            [
                'id' => 805,
                'word' => 'dingleberries',
                'isolated_only' => false
            ],
            [
                'id' => 806,
                'word' => 'dirty pillows',
                'isolated_only' => false
            ],
            [
                'id' => 807,
                'word' => 'dirty sanchez',
                'isolated_only' => false
            ],
            [
                'id' => 808,
                'word' => 'doggie style',
                'isolated_only' => false
            ],
            [
                'id' => 809,
                'word' => 'doggiestyle',
                'isolated_only' => false
            ],
            [
                'id' => 810,
                'word' => 'doggy style',
                'isolated_only' => false
            ],
            [
                'id' => 811,
                'word' => 'doggystyle',
                'isolated_only' => false
            ],
            [
                'id' => 812,
                'word' => 'dog style',
                'isolated_only' => false
            ],
            [
                'id' => 813,
                'word' => 'dolcett',
                'isolated_only' => false
            ],
            [
                'id' => 814,
                'word' => 'domination',
                'isolated_only' => false
            ],
            [
                'id' => 815,
                'word' => 'dominatrix',
                'isolated_only' => false
            ],
            [
                'id' => 816,
                'word' => 'dommes',
                'isolated_only' => false
            ],
            [
                'id' => 817,
                'word' => 'donkey punch',
                'isolated_only' => false
            ],
            [
                'id' => 818,
                'word' => 'double dong',
                'isolated_only' => false
            ],
            [
                'id' => 819,
                'word' => 'double penetration',
                'isolated_only' => false
            ],
            [
                'id' => 820,
                'word' => 'dp action',
                'isolated_only' => false
            ],
            [
                'id' => 821,
                'word' => 'dry hump',
                'isolated_only' => false
            ],
            [
                'id' => 822,
                'word' => 'dvda',
                'isolated_only' => false
            ],
            [
                'id' => 823,
                'word' => 'eat my ass',
                'isolated_only' => false
            ],
            [
                'id' => 824,
                'word' => 'ecchi',
                'isolated_only' => false
            ],
            [
                'id' => 825,
                'word' => 'erotic',
                'isolated_only' => false
            ],
            [
                'id' => 826,
                'word' => 'erotism',
                'isolated_only' => false
            ],
            [
                'id' => 827,
                'word' => 'escort',
                'isolated_only' => false
            ],
            [
                'id' => 828,
                'word' => 'eunuch',
                'isolated_only' => false
            ],
            [
                'id' => 829,
                'word' => 'fecal',
                'isolated_only' => false
            ],
            [
                'id' => 830,
                'word' => 'felch',
                'isolated_only' => false
            ],
            [
                'id' => 831,
                'word' => 'female squirting',
                'isolated_only' => false
            ],
            [
                'id' => 832,
                'word' => 'femdom',
                'isolated_only' => false
            ],
            [
                'id' => 833,
                'word' => 'figging',
                'isolated_only' => false
            ],
            [
                'id' => 834,
                'word' => 'fingerbang',
                'isolated_only' => false
            ],
            [
                'id' => 835,
                'word' => 'fingering',
                'isolated_only' => false
            ],
            [
                'id' => 836,
                'word' => 'fisting',
                'isolated_only' => false
            ],
            [
                'id' => 837,
                'word' => 'foot fetish',
                'isolated_only' => false
            ],
            [
                'id' => 838,
                'word' => 'footjob',
                'isolated_only' => false
            ],
            [
                'id' => 839,
                'word' => 'frotting',
                'isolated_only' => false
            ],
            [
                'id' => 840,
                'word' => 'fuck buttons',
                'isolated_only' => false
            ],
            [
                'id' => 841,
                'word' => 'fucktards',
                'isolated_only' => false
            ],
            [
                'id' => 842,
                'word' => 'futanari',
                'isolated_only' => false
            ],
            [
                'id' => 843,
                'word' => 'gang bang',
                'isolated_only' => false
            ],
            [
                'id' => 844,
                'word' => 'gay sex',
                'isolated_only' => false
            ],
            [
                'id' => 845,
                'word' => 'genitals',
                'isolated_only' => false
            ],
            [
                'id' => 846,
                'word' => 'giant cock',
                'isolated_only' => false
            ],
            [
                'id' => 847,
                'word' => 'girl on',
                'isolated_only' => false
            ],
            [
                'id' => 848,
                'word' => 'girl on top',
                'isolated_only' => false
            ],
            [
                'id' => 849,
                'word' => 'girls gone wild',
                'isolated_only' => false
            ],
            [
                'id' => 850,
                'word' => 'goatcx',
                'isolated_only' => false
            ],
            [
                'id' => 851,
                'word' => 'god damn',
                'isolated_only' => false
            ],
            [
                'id' => 852,
                'word' => 'gokkun',
                'isolated_only' => false
            ],
            [
                'id' => 853,
                'word' => 'golden shower',
                'isolated_only' => false
            ],
            [
                'id' => 854,
                'word' => 'goodpoop',
                'isolated_only' => false
            ],
            [
                'id' => 855,
                'word' => 'goo girl',
                'isolated_only' => false
            ],
            [
                'id' => 856,
                'word' => 'goregasm',
                'isolated_only' => false
            ],
            [
                'id' => 857,
                'word' => 'grope',
                'isolated_only' => false
            ],
            [
                'id' => 858,
                'word' => 'group sex',
                'isolated_only' => false
            ],
            [
                'id' => 859,
                'word' => 'g-spot',
                'isolated_only' => false
            ],
            [
                'id' => 860,
                'word' => 'guro',
                'isolated_only' => false
            ],
            [
                'id' => 861,
                'word' => 'hand job',
                'isolated_only' => false
            ],
            [
                'id' => 862,
                'word' => 'hard core',
                'isolated_only' => false
            ],
            [
                'id' => 863,
                'word' => 'hardcore',
                'isolated_only' => false
            ],
            [
                'id' => 864,
                'word' => 'hentai',
                'isolated_only' => false
            ],
            [
                'id' => 865,
                'word' => 'homoerotic',
                'isolated_only' => false
            ],
            [
                'id' => 866,
                'word' => 'hooker',
                'isolated_only' => false
            ],
            [
                'id' => 867,
                'word' => 'hot carl',
                'isolated_only' => false
            ],
            [
                'id' => 868,
                'word' => 'hot chick',
                'isolated_only' => false
            ],
            [
                'id' => 869,
                'word' => 'how to kill',
                'isolated_only' => false
            ],
            [
                'id' => 870,
                'word' => 'how to murder',
                'isolated_only' => false
            ],
            [
                'id' => 871,
                'word' => 'huge fat',
                'isolated_only' => false
            ],
            [
                'id' => 872,
                'word' => 'intercourse',
                'isolated_only' => false
            ],
            [
                'id' => 873,
                'word' => 'jail bait',
                'isolated_only' => false
            ],
            [
                'id' => 874,
                'word' => 'jailbait',
                'isolated_only' => false
            ],
            [
                'id' => 875,
                'word' => 'jelly donut',
                'isolated_only' => false
            ],
            [
                'id' => 876,
                'word' => 'jiggaboo',
                'isolated_only' => false
            ],
            [
                'id' => 877,
                'word' => 'jiggerboo',
                'isolated_only' => false
            ],
            [
                'id' => 878,
                'word' => 'juggs',
                'isolated_only' => false
            ],
            [
                'id' => 879,
                'word' => 'kinbaku',
                'isolated_only' => false
            ],
            [
                'id' => 880,
                'word' => 'kinkster',
                'isolated_only' => false
            ],
            [
                'id' => 881,
                'word' => 'kinky',
                'isolated_only' => false
            ],
            [
                'id' => 882,
                'word' => 'knobbing',
                'isolated_only' => false
            ],
            [
                'id' => 883,
                'word' => 'leather restraint',
                'isolated_only' => false
            ],
            [
                'id' => 884,
                'word' => 'leather straight jacket',
                'isolated_only' => false
            ],
            [
                'id' => 885,
                'word' => 'lemon party',
                'isolated_only' => false
            ],
            [
                'id' => 886,
                'word' => 'lolita',
                'isolated_only' => false
            ],
            [
                'id' => 887,
                'word' => 'lovemaking',
                'isolated_only' => false
            ],
            [
                'id' => 888,
                'word' => 'make me come',
                'isolated_only' => false
            ],
            [
                'id' => 889,
                'word' => 'male squirting',
                'isolated_only' => false
            ],
            [
                'id' => 890,
                'word' => 'menage a trois',
                'isolated_only' => false
            ],
            [
                'id' => 891,
                'word' => 'milf',
                'isolated_only' => false
            ],
            [
                'id' => 892,
                'word' => 'missionary position',
                'isolated_only' => false
            ],
            [
                'id' => 893,
                'word' => 'mound of venus',
                'isolated_only' => false
            ],
            [
                'id' => 894,
                'word' => 'mr hands',
                'isolated_only' => false
            ],
            [
                'id' => 895,
                'word' => 'muff diver',
                'isolated_only' => false
            ],
            [
                'id' => 896,
                'word' => 'muffdiving',
                'isolated_only' => false
            ],
            [
                'id' => 897,
                'word' => 'nambla',
                'isolated_only' => false
            ],
            [
                'id' => 898,
                'word' => 'nawashi',
                'isolated_only' => false
            ],
            [
                'id' => 899,
                'word' => 'neonazi',
                'isolated_only' => false
            ],
            [
                'id' => 900,
                'word' => 'nig nog',
                'isolated_only' => false
            ],
            [
                'id' => 901,
                'word' => 'nimphomania',
                'isolated_only' => false
            ],
            [
                'id' => 902,
                'word' => 'nipple',
                'isolated_only' => false
            ],
            [
                'id' => 903,
                'word' => 'nipples',
                'isolated_only' => false
            ],
            [
                'id' => 904,
                'word' => 'nsfw images',
                'isolated_only' => false
            ],
            [
                'id' => 905,
                'word' => 'nude',
                'isolated_only' => false
            ],
            [
                'id' => 906,
                'word' => 'nudity',
                'isolated_only' => false
            ],
            [
                'id' => 907,
                'word' => 'nympho',
                'isolated_only' => false
            ],
            [
                'id' => 908,
                'word' => 'nymphomania',
                'isolated_only' => false
            ],
            [
                'id' => 909,
                'word' => 'octopussy',
                'isolated_only' => false
            ],
            [
                'id' => 910,
                'word' => 'omorashi',
                'isolated_only' => false
            ],
            [
                'id' => 911,
                'word' => 'one cup two girls',
                'isolated_only' => false
            ],
            [
                'id' => 912,
                'word' => 'one guy one jar',
                'isolated_only' => false
            ],
            [
                'id' => 913,
                'word' => 'orgy',
                'isolated_only' => false
            ],
            [
                'id' => 914,
                'word' => 'paedophile',
                'isolated_only' => false
            ],
            [
                'id' => 915,
                'word' => 'panties',
                'isolated_only' => false
            ],
            [
                'id' => 916,
                'word' => 'panty',
                'isolated_only' => false
            ],
            [
                'id' => 917,
                'word' => 'pedobear',
                'isolated_only' => false
            ],
            [
                'id' => 918,
                'word' => 'pedophile',
                'isolated_only' => false
            ],
            [
                'id' => 919,
                'word' => 'pegging',
                'isolated_only' => false
            ],
            [
                'id' => 920,
                'word' => 'phone sex',
                'isolated_only' => false
            ],
            [
                'id' => 921,
                'word' => 'piece of shit',
                'isolated_only' => false
            ],
            [
                'id' => 922,
                'word' => 'piss pig',
                'isolated_only' => false
            ],
            [
                'id' => 923,
                'word' => 'pisspig',
                'isolated_only' => false
            ],
            [
                'id' => 924,
                'word' => 'playboy',
                'isolated_only' => false
            ],
            [
                'id' => 925,
                'word' => 'pleasure chest',
                'isolated_only' => false
            ],
            [
                'id' => 926,
                'word' => 'pole smoker',
                'isolated_only' => false
            ],
            [
                'id' => 927,
                'word' => 'ponyplay',
                'isolated_only' => false
            ],
            [
                'id' => 928,
                'word' => 'poof',
                'isolated_only' => false
            ],
            [
                'id' => 929,
                'word' => 'punany',
                'isolated_only' => false
            ],
            [
                'id' => 930,
                'word' => 'poop chute',
                'isolated_only' => false
            ],
            [
                'id' => 931,
                'word' => 'poopchute',
                'isolated_only' => false
            ],
            [
                'id' => 932,
                'word' => 'prince albert piercing',
                'isolated_only' => false
            ],
            [
                'id' => 933,
                'word' => 'pthc',
                'isolated_only' => false
            ],
            [
                'id' => 934,
                'word' => 'pubes',
                'isolated_only' => false
            ],
            [
                'id' => 935,
                'word' => 'queaf',
                'isolated_only' => false
            ],
            [
                'id' => 936,
                'word' => 'quim',
                'isolated_only' => false
            ],
            [
                'id' => 937,
                'word' => 'raghead',
                'isolated_only' => false
            ],
            [
                'id' => 938,
                'word' => 'raging boner',
                'isolated_only' => false
            ],
            [
                'id' => 939,
                'word' => 'rape',
                'isolated_only' => false
            ],
            [
                'id' => 940,
                'word' => 'raping',
                'isolated_only' => false
            ],
            [
                'id' => 941,
                'word' => 'rapist',
                'isolated_only' => false
            ],
            [
                'id' => 942,
                'word' => 'reverse cowgirl',
                'isolated_only' => false
            ],
            [
                'id' => 943,
                'word' => 'rosy palm',
                'isolated_only' => false
            ],
            [
                'id' => 944,
                'word' => 'rosy palm and her 5 sisters',
                'isolated_only' => false
            ],
            [
                'id' => 945,
                'word' => 'rusty trombone',
                'isolated_only' => false
            ],
            [
                'id' => 946,
                'word' => 'sadism',
                'isolated_only' => false
            ],
            [
                'id' => 947,
                'word' => 'santorum',
                'isolated_only' => false
            ],
            [
                'id' => 948,
                'word' => 'scat',
                'isolated_only' => false
            ],
            [
                'id' => 949,
                'word' => 'scissoring',
                'isolated_only' => false
            ],
            [
                'id' => 950,
                'word' => 'sexo',
                'isolated_only' => false
            ],
            [
                'id' => 951,
                'word' => 'sexy',
                'isolated_only' => false
            ],
            [
                'id' => 952,
                'word' => 'shaved beaver',
                'isolated_only' => false
            ],
            [
                'id' => 953,
                'word' => 'shaved pussy',
                'isolated_only' => false
            ],
            [
                'id' => 954,
                'word' => 'shibari',
                'isolated_only' => false
            ],
            [
                'id' => 955,
                'word' => 'shitblimp',
                'isolated_only' => false
            ],
            [
                'id' => 956,
                'word' => 'shota',
                'isolated_only' => false
            ],
            [
                'id' => 957,
                'word' => 'shrimping',
                'isolated_only' => false
            ],
            [
                'id' => 958,
                'word' => 'slanteye',
                'isolated_only' => false
            ],
            [
                'id' => 959,
                'word' => 's&m',
                'isolated_only' => false
            ],
            [
                'id' => 960,
                'word' => 'snowballing',
                'isolated_only' => false
            ],
            [
                'id' => 961,
                'word' => 'sodomize',
                'isolated_only' => false
            ],
            [
                'id' => 962,
                'word' => 'sodomy',
                'isolated_only' => false
            ],
            [
                'id' => 963,
                'word' => 'splooge moose',
                'isolated_only' => false
            ],
            [
                'id' => 964,
                'word' => 'spooge',
                'isolated_only' => false
            ],
            [
                'id' => 965,
                'word' => 'spread legs',
                'isolated_only' => false
            ],
            [
                'id' => 966,
                'word' => 'strap on',
                'isolated_only' => false
            ],
            [
                'id' => 967,
                'word' => 'strapon',
                'isolated_only' => false
            ],
            [
                'id' => 968,
                'word' => 'strappado',
                'isolated_only' => false
            ],
            [
                'id' => 969,
                'word' => 'strip club',
                'isolated_only' => false
            ],
            [
                'id' => 970,
                'word' => 'style doggy',
                'isolated_only' => false
            ],
            [
                'id' => 971,
                'word' => 'suck',
                'isolated_only' => false
            ],
            [
                'id' => 972,
                'word' => 'sucks',
                'isolated_only' => false
            ],
            [
                'id' => 973,
                'word' => 'suicide girls',
                'isolated_only' => false
            ],
            [
                'id' => 974,
                'word' => 'sultry women',
                'isolated_only' => false
            ],
            [
                'id' => 975,
                'word' => 'swastika',
                'isolated_only' => false
            ],
            [
                'id' => 976,
                'word' => 'swinger',
                'isolated_only' => false
            ],
            [
                'id' => 977,
                'word' => 'tainted love',
                'isolated_only' => false
            ],
            [
                'id' => 978,
                'word' => 'taste my',
                'isolated_only' => false
            ],
            [
                'id' => 979,
                'word' => 'tea bagging',
                'isolated_only' => false
            ],
            [
                'id' => 980,
                'word' => 'threesome',
                'isolated_only' => false
            ],
            [
                'id' => 981,
                'word' => 'throating',
                'isolated_only' => false
            ],
            [
                'id' => 982,
                'word' => 'tied up',
                'isolated_only' => false
            ],
            [
                'id' => 983,
                'word' => 'tight white',
                'isolated_only' => false
            ],
            [
                'id' => 984,
                'word' => 'titty',
                'isolated_only' => false
            ],
            [
                'id' => 985,
                'word' => 'tongue in a',
                'isolated_only' => false
            ],
            [
                'id' => 986,
                'word' => 'topless',
                'isolated_only' => false
            ],
            [
                'id' => 987,
                'word' => 'towelhead',
                'isolated_only' => false
            ],
            [
                'id' => 988,
                'word' => 'tranny',
                'isolated_only' => false
            ],
            [
                'id' => 989,
                'word' => 'tribadism',
                'isolated_only' => false
            ],
            [
                'id' => 990,
                'word' => 'tub girl',
                'isolated_only' => false
            ],
            [
                'id' => 991,
                'word' => 'tubgirl',
                'isolated_only' => false
            ],
            [
                'id' => 992,
                'word' => 'tushy',
                'isolated_only' => false
            ],
            [
                'id' => 993,
                'word' => 'twink',
                'isolated_only' => false
            ],
            [
                'id' => 994,
                'word' => 'twinkie',
                'isolated_only' => false
            ],
            [
                'id' => 995,
                'word' => 'two girls one cup',
                'isolated_only' => false
            ],
            [
                'id' => 996,
                'word' => 'undressing',
                'isolated_only' => false
            ],
            [
                'id' => 997,
                'word' => 'upskirt',
                'isolated_only' => false
            ],
            [
                'id' => 998,
                'word' => 'urethra play',
                'isolated_only' => false
            ],
            [
                'id' => 999,
                'word' => 'urophilia',
                'isolated_only' => false
            ],
            [
                'id' => 1000,
                'word' => 'venus mound',
                'isolated_only' => false
            ],
            [
                'id' => 1001,
                'word' => 'vibrator',
                'isolated_only' => false
            ],
            [
                'id' => 1002,
                'word' => 'violet wand',
                'isolated_only' => false
            ],
            [
                'id' => 1003,
                'word' => 'vorarephilia',
                'isolated_only' => false
            ],
            [
                'id' => 1004,
                'word' => 'voyeur',
                'isolated_only' => false
            ],
            [
                'id' => 1005,
                'word' => 'wet dream',
                'isolated_only' => false
            ],
            [
                'id' => 1006,
                'word' => 'white power',
                'isolated_only' => false
            ],
            [
                'id' => 1007,
                'word' => 'wrapping men',
                'isolated_only' => false
            ],
            [
                'id' => 1008,
                'word' => 'wrinkled starfish',
                'isolated_only' => false
            ],
            [
                'id' => 1009,
                'word' => 'xx',
                'isolated_only' => false
            ],
            [
                'id' => 1010,
                'word' => 'yaoi',
                'isolated_only' => false
            ],
            [
                'id' => 1011,
                'word' => 'yellow showers',
                'isolated_only' => false
            ],
            [
                'id' => 1012,
                'word' => 'yiffy',
                'isolated_only' => false
            ],
            [
                'id' => 1013,
                'word' => 'zoophilia',
                'isolated_only' => false
            ],
        ];
        parent::init();
    }
}
