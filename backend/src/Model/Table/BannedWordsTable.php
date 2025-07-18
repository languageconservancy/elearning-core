<?php

namespace App\Model\Table;

use App\Model\Entity\BannedWord;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BannedWords Model
 *
 * @method BannedWord get($primaryKey, $options = [])
 * @method BannedWord newEmptyEntity($data = null, array $options = [])
 * @method BannedWord[] newEntities(array $data, array $options = [])
 * @method BannedWord|bool save(EntityInterface $entity, $options = [])
 * @method BannedWord patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method BannedWord[] patchEntities($entities, array $data, array $options = [])
 * @method BannedWord findOrCreate($search, callable $callback = null, $options = [])
 */
class BannedWordsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('banned_words');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('word')
            ->maxLength('word', 255)
            ->requirePresence('word', 'create')
            ->notEmptyString('word');

        return $validator;
    }

    public function getList(): array
    {
        return $this->find()->toArray();
    }

    /**
     * Remove diacritics manually and get the rest with iconv.
     * The manual portion is because iconv doesn't work well in all environments.
     * This function expects $text to be all lowercase.
     * @param {string} $text - String to convert
     * @return false|string {string} Converted string
     */
    public function removeDiacritics($text)
    {
        $replacePairs = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ɨ' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'č' => 'c', 'ç' => 'c',
            'ǧ' => 'g',
            'ħ' => 'h', 'ɦ' => 'h', 'ɥ' => 'h', 'ɧ' => 'h', 'ȟ' => 'h',
            'ŋ' => 'n', 'ɲ' => 'n', 'ɳ' => 'n',
            'š' => 's',
            'ʔ' => '', 'ʕ' => ''
        ];

        $text = strtr($text, $replacePairs);
        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    }

    /**
     * Tells calling function whether string passed in contains banned words.
     * @param {string} $text - String to check for banned words.
     * @return True if banned words exist in the $text, false otherwise.
     */
    public function presentInText($text): bool
    {
        $textLowercase = mb_strtolower($text, 'UTF-8');
        $txtNoDiacritics = $this->removeDiacritics($textLowercase);
        $bannedWords = $this->getList();

        foreach ($bannedWords as $banned) {
            if ($banned['isolated_only']) {
                if (strpos($txtNoDiacritics, ' ' . $banned['word'] . ' ') !== false) {
                    return true;
                }
            } else {
                if (strpos($txtNoDiacritics, $banned['word']) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
