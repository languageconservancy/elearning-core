<?php

declare(strict_types=1);

namespace App\Lib;

use Cake\ORM\TableRegistry;
use InvalidArgumentException;

class VocabBuilder
{
    public const TYPE = 'vocab-builder';

    public const ACTIVITIES = [
        'bird-activity' => ['label' => 'Bird', 'letters' => 1, 'words' => 2, 'review' => true],
        'coloring' => ['label' => 'Coloring', 'letters' => 1, 'words' => 1, 'review' => true],
        'fill-letter' => ['label' => 'Fill Letter', 'letters' => 3, 'words' => 1, 'review' => true],
        'match-sound' => ['label' => 'Match Sound', 'letters' => 3, 'words' => 3, 'review' => true],
        'memory-activity' => ['label' => 'Memory', 'letters' => 8, 'words' => 1, 'review' => true],
        'select-grid' => ['label' => 'Select Grid', 'letters' => 2, 'words' => 1, 'review' => true],
        'word-list' => ['label' => 'Word List', 'letters' => 1, 'words' => 1, 'review' => false],
    ];

    public static function labels(): array
    {
        return array_map(static function ($activity) {
            return $activity['label'];
        }, self::ACTIVITIES);
    }

    public static function modes(string $activity): array
    {
        switch ($activity) {
            case 'coloring':
            case 'match-sound':
                return ['word' => 'Word', 'letter' => 'Letter'];
            case 'memory-activity':
            case 'select-grid':
                return ['letter' => 'Letter'];
            default:
                return ['word' => 'Word'];
        }
    }

    public static function mode(array $exercise): string
    {
        $activity = $exercise['vbl_activity'] ?? '';
        if (!isset(self::ACTIVITIES[$activity])) {
            throw new InvalidArgumentException('Select a supported vocab builder activity.');
        }
        $modes = self::modes($activity);
        $mode = $exercise['vbl_mode'] ?? array_key_first($modes);
        if (!isset($modes[$mode])) {
            throw new InvalidArgumentException('Select a supported word or letter version for this activity.');
        }
        return $mode;
    }

    public function normalizeOptions(array $rows, array $exercise = []): array
    {
        $mode = $exercise ? self::mode($exercise) : null;
        $activity = $exercise['vbl_activity'] ?? '';
        $options = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new InvalidArgumentException('Invalid vocab builder content row.');
            }
            $needsWord = $mode !== 'letter' && ($activity !== 'fill-letter' || !empty($row['target']));
            $needsLetter = $mode === null || $mode === 'letter' || $activity === 'fill-letter';
            $cardId = $needsWord ? $this->positiveId($row['card_id'] ?? null) : null;
            $letter = trim((string)($row['vbl_letter'] ?? ''));
            if ($needsLetter && ($letter === '' || $letter === '*' || mb_strlen($letter) > 100)) {
                throw new InvalidArgumentException('Each letter response needs a letter (not the random "*" letter).');
            }
            $option = [
                'card_id' => $cardId,
                'type' => 'card',
                'card_type' => !empty($row['target']) ? 'P' : 'O',
                'vbl_letter' => $needsLetter ? $letter : null,
                'text_option' => ($activity === 'fill-letter' && !empty($row['target'])) || $mode === null
                    ? trim((string)($row['text_option'] ?? '')) : '',
            ];
            foreach (['vbl_letter_audio_id', 'vbl_letter_image_id', 'vbl_coloring_image_id'] as $field) {
                $needed = $mode === null
                    || ($field === 'vbl_letter_audio_id' && $needsLetter)
                    || ($field === 'vbl_letter_image_id' && $activity === 'coloring' && $mode === 'letter')
                    || ($field === 'vbl_coloring_image_id' && $activity === 'coloring' && $mode === 'word');
                $option[$field] = !$needed || empty($row[$field]) ? null : $this->positiveId($row[$field]);
            }
            $options[] = $option;
        }
        if (
            count(array_filter($options, static function ($option) {
                return $option['card_type'] === 'P';
            })) !== 1
        ) {
            throw new InvalidArgumentException('Select exactly one target response.');
        }
        return $options;
    }

    private function positiveId($value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('Card and attachment IDs must be positive integers.');
        }
        return $id;
    }

    private function fileUrl($id, string $type): string
    {
        if (empty($id)) {
            return '';
        }
        $file = TableRegistry::getTableLocator()->get('Files')->get($id);
        if ($file->type !== $type || !$file->FullUrl) {
            throw new InvalidArgumentException("Attachment {$id} must be a usable {$type} file.");
        }
        return $file->FullUrl;
    }

    public function payload(array $exercise, array $options, ?int $targetCardId = null): array
    {
        $activity = $exercise['vbl_activity'] ?? '';
        $mode = self::mode($exercise);
        $needsLetters = $mode === 'letter' || $activity === 'fill-letter';
        $letters = [];
        $cardIds = [];
        $target = null;
        foreach ($options as $option) {
            $isTarget = $targetCardId !== null
                ? (int)($option['card_id'] ?? 0) === $targetCardId
                : ($option['card_type'] ?? '') === 'P';
            $needsWord = $mode === 'word' && ($activity !== 'fill-letter' || $isTarget);
            $cardId = empty($option['card_id']) ? null : $this->positiveId($option['card_id']);
            if ($needsWord && $cardId === null) {
                throw new InvalidArgumentException('Each word response needs a word card.');
            }
            if ($cardId !== null && in_array($cardId, $cardIds, true)) {
                throw new InvalidArgumentException('Attach each word card only once.');
            }
            $card = $cardId === null ? ['id' => null]
                : TableRegistry::getTableLocator()->get('Card')->get($cardId)->toArray();
            if ($cardId !== null) {
                $cardIds[] = $cardId;
            }
            $letter = $needsLetters ? trim((string)($option['vbl_letter'] ?? '')) : 'Words';
            if ($letter === '' || $letter === '*' || mb_strlen($letter) > 100) {
                throw new InvalidArgumentException('Each letter response needs a valid letter.');
            }
            $wordText = trim(html_entity_decode(
                strip_tags((string)($card['lakota'] ?? '')),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ));
            $markedWord = trim((string)($option['text_option'] ?? ''));
            if ($needsWord && $markedWord !== '' && str_replace(['[', ']'], '', $markedWord) !== $wordText) {
                throw new InvalidArgumentException("Marked word for card {$cardId} must match its language text.");
            }
            if ($activity === 'fill-letter' && $needsWord) {
                if ($markedWord === '') {
                    $position = mb_strpos($wordText, $letter);
                    if ($position === false) {
                        throw new InvalidArgumentException("Card {$cardId} must contain its letter for Fill Letter.");
                    }
                    $markedWord = mb_substr($wordText, 0, $position) . '[' . $letter . ']'
                        . mb_substr($wordText, $position + mb_strlen($letter));
                }
                if (
                    !preg_match('/^[^\[\]]*\[([^\[\]]+)\][^\[\]]*$/u', $markedWord, $match)
                    || $match[1] !== $letter
                ) {
                    throw new InvalidArgumentException(
                        "Card {$cardId} must mark exactly its target letter with brackets."
                    );
                }
            }
            $word = null;
            if ($needsWord) {
                $audio = [];
                foreach (array_filter(explode(',', (string)$card['audio'])) as $audioId) {
                    $audio[] = $this->fileUrl(trim($audioId), 'audio');
                }
                $needsImage = !in_array($activity, ['match-sound', 'coloring'], true);
                $image = $needsImage ? $this->fileUrl($card['image_id'], 'image') : '';
                if ($wordText === '' || !$audio || ($needsImage && $image === '')) {
                    throw new InvalidArgumentException(
                        "Word card {$cardId} needs language text, audio" . ($needsImage ? ' and an image.' : '.')
                    );
                }
                $word = [
                    'word' => $activity === 'fill-letter' ? $markedWord : $wordText,
                    'eng' => html_entity_decode(strip_tags((string)$card['english']), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'mp3' => $audio,
                    'img' => $image,
                    'coloring' => $activity === 'coloring'
                        ? $this->fileUrl($option['vbl_coloring_image_id'] ?? null, 'image') : '',
                ];
            }
            $letterAudio = $needsLetters ? $this->fileUrl($option['vbl_letter_audio_id'] ?? null, 'audio') : '';
            $letterImage = $activity === 'coloring' && $mode === 'letter'
                ? $this->fileUrl($option['vbl_letter_image_id'] ?? null, 'image') : '';
            if ($needsLetters && $letterAudio === '') {
                throw new InvalidArgumentException("Letter {$letter} needs a letter audio attachment.");
            }
            if (
                isset($letters[$letter])
                && ($letters[$letter]['mp3'] !== $letterAudio || $letters[$letter]['img'] !== $letterImage)
            ) {
                throw new InvalidArgumentException("Use the same letter attachments for every {$letter} word.");
            }
            if (!isset($letters[$letter])) {
                $letters[$letter] = ['letter' => $letter, 'mp3' => $letterAudio, 'img' => $letterImage, 'words' => []];
            }
            if ($word !== null) {
                $letters[$letter]['words'][] = $word;
            }
            $card['exerciseOptions'] = $option;
            $entry = [
                'card' => $card, 'letter' => $letter, 'word' => $word,
                'word_index' => $word === null ? null : count($letters[$letter]['words']) - 1,
            ];
            if ($isTarget) {
                if ($target !== null) {
                    throw new InvalidArgumentException('Select exactly one target response.');
                }
                $target = $entry;
            }
        }
        $this->validatePool($activity, array_values($letters), $target, $mode);
        $selectedLetter = $letters[$target['letter']];
        if ($activity === 'coloring' && $mode === 'letter' && $selectedLetter['img'] === '') {
            throw new InvalidArgumentException('The Coloring target needs a letter line-art image attachment.');
        }
        return [
            'activity' => $activity,
            'mode' => $mode,
            'content' => ['availableLetters' => array_values($letters), 'selectedLetter' => $selectedLetter],
            'config' => [
                'playMode' => 'guided',
                'targetLetter' => $needsLetters ? $selectedLetter : null,
                'targetWord' => $mode === 'word' ? $target['word'] : null,
            ],
            'target_word_index' => $target['word_index'],
            'card_ids' => $cardIds,
            'question' => $target['card'],
            'response' => $target['card'],
        ];
    }

    public function validatePool(string $activity, array $letters, ?array $target, ?string $mode = null): void
    {
        $mode = $mode ?? self::mode(['vbl_activity' => $activity]);
        $requirements = self::ACTIVITIES[$activity] ?? null;
        if (!$requirements || !$target) {
            throw new InvalidArgumentException('A supported activity and target response are required.');
        }
        $words = [];
        foreach ($letters as $letter) {
            foreach ($letter['words'] as $word) {
                $text = str_replace(['[', ']'], '', $word['word']);
                $words[] = $activity === 'bird-activity' ? mb_strtolower($text) : $text;
            }
        }
        $minLetters = $mode === 'letter' || $activity === 'fill-letter' ? $requirements['letters'] : 1;
        $minWords = $mode === 'letter' ? 0 : $requirements['words'];
        if (count($letters) < $minLetters || count(array_unique($words)) < $minWords) {
            $content = $mode === 'letter' ? "{$minLetters} distinct letters"
                : ($activity === 'fill-letter' ? "{$minLetters} distinct letters and 1 target word"
                    : "{$minWords} distinct words");
            throw new InvalidArgumentException(
                "{$requirements['label']} needs at least {$content}."
            );
        }
        if ($activity === 'coloring' && $mode === 'word' && empty($target['word']['coloring'])) {
            throw new InvalidArgumentException('The Coloring target needs a line-art image attachment.');
        }
    }

    public function exercise(array $exercise, array $options): array
    {
        $payload = $this->payload($exercise, $options);
        $exercise['questions'] = [[
            'question' => $payload['question'],
            'response' => $payload['response'],
            'vocab_builder' => $payload,
        ]];
        return $exercise;
    }
}
