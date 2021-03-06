<?php

namespace LazyRecord\Command;

use LazyRecord\Migration\MigrationRunner;

class MigrateDowngradeCommand extends BaseCommand
{
    public function brief()
    {
        return 'Run downgrade migration scripts.';
    }

    public function aliases()
    {
        return array('d', 'down');
    }

    public function options($opts)
    {
        parent::options($opts);
        $opts->add('script-dir', 'Migration script directory. (default: db/migrations)');
        $opts->add('b|backup', 'Backup database before running migration script.');
    }

    public function execute()
    {
        if ($this->options->backup) {
            $connection = $this->getCurrentConnection();
            $driver = $this->getCurrentQueryDriver();
            if (!$driver instanceof PDOMySQLDriver) {
                $this->logger->error('backup is only supported for MySQL');

                return false;
            }
            $this->logger->info('Backing up database...');
            $backup = new MySQLBackup();
            if ($dbname = $backup->incrementalBackup($connection)) {
                $this->logger->info("Backup at $dbname");
            }
        }

        $dsId = $this->getCurrentDataSourceId();
        $runner = new MigrationRunner($dsId);
        $runner->load($this->options->{'script-dir'} ?: 'db/migrations');
        $this->logger->info('Running migration scripts to downgrade...');
        $runner->runDowngrade();
        $this->logger->info('Done.');
    }
}
