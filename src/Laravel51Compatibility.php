<?php namespace BackupManager\Laravel;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class Laravel5Compatibility
 * @package BackupManager\Laravel
 */
trait Laravel51Compatibility {
    /**
     * @param array $headers
     * @param array $rows
     * @internal param string $style
     * @return void
     */
    public function table(array $headers, $rows, $style = 'default') {
        try {
            $table = $this->getHelperSet()->get('table');
        } catch (InvalidArgumentException $error) {
            //
        } finally {
            $table = new Table($this->output);
        }

        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render($this->output);
    }
}
