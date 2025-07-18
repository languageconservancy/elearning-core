<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ActivityType Entity
 *
 * @property int $id
 * @property int $type
 * @property string $specific_skill
 * @property string $global_skill
 * @property string $prompt_response_pairs_words
 * @property string $prompt_response_pairs_patterns
 * @property string $exercise_type_words
 * @property string $exercise_type_patterns
 * @property int $learning_percentage_words
 * @property int $learning_percentage_patterns
 * @property int $review_percentage_words
 * @property int $review_percentage_patterns
 * @property string $learning_style
 * @property string $exclude_words
 * @property string $exclude_patterns
 */
class ActivityType extends Entity
{
    /**
     * Fields that can be mass assigned using newEmptyEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'type' => true,
        'specific_skill' => true,
        'global_skill' => true,
        'prompt_response_pairs_words' => true,
        'prompt_response_pairs_patterns' => true,
        'exercise_type_words' => true,
        'exercise_type_patterns' => true,
        'learning_percentage_words' => true,
        'learning_percentage_patterns' => true,
        'review_percentage_words' => true,
        'review_percentage_patterns' => true,
        'learning_style' => true,
        'exclude_words' => true,
        'exclude_patterns' => true
    ];

    protected function _getIsWordActivity(): bool
    {
        return isset($this->prompt_response_pairs_words);
    }

    protected function _getIsPatternActivity(): bool
    {
        return isset($this->prompt_response_pairs_patterns);
    }

    protected function _getCardTypeStr(): string
    {
        if ($this->get('is_word_activity')) {
            return 'word';
        } elseif ($this->get('is_pattern_activity')) {
            return 'pattern';
        } else {
            return 'n/a';
        }
    }
}
