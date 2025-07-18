<?php

use Cake\Datasource\ConnectionManager;

namespace App\Lib;

class UtilLibrary
{
    /* For converting between letter and word */
    public const PROMPT_LETTER_TO_WORD = [
        'l' => 'lakota', 'e' => 'english',
        'i' => 'image_id', 'a' => 'audio',
        'v' => 'video_id', 'r' => 'recording'];
    public const PROMPT_WORD_TO_LETTER = [
        'lakota' => 'l', 'english' => 'e',
        'image_id' => 'i', 'audio' => 'a',
        'video_id' => 'v', 'recording' => 'r'];

    public const PROMPT_TYPE_WORDS = [
        'lakota', 'english', 'image_id', 'audio', 'video', 'recording'
    ];

    public const MIN_CARDS_REQUIRED_FOR_REVIEW = 2;
    public const UNLOCK_NEXT_UNIT_MULTIPLIER = 3;
    public const MAX_CORRECT_ANSWERS_TO_UNLOCK_NEXT_UNIT = 25;

    // role strings that must be the same as in the database
    public const ROLE_SUPERADMIN_STR = 'superadmin';
    public const ROLE_TEACHER_STR = 'teacher';
    public const ROLE_STUDENT_STR = 'student';
    public const ROLE_MODERATOR_STR = 'moderator';
    public const ROLE_CONTENT_DEVELOPER_STR = 'content developer';
    public const ROLE_USER_STR = 'user';

    public const SCHOOL_ROLE_TEACHER_STR = 'teacher';
    public const SCHOOL_ROLE_SUBSTITUTE_STR = 'substitute';
    public const SCHOOL_ROLE_STUDENT_STR = 'student';

    public const FORBIDDEN_RESPONSE_REASONS = [
        "AGREEMENTS_NOT_ACCEPTED" => "You must accept the agreements to proceed.",
        "USER_NOT_PUBLICLY_ACCESSIBLE" => "User is not publicly accessible due to age.",
        "CANNOT_TAMPER_WITH_OTHERS_DATA" => "You cannot tamper with other users' data.",
    ];

    /**
     * @param object|resource|array|string|float|int|bool|null $var
     * @return bool
     */
    public static function isValid($var): bool
    {
        return (isset($var) && !empty($var));
    }

    /**
     * Given an array of percentages, stack them up and generate a random number between 0 and the sum
     * of the percentages and determine which percentage index it falls in.
     * Do this $num times. This simulates selecting items from
     * an array with frequency based on the percentages. This method also
     * eliminates a need to directly keep
     * track of the frequencies, which is much simpler.
     * Ex:
     *   Percentages:               1  5  25  50   50
     *   StackedPercentages:        1  6  31  81  131
     *   StackedPercentagesIndices: 0  1   2   3    4
     *   Random Number: 28
     *   Index: 2, which corresponds to the 25% item and this corresponds to
     *   some item that should occur 25% of the time.
     * @param $percentages Array of integer percentage values that don't necessarily add to 100
     * @param $num Number of random percentages to generate
     * @return null|array<int> (0-based) of percentages area that the random number corresponds to for $num items (null|array<int>)
     */
    public static function getRandomIndexFromStackedPercentages($percentages, $numRandomIndicesDesired): ?array
    {
        /* Check for invalid arguments */
        if (empty($percentages) || $numRandomIndicesDesired <= 0) {
            return null;
        }

        /* Sum of the percentages to use as the max value for the random number generator */
        $sum = 0;
        $stackedPercentages = array();
        $indices = array();

        /* Compute sum of percentages, and create array of stacked percentages */
        foreach ($percentages as $p) {
            if ($p < 0) {
                return null;
            }
            $sum += $p;
            $stackedPercentages[] = $sum;
        }

        /* For number of times specified by $num argument, */
        for ($i = 0; $i < $numRandomIndicesDesired; $i++) {
            /* Generate a random number between 0 and sum of percentages using
               the Mersenne Twister Random Number Generator
               which is more efficient than plain old rand(), which uses old libc implementations. */
            $rand_num = mt_rand(0, $sum);

            /* Determine which activity this random number corresponds to */
            foreach ($stackedPercentages as $index => $percentage) {
                if ($rand_num <= $percentage) {
                    $indices[] = $index;
                    break;
                }
            }
        }

        return (count($indices) == $numRandomIndicesDesired) ? $indices : null;
    }

    public static function getRandomItemFromArray($array)
    {
        if ($array == null) {
            return null;
        }
        /* Randomly pick a prompt type and a response type */
        $randIndex = (mt_rand(0, count($array) - 1));
        return $array[$randIndex];
    }

    public static function convertPromptWordToLetter($promptOrResponseWord)
    {
        return UtilLibrary::PROMPT_WORD_TO_LETTER[$promptOrResponseWord];
    }

    public static function convertPromptLetterToWord($promptOrResponseLetter)
    {
        return UtilLibrary::PROMPT_LETTER_TO_WORD[$promptOrResponseLetter];
    }

    public static function printDatabaseTables($datasource)
    {
        $myTables = ConnectionManager::get($datasource)
            ->schemaCollection()->listTables();
        var_dump($myTables);
    }

    public static function convertPhraseIntoArrayOfWords($phrase)
    {
        return preg_split('/(?<=\s)|(?<=\w)(?=[.,:;!?()-])|(?<=[.,!()?\x{201C}])(?=[^ ])/u', $phrase);
    }

    public static function isCountable($o)
    {
        return (is_array($o) || $o instanceof Countable);
    }

    public static function checkArrayMembers($data, $params)
    {
        $msg = '';

        if (!$params || !is_array($params)) {
            return ['status' => false, 'message' => 'params invalid', 'data' => array()];
        }

        foreach ($params as $p) {
            if (
                !is_array($data) || !array_key_exists($p, $data)
                || !$data[$p] || $data[$p] == ""
            ) {
                if ($msg != '') {
                    $msg .= " ";
                }
                $msg .= $p . ' is not set.';
            }
        }

        return ['status' => $msg == '', 'message' => $msg, 'data' => array()];
    }

    /**
     * Load keyboard configuration from JSON file
     * @return array|null
     */
    private static function loadKeyboardConfig()
    {
        static $config = null;

        if ($config === null) {
            $configPath = CONFIG . 'keyboard.json';
            if (file_exists($configPath)) {
                $config = json_decode(file_get_contents($configPath), true);
            } else {
                $config = [];
            }
        }

        return $config;
    }

    public static function getLanguageAllWordCharsRegex()
    {
      $keyboardConfig = self::loadKeyboardConfig();

      if (empty($keyboardConfig['allWordCharsRegex'])) {
        return "a-zA-Z";
      }

      return $keyboardConfig['allWordCharsRegex'];
    }

    public static function replaceInvalidChars($text, $language)
    {
        $keyboardConfig = self::loadKeyboardConfig();
        $toFind = [];
        $replaceWith = [];

        // Use keyboard config if available, otherwise fall back to hardcoded values
        if (!empty($keyboardConfig['characterReplacements'])) {
            $toFind = array_keys($keyboardConfig['characterReplacements']);
            $replaceWith = array_values($keyboardConfig['characterReplacements']);
        } else {
          return $text;
        }

        return str_replace($toFind, $replaceWith, $text);
    }

    public static function replaceInvalidPunctuation($text, $language)
    {
        $keyboardConfig = self::loadKeyboardConfig();
        $toFind = [];
        $replaceWith = [];

        // Use keyboard config if available, otherwise fall back to hardcoded values
        if (!empty($keyboardConfig['characterReplacements'])) {
            $toFind = array_keys($keyboardConfig['characterReplacements']);
            $replaceWith = array_values($keyboardConfig['characterReplacements']);
        } else {
          return $text;
        }

        return str_replace($toFind, $replaceWith, $text);
    }

    public static function numCorrectReviewAnswersToUnlockUnit(int $numReviewCardsInUnit): int {
        if ($numReviewCardsInUnit < self::MIN_CARDS_REQUIRED_FOR_REVIEW) {
            return 0;
        } else {
            return min(
                $numReviewCardsInUnit * self::UNLOCK_NEXT_UNIT_MULTIPLIER,
                self::MAX_CORRECT_ANSWERS_TO_UNLOCK_NEXT_UNIT,
            );
        }
    }
}
