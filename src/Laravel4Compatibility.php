<?php namespace BackupManager\Laravel;

/**
 * Class Laravel4Compatibility
 * @package BackupManager\Laravel
 */
trait Laravel4Compatibility {
    /**
     * @param array $headers
     * @param array $rows
     * @internal param string $style
     * @return void
     */
    public function table(array $headers, array $rows, $style = 'default') {
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render($this->output);
    }
}
