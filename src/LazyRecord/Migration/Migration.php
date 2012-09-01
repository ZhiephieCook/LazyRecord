<?php
namespace LazyRecord\Migration;
use SQLBuilder\MigrationBuilder;
use LazyRecord\Schema\DynamicSchemaDeclare;
use LazyRecord\ConnectionManager;
use LazyRecord\Console;
use PDOException;

class Migration
{
    public $driver;
    public $builder;
    public $connection;
    public $logger;

    public function __construct($dsId)
    {
        $connectionManager = ConnectionManager::getInstance();
        $this->driver = $connectionManager->getQueryDriver($dsId);
        $this->connection = $connectionManager->getConnection($dsId);
        $this->builder = new MigrationBuilder($this->driver);
        $this->logger  = Console::getInstance()->getLogger();
    }

    /**
     * Execute sql for migration
     *
     * @param string $sql
     */
    public function executeSql($sql) 
    {
        try { 
            foreach( (array) $sql as $q ) {
                $stm = $this->connection->query($q);
                $this->logger->info('QueryOK: ' . $q);
            }
        } catch(PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


    public function addColumn($table,$cb)
    {
        $c = new \LazyRecord\Schema\SchemaDeclare\Column;
        call_user_func($cb,$c);
        $sql = $this->builder->addColumn($table,$c);
        $this->executeSql($sql);
    }

    /**
     * $this->createTable(function($s) {
     *      $s->column('title')->varchar(120);
     * });
     */
    public function createTable($cb) 
    {
        $ds =  new DynamicSchemaDeclare;
        call_user_func($cb,$ds);
        $ds->build();

        $builder = \LazyRecord\SqlBuilder\SqlBuilderFactory::create($this->driver);
        $sqls = $builder->build($ds);
        $this->executeSql($sqls);
    }

    public function importSchema($schema) {
        $builder = \LazyRecord\SqlBuilder\SqlBuilderFactory::create($this->driver);
        if( is_a($schema,'LazyRecord\Schema\SchemaDeclare') ) {
            $sqls = $builder->build($schema);
            $this->executeSql($sqls);
        } 
        elseif( is_a($schema,'LazyRecord\BaseModel') && method_exists($schema,'schema') ) {
            $model = $schema;
            $schema = new DynamicSchemaDeclare($model);

            $sqls = $builder->build($schema);
            $this->executeSql($sqls);
        }
    }

    /**
     * Execute migration sql builder commands
     *
     * @param string $m method name
     * @param array $a method arguments
     */
    public function executeCommand($m,$a) 
    {
        $this->logger->info($m);
        $sql = call_user_func_array( array($this->builder,$m) , $a );
        $this->executeSql($sql);
    }

    public function upgrade() {
    }

    public function downgrade() { 
    }

    public function __call($m,$a) {
        $this->executeCommand($m,$a);
    }
}



