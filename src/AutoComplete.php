<?php namespace BackupManager\Laravel;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class AutoComplete
 *
 * @package BackupManager\Laravel
 */
trait AutoComplete {
    /**
     * @param $dialog
     * @param array $list
     * @param null $default
     * @throws \LogicException
     * @throws InvalidArgumentException
     * @internal param $question
     * @return mixed
     */
    public function autocomplete($dialog, array $list, $default = null) {
        $validation = function ($item) use ($list) {
            if ( ! in_array($item, array_values($list))) {
                throw new \InvalidArgumentException("{$item} does not exist.");
            }
            return $item;
        };

        try {
            return $this->useSymfontDialog($dialog, $list, $default, $validation);
        } catch (InvalidArgumentException $error) {
            //
        }
        return $this->useSymfonyQuestion($dialog, $default, $validation);
    }

    /**
     * @param $dialog
     * @param array $list
     * @param null $default
     * @return mixed
     */
    protected function useSymfontDialog($dialog, array $list, $default = null, $validation) {
        $helper = $this->getHelperSet()->get('dialog');

        return $helper->askAndValidate(
            $this->output, "<question>{$dialog}</question>", $validation, false, $default, $list
        );
    }

    /**
     * @param $dialog
     * @param null $default
     * @return mixed
     */
    protected function useSymfonyQuestion($dialog, $default = null, $validation) {
        $question = new Question($dialog . ' ', $default);
        $question->setValidator($validation);
        $helper = $this->getHelper('question');

        return $helper->ask($this->input, $this->output, $question);
    }
}
