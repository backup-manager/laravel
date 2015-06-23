<?php namespace BackupManager\Laravel;

/**
 * Class AutoComplete
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
                throw new InvalidArgumentException("{$item} does not exist.");
            }
            return $item;
        };
        $helper = $this->getHelperSet()->get('dialog');
        return $helper->askAndValidate($this->output, "<question>{$dialog}</question>", $validation, false, $default, $list);
    }
}
