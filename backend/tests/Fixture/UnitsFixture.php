<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UnitsFixture
 *
 */
class UnitsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'type' => ['type' => 'tinyinteger', 'length' => 2, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'latin1_swedish_ci'
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
                'id' => 8,
                'name' => 'Unit 1',
                'description' => 'Introductory vocabulary.

Learn your first ten Lakota words.',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 9,
                'name' => 'Unit 2',
                'description' => 'Yes and no questions, yes and no answers (I)

Paul bought a car. David is trying to guess what it is like.',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 10,
                'name' => 'Unit 3',
                'description' => 'Yes and no questions (II)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 11,
                'name' => 'Unit 4',
                'description' => 'Šúŋka kiŋ sápa he? - Is the dog black?',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 12,
                'name' => 'Unit 5',
                'description' => 'Vocabulary review

Let’s review some of the vocabulary from the previous dialogue.
',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 13,
                'name' => 'Unit 6',
                'description' => 'Pronunciation I: Lakota oral vowels',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 14,
                'name' => 'Unit 7',
                'description' => 'Common greetings and phrases',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 15,
                'name' => 'Unit 8',
                'description' => 'Expressing gratitude',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 16,
                'name' => 'Unit 9',
                'description' => 'Clothes vocabulary',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 17,
                'name' => 'Unit 15',
                'description' => 'Some kitchen vocabulary',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 18,
                'name' => 'Unit 10',
                'description' => 'lé, hé, ká (Singular demonstrative pronouns)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 19,
                'name' => 'Unit 11',
                'description' => 'Pronunciation II: Lakota nasal vowels (aŋ, iŋ, uŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 20,
                'name' => 'Unit 12',
                'description' => 'lenáuŋs, henáuŋs, kanáuŋs (dual demonstrative pronouns)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 21,
                'name' => 'Unit 13',
                'description' => 'Pronunciation III. Word stress',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 22,
                'name' => 'Unit 14',
                'description' => 'Word stress - practice',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 23,
                'name' => 'Unit 16',
                'description' => 'Lená, hená, kaná (Plural demonstrative pronouns)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 24,
                'name' => 'Unit 17',
                'description' => 'Pronunciation IV: Unaspirated stops (k, p, t)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 25,
                'name' => 'Unit 18',
                'description' => 'Introducing the article kiŋ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 26,
                'name' => 'Unit 19',
                'description' => 'Pronunciation V: The Lakota letter ȟ [ȟé] - h with a wedge',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 27,
                'name' => 'Unit 20',
                'description' => 'Kiŋ with lená and introduction to reduplication of stative verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 28,
                'name' => 'Unit 21',
                'description' => 'Reduplication of stative verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 29,
                'name' => 'Unit 22',
                'description' => 'Review of reduplication and of šni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 30,
                'name' => 'Unit 23',
                'description' => 'Ablaut at the end of a sentence',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 31,
                'name' => 'Unit 24',
                'description' => 'Ablaut before šni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 32,
                'name' => 'Unit 25',
                'description' => 'Ablaut before ȟčA',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 33,
                'name' => 'Unit 26',
                'description' => 'Non-ablaut verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 34,
                'name' => 'Unit 27',
                'description' => '3rd person singular',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 36,
                'name' => 'Unit 28',
                'description' => '3rd singular verbs in sentences',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 37,
                'name' => 'Unit 29',
                'description' => '3rd person plural (animate)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 38,
                'name' => 'Unit 30',
                'description' => '3rd plural verbs in sentences',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 39,
                'name' => 'Unit 31',
                'description' => 'Animate plural (-pi) and inanimate plural (reduplication)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 40,
                'name' => 'Unit 32',
                'description' => 'Pronunciation VI: kȟ [kȟa], pȟ [pȟa], tȟ [tȟa] (velarized stops)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 41,
                'name' => 'Unit 33',
                'description' => 'A brief note about time and tense',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 42,
                'name' => 'Unit 34',
                'description' => 'Age categories (vocabulary)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 43,
                'name' => 'Unit 35',
                'description' => 'Review of 3rd singular and 3rd plural',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 44,
                'name' => 'Unit 36',
                'description' => 'Suffix -pi in questions',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 45,
                'name' => 'Unit 37',
                'description' => 'Suffix -pi with šni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 46,
                'name' => 'Unit 38',
                'description' => 'Pronunciation VII: kh [khi], ph [phi], th [thi] (aspirated stops)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 47,
                'name' => 'Unit 39',
                'description' => '1st person singular of “I” Class I verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 48,
                'name' => 'Unit 40',
                'description' => '2nd person singular or “you” in Class I verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 49,
                'name' => 'Unit 41',
                'description' => '2nd person singular and 1st person singular in Class I verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 50,
                'name' => 'Unit 42',
                'description' => 'Some common Class I verbs and their conjugation',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 51,
                'name' => 'Unit 43',
                'description' => 'What is your name? What is his name? What are their names?',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 52,
                'name' => 'Unit 44',
                'description' => 'Kinship IA: The nuclear family (What is your mother’s name?)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 53,
                'name' => 'Unit 45',
                'description' => 'Review of some common Class I verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 54,
                'name' => 'Unit 46',
                'description' => 'What is your mother’s name?',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 55,
                'name' => 'Unit 47',
                'description' => 'Where are you from?',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 56,
                'name' => 'Unit 48',
                'description' => 'Kinship IB: suffix -yA with kinship terms',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 57,
                'name' => 'Unit 49',
                'description' => 'Suffix -yA with kinship terms - review and addition',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 58,
                'name' => 'Unit 50',
                'description' => 'Kinship IC: atéwaye kiŋ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 59,
                'name' => 'Unit 51',
                'description' => 'Pronunciation VIII: The Lakota sound ǧ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 60,
                'name' => 'Unit 52',
                'description' => 'Object and unmarked object',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 61,
                'name' => 'Unit 53',
                'description' => 'The boy saw the girl (Subject - object word order)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 62,
                'name' => 'Unit 54',
                'description' => 'Who heard whom? (subject/object ambiguity)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 63,
                'name' => 'Unit 55',
                'description' => '“I” in Class II verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 64,
                'name' => 'Unit 56',
                'description' => '“You” in Class II verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 65,
                'name' => 'Unit 57',
                'description' => '“I” and “you” in Class II verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 66,
                'name' => 'Unit 58',
                'description' => 'waŋ and waŋží (indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 67,
                'name' => 'Unit 59',
                'description' => 'Waŋží and waŋžíni (indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 68,
                'name' => 'Unit 60',
                'description' => 'waŋ, waŋží and waŋžíni (overview of indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 69,
                'name' => 'Unit 61',
                'description' => 'When did you come? (hí, híŋhaŋni, haŋhépi, ȟtálehaŋ, aŋpéhaŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 70,
                'name' => 'Unit 62',
                'description' => 'Vocabulary review',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 71,
                'name' => 'Unit 63',
                'description' => 'Are you going to dance? (The word ktA)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 72,
                'name' => 'Unit 64',
                'description' => 'Ablaut words before ktA (iŋ ablaut)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 73,
                'name' => 'Unit 65',
                'description' => 'Tomorrow, later today, tonight (“when” in the future)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 74,
                'name' => 'Unit 66',
                'description' => 'Híŋhaŋni and híŋhaŋni kiŋháŋ (In the morning and tomorrow)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 75,
                'name' => 'Unit 67',
                'description' => 'Tóhaŋ and toháŋl',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 76,
                'name' => 'Unit 68',
                'description' => 'Pronunciation IX: č vs čh',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 77,
                'name' => 'Unit 69',
                'description' => '“I” in Class III verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 78,
                'name' => 'Unit 70',
                'description' => '“You” in Class III verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 79,
                'name' => 'Unit 71',
                'description' => 'Ečhúŋ, léčhuŋ, héčhuŋ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 80,
                'name' => 'Unit 72',
                'description' => 'The affix wičha-',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 81,
                'name' => 'Unit 73',
                'description' => 'The affix wičha- with affixes for “I” and “you”',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 82,
                'name' => 'Unit 74',
                'description' => 'Pronunciation X.: Glottal stops: č’, k’, p’, t’, ȟ’, s’, š’',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 83,
                'name' => 'Unit 75',
                'description' => 'You and I (1st dual)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 84,
                'name' => 'Unit 76',
                'description' => '“Will you dance with me?” (short dialogues with the 1st dual)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 85,
                'name' => 'Unit 77',
                'description' => 'Affix uŋ- (1st dual) before vowels',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 86,
                'name' => 'Unit 78',
                'description' => '1st dual in conversations',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 87,
                'name' => 'Unit 79',
                'description' => 'Review of verb conjugation',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 88,
                'name' => 'Unit 80',
                'description' => 'We (1st plural)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 89,
                'name' => 'Unit 81',
                'description' => 'We (1st plural) in conversations',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 90,
                'name' => 'Unit 82',
                'description' => 'You singular and you plural (2nd singular and 2nd plural)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 91,
                'name' => 'Unit 83',
                'description' => 'You singular and you plural',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 92,
                'name' => 'Unit 84',
                'description' => 'Cardinal numbers from 1 to 10',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 93,
                'name' => 'Unit 85',
                'description' => 'Cardinal numbers with the suffix -la',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 94,
                'name' => 'Unit 86',
                'description' => 'Cardinal numbers as verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 95,
                'name' => 'Unit 87',
                'description' => 'Cardinal numbers II. (reduplication)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 96,
                'name' => 'Unit 88',
                'description' => 'Talking about the weather',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 97,
                'name' => 'Unit 89',
                'description' => 'Four seasons',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 98,
                'name' => 'Unit 90',
                'description' => 'Vocab Unit 1',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 99,
                'name' => 'Unit 91',
                'description' => 'Talking about seasons I. (čháŋna, recurring/habitual events)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 100,
                'name' => 'Unit 92',
                'description' => 'Talking about seasons II. (last summer, blokétu k’uŋ héhaŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 101,
                'name' => 'Unit 93',
                'description' => 'Talking about seasons III. (last summer, blokéhaŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 102,
                'name' => 'Unit 94',
                'description' => 'Talking about seasons IV. (non-specific past, eháŋtu and eháŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 103,
                'name' => 'Unit 95',
                'description' => 'Talking about seasons V. (specific past indefinite event)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 104,
                'name' => 'Unit 96',
                'description' => 'Talking about seasons VI. (specific event in the future)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 105,
                'name' => 'Unit 97',
                'description' => 'Talking about seasons VII. (hypothetical event in the future)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 106,
                'name' => 'Unit 98',
                'description' => 'Talking about seasons VIII. (the following winter - íwaniyetu)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 107,
                'name' => 'Unit 99',
                'description' => 'Talking about seasons IX. (overview and review)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 108,
                'name' => 'Unit 100',
                'description' => 'When do you usually go to bed? (toháŋtu čháŋna šna)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 109,
                'name' => 'Unit 101',
                'description' => 'Giving commands with yo and ye',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 110,
                'name' => 'Unit 102',
                'description' => 'Giving commands with wo and we',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 111,
                'name' => 'Unit 103',
                'description' => 'Giving negative commands to one person',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 112,
                'name' => 'Unit 104',
                'description' => 'Giving negative commands to more than one person',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 113,
                'name' => 'Unit 105',
                'description' => 'Negative commands to one person and to more people',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 114,
                'name' => 'Unit 106',
                'description' => 'Giving commands to more than one person (positive)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 115,
                'name' => 'Unit 107',
                'description' => 'Review of plural command - positive and negative',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 116,
                'name' => 'Unit 108',
                'description' => 'Command with “na”',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 117,
                'name' => 'Unit 109',
                'description' => 'Giving commands, summary and overview',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 118,
                'name' => 'Unit 110',
                'description' => 'Some commands used at school',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 119,
                'name' => 'Unit 111',
                'description' => 'Talking about getting dressed',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 120,
                'name' => 'Unit 112',
                'description' => 'Táku and táku čha',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 121,
                'name' => 'Unit 113',
                'description' => 'ma- (1st singular object)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 122,
                'name' => 'Unit 114',
                'description' => 'ni- (2nd person object)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 123,
                'name' => 'Unit 115',
                'description' => 'ni- before k (plus a review of ma- and ni-)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 124,
                'name' => 'Unit 116',
                'description' => 'Vocab Unit 2',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 125,
                'name' => 'Unit 117',
                'description' => 'ma- with commands',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 126,
                'name' => 'Unit 118',
                'description' => 'Review of object affixes',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 127,
                'name' => 'Unit 119',
                'description' => 'He came the following day.',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 128,
                'name' => 'Unit 120',
                'description' => 'Waŋží and etáŋ (intro to plural indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 129,
                'name' => 'Unit 121',
                'description' => 'waŋ and eyá',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 130,
                'name' => 'Unit 122',
                'description' => 'etáŋ and eyá',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 131,
                'name' => 'Unit 123',
                'description' => 'Tákuni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 132,
                'name' => 'Unit 124',
                'description' => 'Introduction to possessive verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 133,
                'name' => 'Unit 125',
                'description' => 'Introduction to reflexive verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 134,
                'name' => 'Unit 1',
                'description' => 'Colors',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 135,
                'name' => 'Unit 2',
                'description' => 'It is not red - negation',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 136,
                'name' => 'Unit 3',
                'description' => 'Is it white? - questions',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 137,
                'name' => 'Unit 4',
                'description' => 'Is it white? - positive and negative answer',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 138,
                'name' => 'Unit 5',
                'description' => 'Colors - quiz',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 177,
                'name' => 'Unit 9',
                'description' => 'Pronunciation I: Lakota oral vowels',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 178,
                'name' => 'Unit 10',
                'description' => 'Expressing gratitude',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 179,
                'name' => 'Unit 11',
                'description' => 'Clothes vocabulary',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 180,
                'name' => 'Unit 12',
                'description' => 'lé, hé, ká (Singular demonstrative pronouns)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 181,
                'name' => 'Unit 13',
                'description' => 'Pronunciation II: Lakota nasal vowels (aŋ, iŋ, uŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 182,
                'name' => 'Unit 14',
                'description' => 'lenáuŋs, henáuŋs, kanáuŋs (dual demonstrative pronouns)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 183,
                'name' => 'Unit 15',
                'description' => 'Pronunciation III. Word stress',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 184,
                'name' => 'Unit 16',
                'description' => 'Word stress - practice',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 185,
                'name' => 'Unit 17',
                'description' => 'Some kitchen vocabulary',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 186,
                'name' => 'Unit 18',
                'description' => 'Lená, hená, kaná (Plural demonstrative pronouns)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 187,
                'name' => 'Unit 19',
                'description' => 'Pronunciation IV: Unaspirated stops (k, p, t)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 188,
                'name' => 'Unit 20',
                'description' => 'Introducing the article kiŋ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 189,
                'name' => 'Unit 21',
                'description' => 'Pronunciation V: The Lakota letter ȟ [ȟé] - h with a wedge',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 190,
                'name' => 'Unit 22',
                'description' => 'Kiŋ with lená and introduction to reduplication of stative verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 191,
                'name' => 'Unit 23',
                'description' => 'Reduplication of stative verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 192,
                'name' => 'Unit 24',
                'description' => 'Review of reduplication and of šni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 193,
                'name' => 'Unit 25',
                'description' => 'Ablaut at the end of a sentence',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 194,
                'name' => 'Unit 26',
                'description' => 'Ablaut before šni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 195,
                'name' => 'Unit 27',
                'description' => 'Ablaut before ȟčA',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 196,
                'name' => 'Unit 28',
                'description' => 'Non-ablaut verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 197,
                'name' => 'Unit 29',
                'description' => '3rd person singular',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 198,
                'name' => 'Unit 30',
                'description' => '3rd singular verbs in sentences',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 199,
                'name' => 'Unit 31',
                'description' => '3rd person plural (animate)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 200,
                'name' => 'Unit 32',
                'description' => '3rd plural verbs in sentences',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 201,
                'name' => 'Unit 33',
                'description' => 'Animate plural (-pi) and inanimate plural (reduplication)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 202,
                'name' => 'Unit 34',
                'description' => 'Pronunciation VI: kȟ [kȟa], pȟ [pȟa], tȟ [tȟa] (velarized stops)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 203,
                'name' => 'Unit 35',
                'description' => 'A brief note about time and tense',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 204,
                'name' => 'Unit 36',
                'description' => 'Age categories (vocabulary)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 205,
                'name' => 'Unit 37',
                'description' => 'Review of 3rd singular and 3rd plural',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 206,
                'name' => 'Unit 38',
                'description' => 'Suffix -pi in questions',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 207,
                'name' => 'Unit 39',
                'description' => 'Suffix -pi with šni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 208,
                'name' => 'Unit 40',
                'description' => 'Pronunciation VII: kh [khi], ph [phi], th [thi] (aspirated stops)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 209,
                'name' => 'Unit 41',
                'description' => '1st person singular or “I” in Class I verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 210,
                'name' => 'Unit 42',
                'description' => '2nd person singular or “you” in Class I verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 211,
                'name' => 'Unit 43',
                'description' => '2nd person singular and 1st person singular in Class I verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 212,
                'name' => 'Unit 44',
                'description' => 'Some common Class I verbs and their conjugation',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 213,
                'name' => 'Unit 45',
                'description' => 'What is your name? What is his name? What are their names?',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 214,
                'name' => 'Unit 46',
                'description' => 'Kinship IA: The nuclear family (What is your mother’s name?)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 215,
                'name' => 'Unit 47',
                'description' => 'Review of some common Class I verbs.',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 216,
                'name' => 'Unit 48',
                'description' => 'Common greetings and expressions',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 217,
                'name' => 'Unit 49',
                'description' => 'What is your mother’s name?',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 218,
                'name' => 'Unit 50',
                'description' => 'Where are you from?',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 219,
                'name' => 'Unit 51',
                'description' => 'Kinship IB: suffix -yA with kinship terms',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 220,
                'name' => 'Unit 52',
                'description' => 'Suffix -yA with kinship terms - review and addition',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 221,
                'name' => 'Unit 53',
                'description' => 'Kinship IC: atéwaye kiŋ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 222,
                'name' => 'Unit 54',
                'description' => 'Pronunciation VIII: The Lakota sound ǧ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 224,
                'name' => 'unit one',
                'description' => 'unit one',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 226,
                'name' => 'Unit 55',
                'description' => 'Object and unmarked object',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 227,
                'name' => 'Unit 56',
                'description' => 'The boy saw the girl (Subject - object word order)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 229,
                'name' => 'Unit 57',
                'description' => 'Who heard whom? (subject/object ambiguity)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 230,
                'name' => 'Unit 58',
                'description' => '“I” in Class II verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 231,
                'name' => 'Unit 59',
                'description' => '“You” in Class II verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 232,
                'name' => 'Unit 60',
                'description' => '“I” and “you” in Class II verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 233,
                'name' => 'Unit 61',
                'description' => 'waŋ and waŋží (indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 234,
                'name' => 'Unit 62',
                'description' => 'waŋží and waŋžíni (indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 235,
                'name' => 'Unit 63',
                'description' => 'waŋ, waŋží and waŋžíni (overview of indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 236,
                'name' => 'Unit 64',
                'description' => 'When did you come? (hí, híŋhaŋni, haŋhépi, ȟtálehaŋ, aŋpéhaŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 237,
                'name' => 'Unit 65',
                'description' => 'Vocabulary review',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 238,
                'name' => 'Unit 66',
                'description' => 'Are you going to dance? (The word ktA)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 239,
                'name' => 'Unit 67',
                'description' => 'Ablaut words before ktA (iŋ ablaut)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 240,
                'name' => 'Unit 68',
                'description' => 'Tomorrow, later today, tonight (“when” in the future)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 241,
                'name' => 'Unit 69',
                'description' => 'Híŋhaŋni and híŋhaŋni kiŋháŋ (In the morning and tomorrow)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 242,
                'name' => 'Unit 70',
                'description' => 'Tóhaŋ and toháŋl',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 243,
                'name' => 'Unit 71',
                'description' => 'Pronunciation IX: č vs čh',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 244,
                'name' => 'Unit 72',
                'description' => '“I” in Class III verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 245,
                'name' => 'Unit 73',
                'description' => '“You” in Class III verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 246,
                'name' => 'Unit 74',
                'description' => 'Ečhúŋ, léčhuŋ, héčhuŋ',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 247,
                'name' => 'Unit 75',
                'description' => 'The affix wičha-',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 248,
                'name' => 'Unit 76',
                'description' => 'The affix wičha- with affixes for “I” and “you”',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 249,
                'name' => 'Unit 77',
                'description' => 'Pronunciation X.: Glottal stops: č’, k’, p’, t’, ȟ’, s’, š’',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 253,
                'name' => 'Unit 6',
                'description' => 'Animals',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 254,
                'name' => 'Unit 7',
                'description' => 'Animals - quiz',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 255,
                'name' => 'Unit 8',
                'description' => 'Basic vocabulary',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 256,
                'name' => 'Unit 78',
                'description' => 'You and I (1st dual)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 257,
                'name' => 'Unit 79',
                'description' => '1st dual - practice',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 258,
                'name' => 'Unit 80',
                'description' => 'Affix uŋ- (1st dual) before vowels',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 259,
                'name' => 'Unit 81',
                'description' => '1st dual with uŋk- - practice',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 260,
                'name' => 'Unit 82',
                'description' => 'Review of verb conjugation',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 261,
                'name' => 'Unit 83',
                'description' => 'We (1st plural)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 262,
                'name' => 'Unit 84',
                'description' => 'We (1st plural) in conversations',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 263,
                'name' => 'Unit 85',
                'description' => 'You singular and you plural (2nd singular and 2nd plural)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 264,
                'name' => 'Unit 86',
                'description' => 'You singular and you plural',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 265,
                'name' => 'Unit 87',
                'description' => 'Cardinal numbers from 1 to 10',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 266,
                'name' => 'Unit 88',
                'description' => 'Cardinal numbers with the suffix -la',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 267,
                'name' => 'Unit 89',
                'description' => 'Cardinal numbers as verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 268,
                'name' => 'Unit 90',
                'description' => 'Cardinal numbers II. (reduplication)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 269,
                'name' => 'Unit 91',
                'description' => 'Talking about the weather',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 270,
                'name' => 'Unit 92',
                'description' => 'Four seasons',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 271,
                'name' => 'Unit 93',
                'description' => 'Additional vocabulary 1',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 272,
                'name' => 'Unit 94',
                'description' => 'Talking about seasons I. (čháŋna, recurring/habitual events)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 273,
                'name' => 'Unit 95',
                'description' => 'Talking about seasons II. (last summer, blokétu k’uŋ héhaŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 274,
                'name' => 'Unit 96',
                'description' => 'Talking about seasons III. (last summer, blokéhaŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 275,
                'name' => 'Unit 97',
                'description' => 'Talking about seasons IV. (non-specific past, eháŋtu and eháŋ)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 276,
                'name' => 'Unit 98',
                'description' => 'Talking about seasons V. (specific past indefinite event)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 277,
                'name' => 'Unit 99',
                'description' => 'Talking about seasons VI. (specific event in the future)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 278,
                'name' => 'Unit 100',
                'description' => 'Talking about seasons VII. (hypothetical event in the future)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 279,
                'name' => 'Unit 101',
                'description' => 'Talking about seasons VIII. (the following winter - íwaniyetu)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 280,
                'name' => 'Unit 102',
                'description' => 'Talking about seasons IX. (overview and review)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 281,
                'name' => 'Unit 103',
                'description' => 'When do you usually go to bed? (toháŋtu čháŋna šna)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 282,
                'name' => 'Unit 104',
                'description' => 'Giving commands with yo and ye',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 283,
                'name' => 'Unit 105',
                'description' => 'Giving commands with wo and we',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 284,
                'name' => 'Unit 106',
                'description' => 'Giving negative commands to one person',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 285,
                'name' => 'Unit 107',
                'description' => 'Giving negative commands to more than one person',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 286,
                'name' => 'Unit 108',
                'description' => 'Negative commands to one person and to more people - practice',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 287,
                'name' => 'Unit 109',
                'description' => 'Giving commands to more than one person (positive)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 288,
                'name' => 'Unit 110',
                'description' => 'Review of plural command - positive and negative',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 289,
                'name' => 'Unit 111',
                'description' => 'Command with “na”',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 290,
                'name' => 'Unit 112',
                'description' => 'Giving commands, summary and overview',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 291,
                'name' => 'Unit 113',
                'description' => 'Some commands used at school',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 292,
                'name' => 'Unit 114',
                'description' => 'Talking about getting dressed',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 293,
                'name' => 'Unit 115',
                'description' => 'Táku and táku čha',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 294,
                'name' => 'Unit 116',
                'description' => 'ma- (1st singular object)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 295,
                'name' => 'Unit 117',
                'description' => 'ni- (2nd person object)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 296,
                'name' => 'Unit 118',
                'description' => 'ni- before k (plus a review of ma- and ni-)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 297,
                'name' => 'Unit 119',
                'description' => 'Additional vocabulary 2',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 298,
                'name' => 'Unit 120',
                'description' => 'ma- with commands',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 299,
                'name' => 'Unit 121',
                'description' => 'Review of object affixes',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 300,
                'name' => 'Unit 122',
                'description' => 'He came the following day.',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 301,
                'name' => 'Unit 123',
                'description' => 'Waŋží and etáŋ (intro to plural indefinite articles)',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 302,
                'name' => 'Unit 124',
                'description' => 'waŋ and eyá',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 303,
                'name' => 'Unit 125',
                'description' => 'etáŋ and eyá',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 304,
                'name' => 'Unit 126',
                'description' => 'Tákuni',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 305,
                'name' => 'Unit 127',
                'description' => 'Introduction to possessive verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
            [
                'id' => 306,
                'name' => 'Unit 128',
                'description' => 'Introduction to reflexive verbs',
                'type' => null,
                'created' => null,
                'modified' => null
            ],
        ];
        parent::init();
    }
}
