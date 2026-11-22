<?php

declare(strict_types=1);

namespace BackupManager\Laravel;

/**
 * Reads the command's options and asks for the ones left out.
 *
 * @internal
 */
trait PromptsForOptions
{
    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param list<string> $required
     * @return list<string> the required options that were not given
     */
    private function missingOptions(array $required): array
    {
        return array_values(array_filter($required, fn (string $name): bool => $this->stringOption($name) === null));
    }

    /**
     * In a non-interactive run (scheduler, --no-interaction) missing options
     * are an error instead of a prompt.
     *
     * @param list<string> $missing
     */
    private function refuseToPrompt(array $missing): bool
    {
        if ($missing === [] || $this->input->isInteractive()) {
            return false;
        }
        $this->error('Missing options: --' . implode(', --', $missing));

        return true;
    }

    /**
     * @param list<string> $choices
     */
    private function askChoice(string $option, string $question, array $choices): bool
    {
        $answer = $this->choice($question, $choices);
        $this->input->setOption($option, is_array($answer) ? (string) reset($answer) : $answer);

        return true;
    }

    private function askText(string $option, string $question): bool
    {
        $answer = $this->ask($question);
        $this->input->setOption($option, is_string($answer) ? $answer : '');

        return true;
    }

    private function confirmed(string $summary): bool
    {
        $this->info('Just to be sure...');
        $this->info($summary);
        $this->newLine();

        return $this->confirm('Are these correct?', true);
    }
}
