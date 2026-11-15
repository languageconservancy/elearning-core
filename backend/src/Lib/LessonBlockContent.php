<?php

namespace App\Lib;

use InvalidArgumentException;

class LessonBlockContent
{
    public static function validate(array $blocks): void
    {
        foreach ($blocks as $index => $block) {
            $number = $index + 1;
            switch ($block['type'] ?? '') {
                case 'card':
                    if (empty($block['card_id'])) {
                        throw new InvalidArgumentException("Please select a card for block $number");
                    }
                    $enabled = false;
                    foreach (['lakota', 'english', 'audio', 'video', 'image'] as $field) {
                        if (!in_array($block['is_card_' . $field] ?? 'N', ['Y', 'N'], true)) {
                            throw new InvalidArgumentException("Card display fields must be Yes or No for block $number");
                        }
                        $enabled = $enabled || ($block['is_card_' . $field] ?? 'N') === 'Y';
                    }
                    if (!$enabled) {
                        throw new InvalidArgumentException("Select at least one card display field for block $number");
                    }
                    break;
                case 'html':
                    if (trim((string)($block['custom_html'] ?? '')) === '') {
                        throw new InvalidArgumentException("Please enter HTML for block $number");
                    }
                    break;
                case 'audio':
                case 'image':
                case 'video':
                    if (empty($block[$block['type'] . '_id'])) {
                        throw new InvalidArgumentException("Please select {$block['type']} for block $number");
                    }
                    break;
                default:
                    throw new InvalidArgumentException("Please select a type for block $number");
            }
        }
    }
}
