<?php

namespace App\Test\TestCase\Lib;

use App\Lib\ExerciseContent;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

class ExerciseContentTest extends TestCase
{
    private function exercise(array $fields = []): array
    {
        return $fields + [
            'name' => 'Test', 'exercise_type' => 'multiple-choice', 'card_type' => 'card',
            'promteresponsetype' => 'a-l', 'promotetype' => 'a', 'responsetype' => 'l',
        ];
    }

    private function item(string $role, array $fields = []): array
    {
        return $fields + ['card_type' => $role, 'card_id' => 1];
    }

    private function assertRejected(string $message, array $exercise, array $options): void
    {
        try {
            ExerciseContent::validateOptions($exercise, $options);
            $this->fail('Expected the content to be rejected.');
        } catch (InvalidArgumentException $error) {
            $this->assertStringContainsString($message, $error->getMessage());
        }
    }

    public function testRulesOfferOnlySupportedPromptResponsePairs(): void
    {
        $rules = ExerciseContent::rules();
        $this->assertContains('a-l', $rules['multiple-choice']['card']['pairs']);
        $this->assertContains('l-l', $rules['multiple-choice']['card']['pairs']);
        $this->assertNotContains('i-i', $rules['multiple-choice']['card']['pairs']);
        $this->assertNotContains('l-l', $rules['multiple-choice']['card_group']['pairs']);
        $this->assertNotContains('l-l', $rules['anagram']['card']['pairs']);
        $this->assertNotContains('a-r', $rules['multiple-choice']['card']['pairs']);
        $this->assertSame(['a-r', 'e-r', 'i-r', 'l-r', 'v-r'], $rules['recording']['card']['pairs']);
        $this->assertArrayNotHasKey('custom', $rules['anagram']);
        $this->assertSame('required', $rules['truefalse']['card_group']['roundSize']);
        $this->assertSame('derived', $rules['match-the-pair']['custom']['roundSize']);
        $this->assertNull($rules['multiple-choice']['card']['roundSize']);
    }

    public function testRoleCountsFollowTheSetup(): void
    {
        $complete = [$this->item('P'), $this->item('R'), $this->item('O')];
        ExerciseContent::validateOptions($this->exercise(), $complete);
        $this->assertRejected('correct answer', $this->exercise(), [$this->item('P'), $this->item('O')]);
        $this->assertRejected('no more than 1 prompt', $this->exercise(), array_merge($complete, [$this->item('P')]));
        $this->assertRejected('role this exercise does not use', $this->exercise(['exercise_type' => 'match-the-pair']), $complete);
        $custom = $this->exercise(['card_type' => 'custom']);
        $this->assertRejected('no more than 3', $custom, [$this->item('P'), $this->item('O'), $this->item('O'), $this->item('O'), $this->item('O')]);
    }

    public function testSetupSpecificRequirements(): void
    {
        $this->assertRejected('Recording response', $this->exercise(['promteresponsetype' => 'a-r']), [$this->item('P')]);
        $this->assertRejected('cards to use per round', $this->exercise(['card_type' => 'card_group']), [$this->item('P'), $this->item('O')]);
        $fill = $this->exercise(['exercise_type' => 'fill_in_the_blanks']);
        $this->assertRejected('[brackets]', $fill, [$this->item('P', ['text_option' => 'No blanks'])]);
        ExerciseContent::validateOptions($fill, [$this->item('P', ['text_option' => 'A [blank] here'])]);
        $this->assertRejected('language text', array_merge($fill, ['responsetype' => 'e']), [$this->item('P', ['text_option' => 'A [b]'])]);
    }
}
