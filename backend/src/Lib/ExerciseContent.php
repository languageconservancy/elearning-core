<?php

namespace App\Lib;

use InvalidArgumentException;

class ExerciseContent
{
    public const TYPES = [
        'multiple-choice' => 'Multiple choice', 'match-the-pair' => 'Match pairs',
        'truefalse' => 'True / false', 'anagram' => 'Anagram',
        'fill_in_the_blanks' => 'Fill in the blanks', 'recording' => 'Recording',
        'vocab-builder' => 'Vocabulary builder',
    ];
    public const MODALITIES = [
        'a' => 'Audio', 'e' => 'English', 'i' => 'Image',
        'l' => 'Language text', 'r' => 'Recording', 'v' => 'Video',
    ];
    public const SOURCES = [
        'multiple-choice' => ['card', 'card_group', 'custom'],
        'match-the-pair' => ['card', 'card_group', 'custom'],
        'truefalse' => ['card', 'card_group', 'custom'],
        'anagram' => ['card', 'card_group'],
        'fill_in_the_blanks' => ['card', 'custom'],
        'recording' => ['card', 'card_group', 'custom'],
        'vocab-builder' => ['card'],
    ];
    /** Content roles per exercise type and source: role => [label, minimum, maximum or null]. */
    public const ROLES = [
        'multiple-choice' => [
            'card' => ['P' => ['Prompt', 1, 1], 'R' => ['Correct answer', 1, 1], 'O' => ['Distractor', 1, null]],
            'card_group' => ['P' => ['Prompt group', 1, null], 'O' => ['Answer group', 1, null]],
            'custom' => ['P' => ['Prompt', 1, 1], 'O' => ['Answer option', 1, 3]],
        ],
        'match-the-pair' => [
            'card' => ['O' => ['Pair', 1, 6]],
            'card_group' => ['O' => ['Pair group', 1, null]],
            'custom' => ['O' => ['Pair', 1, 6]],
        ],
        'truefalse' => [
            'card' => ['O' => ['Statement', 1, 1]],
            'card_group' => ['P' => ['Card', 1, null]],
            'custom' => ['O' => ['Statement', 1, 1]],
        ],
        'anagram' => [
            'card' => ['O' => ['Word', 1, 1]],
            'card_group' => ['P' => ['Word', 1, null]],
        ],
        'fill_in_the_blanks' => [
            'card' => ['P' => ['Sentence', 1, 1], 'O' => ['Answer choice', 0, 8]],
            'custom' => ['P' => ['Sentence', 1, 1], 'O' => ['Answer choice', 0, 8]],
        ],
        'recording' => [
            'card' => ['P' => ['Prompt', 1, 1]],
            'card_group' => ['P' => ['Card', 1, null]],
            'custom' => ['P' => ['Prompt', 1, 1]],
        ],
        'vocab-builder' => [
            'card' => ['P' => ['Target', 1, 1], 'O' => ['Other response', 0, null]],
        ],
    ];
    /** Modalities that may be both the prompt and the response. */
    public const SELF_PAIRS = ['a', 'e', 'l'];

    /**
     * Setup rules the editor uses to offer only valid choices; validateOptions enforces the same rules.
     */
    public static function rules(): array
    {
        $rules = [];
        foreach (self::ROLES as $type => $sources) {
            foreach ($sources as $source => $roles) {
                $pairs = [];
                foreach (array_keys(self::MODALITIES) as $prompt) {
                    foreach (array_keys(self::MODALITIES) as $response) {
                        if (self::pairProblem($type, $source, "$prompt-$response") === null) {
                            $pairs[] = "$prompt-$response";
                        }
                    }
                }
                $rules[$type][$source] = [
                    'roles' => array_map(
                        fn($role, $config) => ['value' => $role, 'label' => $config[0], 'min' => $config[1], 'max' => $config[2]],
                        array_keys($roles),
                        $roles
                    ),
                    'pairs' => $pairs,
                    'roundSize' => self::roundSize($type, $source),
                ];
            }
        }
        return $rules;
    }

    /** 'required' when the author sets cards per round, 'derived' when it follows the content, otherwise null. */
    public static function roundSize(string $type, string $source): ?string
    {
        if ($source === 'card_group') {
            return 'required';
        }
        return $type === 'match-the-pair' ? 'derived' : null;
    }

    public static function pairProblem(string $type, string $source, string $pair): ?string
    {
        $parts = explode('-', $pair);
        if (count($parts) !== 2 || !isset(self::MODALITIES[$parts[0]], self::MODALITIES[$parts[1]]) || $parts[0] === 'r') {
            return 'Select a valid prompt and response pair.';
        }
        [$prompt, $response] = $parts;
        if (($response === 'r') !== ($type === 'recording')) {
            return $type === 'recording'
                ? 'Recording exercises need a Recording response.'
                : 'A Recording response is only available for recording exercises.';
        }
        if ($prompt === $response) {
            if (!in_array($prompt, self::SELF_PAIRS, true)) {
                return 'The prompt and response cannot both be ' . self::MODALITIES[$prompt] . '.';
            }
            if ($type === 'anagram') {
                return 'Anagrams need different prompt and response types.';
            }
            if ($source === 'card_group' && in_array($type, ['multiple-choice', 'match-the-pair'], true)) {
                return 'Card groups need different prompt and response types for this exercise.';
            }
        }
        return null;
    }

    public static function validateForm(array $data): void
    {
        if (trim((string)($data['name'] ?? '')) === '') {
            throw new InvalidArgumentException('Please enter a valid name.');
        }
        if (($data['formtype'] ?? '') === 'namevalue') {
            return;
        }
        if (empty($data['card_type'])) {
            throw new InvalidArgumentException('Please select card type.');
        }
        if (empty($data['promteresponsetype'])) {
            throw new InvalidArgumentException('Please select prompt and response type.');
        }
        if (empty($data['exercise_type'])) {
            throw new InvalidArgumentException('Please select exercise type.');
        }
    }

    public static function validateOptions(array $exercise, array $options): void
    {
        self::validateForm($exercise);
        if (!isset(self::TYPES[$exercise['exercise_type']])) {
            throw new InvalidArgumentException('Select a supported exercise type.');
        }
        if (!in_array($exercise['card_type'], ['card', 'card_group', 'custom'], true)) {
            throw new InvalidArgumentException('Select cards, card groups, or custom content.');
        }
        if (!in_array($exercise['card_type'], self::SOURCES[$exercise['exercise_type']], true)) {
            throw new InvalidArgumentException('This content source is unavailable for the selected exercise type.');
        }
        $type = $exercise['exercise_type'];
        $source = $exercise['card_type'];
        $problem = self::pairProblem($type, $source, (string)$exercise['promteresponsetype']);
        if ($problem !== null) {
            throw new InvalidArgumentException($problem);
        }
        if (!$options) {
            throw new InvalidArgumentException('Add exercise content before saving.');
        }
        $hasRoundSize = isset($exercise['noofcard']) && $exercise['noofcard'] !== '';
        if (
            $hasRoundSize
            && filter_var($exercise['noofcard'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false
        ) {
            throw new InvalidArgumentException('Cards per round must be a positive whole number.');
        }
        if (self::roundSize($type, $source) === 'required' && !$hasRoundSize) {
            throw new InvalidArgumentException('Enter how many cards to use per round.');
        }
        $roles = self::ROLES[$type][$source];
        $counts = array_fill_keys(array_keys($roles), 0);
        foreach ($options as $index => $option) {
            $role = is_array($option) ? ($option['card_type'] ?? '') : '';
            if (!isset($roles[$role])) {
                throw new InvalidArgumentException('Content item ' . ($index + 1) . ' has a role this exercise does not use.');
            }
            $counts[$role]++;
        }
        foreach ($roles as $role => [$label, $min, $max]) {
            if ($counts[$role] < $min) {
                throw new InvalidArgumentException("Add at least $min " . strtolower($label) . ($min === 1 ? '' : 's') . '.');
            }
            if ($max !== null && $counts[$role] > $max) {
                throw new InvalidArgumentException("Use no more than $max " . strtolower($label) . ($max === 1 ? '' : 's') . '.');
            }
        }
        if ($type === 'fill_in_the_blanks') {
            if ($source === 'card' && !in_array('l', array_map('trim', explode(',', (string)($exercise['responsetype'] ?? ''))), true)) {
                throw new InvalidArgumentException('Fill-in-the-blank responses must show the language text.');
            }
            foreach ($options as $option) {
                if ($option['card_type'] === 'P' && !preg_match('/\[[^\]]+\]/', (string)($option['text_option'] ?? ''))) {
                    throw new InvalidArgumentException('Mark at least one blank in the sentence with [brackets].');
                }
            }
        }
        foreach (['promotetype', 'responsetype'] as $field) {
            self::validateDisplay($exercise[$field] ?? null);
        }
        foreach ($options as $index => $option) {
            if (!is_array($option) || !in_array($option['card_type'] ?? '', ['P', 'R', 'O'], true)) {
                throw new InvalidArgumentException('Each content item needs a valid role.');
            }
            $custom = $option['custom'] ?? [];
            foreach (['prompt_preview_option', 'responce_preview_option'] as $field) {
                self::validateDisplay($option[$field] ?? null);
            }
            if (isset($option['response_true_false']) && !in_array($option['response_true_false'], ['Y', 'N'], true)) {
                throw new InvalidArgumentException('The correct result must be Yes or No.');
            }
            if (isset($option['fill_in_the_blank_type']) && !in_array($option['fill_in_the_blank_type'], ['typing', 'mcq'], true)) {
                throw new InvalidArgumentException('Select typing or multiple choice for blank answers.');
            }
            if (
                isset($option['option_position'])
                && filter_var($option['option_position'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false
            ) {
                throw new InvalidArgumentException('Correct blank positions must be positive whole numbers.');
            }
            if (
                empty($option['card_id']) && empty($option['responce_card_id'])
                && empty($option['group_id']) && trim((string)($option['text_option'] ?? '')) === ''
                && empty($custom['prompt_html']) && empty($custom['response_html'])
                && empty($custom['prompt_audio_id']) && empty($custom['prompt_image_id'])
                && empty($custom['response_audio_id']) && empty($custom['response_image_id'])
                && empty($option['vbl_letter'])
            ) {
                throw new InvalidArgumentException('Content item ' . ($index + 1) . ' needs a card, group, text, or media.');
            }
        }
    }

    private static function validateDisplay($value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        if (!is_string($value) || array_diff(array_map('trim', explode(',', $value)), array_keys(self::MODALITIES))) {
            throw new InvalidArgumentException('Select supported display fields.');
        }
    }
}
