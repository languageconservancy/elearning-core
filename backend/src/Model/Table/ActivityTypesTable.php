<?php

namespace App\Model\Table;

use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\Table;
use App\Lib\UtilLibrary;

/**
 * @class ActivityTypePercentagesTable
 * Class for the table in the database that defines the
 * percentage of exercise types that should be used for the cards.
 * Fields: id, specific_skill, global_skill, prompt_response_pairs,
 * exercise_types, learning_percentage, review_percentage, learning_style.
 */
class ActivityTypesTable extends Table
{
    public const WORD_CARDS = 'words';
    public const PATTERN_CARDS = 'patterns';

    public function initialize(array $config): void
    {
        $this->setPrimaryKey('id');
    }

    public function getAll(): Query
    {
        return $this->find();
    }

    public function getAllNotExcluded($cardType): Query
    {
        /* Add exclude list for activity exercise types that aren't yet implemented */
        return $this->find()
            ->select([
                'id',
                'global_skill',
                'prompt_response_pairs_' . $cardType,
                'exercise_type_' . $cardType,
                'review_percentage_' . $cardType])
            ->where(['exclude_' . $cardType => 0, 'review_percentage_' . $cardType . ' >' => 0])
            ->order(['id' => 'ASC']);
    }

    public function getProbabilisticallyAccordingToReviewPercentages(int $numActivitiesToGet, string $cardType): ?array
    {
        /* Check for bad card type argument */
        if ($cardType != self::WORD_CARDS && $cardType != self::PATTERN_CARDS) {
            Log::error("Bad card type in getProbabilisticallyAccordingToReviewPercentages()");
            return null;
        }

        $activityTypes = $this->getAllNotExcluded($cardType)->toArray();

        $activities = array();
        if ($cardType == self::WORD_CARDS) {
            $percentages = array_map(function ($e) {
                return $e['review_percentage_words'];
            }, $activityTypes);
        } else {
            $percentages = array_map(function ($e) {
                return $e['review_percentage_patterns'];
            }, $activityTypes);
        }

        /* Select an activity type based on the activity type percentages */
        $activityTypeIndices = UtilLibrary::getRandomIndexFromStackedPercentages($percentages, $numActivitiesToGet);

        if ($activityTypeIndices == null) {
            Log::error("No valid activity type indices");
            return null;
        }

        /* Create array with the randomly generated specific activity types */
        foreach ($activityTypeIndices as $index) {
            $activities[] = $activityTypes[$index];
        }

        return $activities;
    }

    public function getRandomExerciseType($activity)
    {
        $exerciseTypes = explode(',', $activity['exercise_type']);
        return $exerciseTypes[mt_rand(0, count($exerciseTypes) - 1)];
    }

    public function getPromptAndResponseWords($activity): ?array
    {
        /* Make sure we are dealing with just an entity and not an array */
        if (is_array($activity)) {
            $activity = $activity[0];
        } elseif ($activity == null) {
            return null;
        }

        $promptAndResponseLetters = $this->getPromptAndResponseLetters($activity);
        if ($promptAndResponseLetters == null) {
            return null;
        }
        $promptTypeWords = array();
        $responseTypeWords = array();

        /* Put prompt type words into an array */
        foreach ($promptAndResponseLetters['prompt_types'] as $promptTypeLetter) {
            $promptTypeWords[] = UtilLibrary::convertPromptLetterToWord($promptTypeLetter);
        }

        /* Put response type words into an array */
        foreach ($promptAndResponseLetters['response_types'] as $responseTypeLetter) {
            $responseTypeWords[] = UtilLibrary::convertPromptLetterToWord($responseTypeLetter);
        }

        /* Return an array of the two array */
        return array(
            'prompt_types' => $promptTypeWords,
            'response_types' => $responseTypeWords);
    }

    public function getPromptAndResponseLetters($activity): ?array
    {
        /* Make sure we are dealing with just an entity and not an array */
        if (is_array($activity)) {
            $activity = $activity[0];
        } elseif ($activity == null) {
            return null;
        }

        /* Extract the prompt types and the response types for this activity */
        $promptTypes = array();
        $responseTypes = array();
        if ($activity->is_word_activity) {
            $activityPromptResponsePairs = $activity['prompt_response_pairs_words'];
        } elseif ($activity->is_pattern_activity) {
            $activityPromptResponsePairs = $activity['prompt_response_pairs_patterns'];
        } else {
            Log::error(
                'Woops, isset for both $activity->prompt_response_pairs_words '
                . 'and $activity->prompt_response_pairs_patterns returned false. Something is wrong.'
            );
            return null;
        }

        $pairs = explode(',', $activityPromptResponsePairs);
        foreach ($pairs as $pair) {
            $pair_exploded = explode('-', $pair);
            $promptTypes[] = $pair_exploded[0];
            $responseTypes[] = $pair_exploded[1];
        }

        /* Return array of the two array */
        return array(
            'prompt_types' => $promptTypes,
            'response_types' => $responseTypes);
    }

    public static function printActivities($activities)
    {
        if ($activities == null) {
            return;
        }

        for ($i = 0; $i < count($activities); $i++) {
            $act = $activities[$i];
            $cardTypeStr = $act->card_type_str;
            Log::info("Act[" . $i . "]: " .
                $act['exercise_type_' . $cardTypeStr . 's'] . ", " .
                $act['prompt_response_pairs_' . $cardTypeStr . 's'] . ", " .
                $cardTypeStr);
        }
    }
}
