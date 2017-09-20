<?php namespace BackupManager\Laravel;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class Laravel5Compatibility
 * @package BackupManager\Laravel
 */
trait Laravel55Compatibility {
    /**
     * @param $headers
     * @param $rows
     * @param string $style
     * @param array $columnStyles
     * @return void
     */
    public function table($headers, $rows, $style = 'default', array $columnStyles = []) {
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

    public function handle()
    {
        $this->fire();
    }
}
