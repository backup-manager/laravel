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
     * Laravel's own rule (ConfiguresPrompts): prompts need an interactive
     * input on a terminal. Symfony Console only turns interactivity off for
     * --no-interaction, so the scheduler and queued Artisan::call() runs
     * would otherwise prompt and abort.
     */
    private function canPrompt(): bool
    {
        if (!$this->input->isInteractive()) {
            return false;
        }

        return (defined('STDIN') && stream_isatty(STDIN)) || $this->laravel->runningUnitTests();
    }

    /**
     * Without a terminal, missing options are an error instead of a prompt.
     *
     * @param list<string> $missing
     */
    private function refuseToPrompt(array $missing): bool
    {
        if ($missing === [] || $this->canPrompt()) {
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
        do {
            $answer = $this->ask($question);
        } while (!is_string($answer) || trim($answer) === '');
        $this->input->setOption($option, $answer);

        return true;
    }

    private function confirmed(string $summary): bool
    {
        $this->info('Just to be sure...');
        $this->info($summary);
        $this->newLine();

        // Defaults to no, as in 2.x: Enter re-asks instead of running a restore.
        return $this->confirm('Are these correct?', false);
    }
}
