<?php

namespace atk4\schema;

use atk4\data\Persistence;

class PHPUnit_SchemaTestCase extends \atk4\core\PHPUnit_AgileTestCase
{
    public $db;

    public $tables = null;

    public $debug = false;

    public $mode = 'sqlite';

    public function setUp()
    {
        parent::setUp();
        // establish connection
        $dsn = getenv('DSN');
        if ($dsn) {
            $this->db = Persistence::connect(($this->debug ? ('dumper:') : '').$dsn);
            list($this->mode, $junk) = explode(':', $dsn, 2);
        } else {
            $this->db = Persistence::connect(($this->debug ? ('dumper:') : '').'sqlite::memory:');
        }
    }

    public function getMigration($m = null)
    {
        if ($this->mode == 'sqlite') {
            return new \atk4\schema\Migration\SQLite($m ?: $this->db);
        } elseif ($this->mode == 'mysql') {
            return new \atk4\schema\Migration\MySQL($m ?: $this->db);
        } else {
            throw new \atk4\core\Exception(['Not sure which migration class to use for your DSN', 'mode'=>$this->mode, 'dsn'=>getenv('DSN')]);
        }
    }

    /**
     * Use this method to clean up tables after you have created them,
     * so that your database would be ready for the next test
     */
    public function dropTable($table)
    {
        $this->db->connection->expr("drop table if exists {}", [$table])->execute();
    }

    /**
     * Sets database into a specific test.
     */
    public function setDB($db_data)
    {
        $this->tables = array_keys($db_data);

        // create databases
        foreach ($db_data as $table => $data) {
            $s = $this->getMigration();
            $s->table($table)->drop();

            $first_row = current($data);

            foreach ($first_row as $field => $row) {
                if ($field === 'id') {
                    $s->id('id');
                    continue;
                }

                if (is_int($row)) {
                    $s->field($field, ['type' => 'integer']);
                    continue;
                }

                $s->field($field);
            }

            if (!isset($first_row['id'])) {
                $s->id();
            }

            $s->create();

            $has_id = (bool) key($data);

            foreach ($data as $id => $row) {
                $s = $this->db->dsql(); //(['connection' => $this->db->connection]);
                if ($id === '_') {
                    continue;
                }

                $s->table($table);
                $s->set($row);

                if (!isset($row['id']) && $has_id) {
                    $s->set('id', $id);
                }

                $s->insert();
            }
        }
    }

    public function getDB($tables = null, $noid = false)
    {
        if (!$tables) {
            $tables = $this->tables;
        }


        if (is_string($tables)) {
            $tables = array_map('trim', explode(',', $tables));
        }

        $ret = [];

        foreach ($tables as $table) {
            $data2 = [];

            $s = $this->db->dsql();
            $data = $s->table($table)->get();

            foreach ($data as &$row) {
                foreach ($row as &$val) {
                    if (is_int($val)) {
                        $val = (int) $val;
                    }
                }

                if ($noid) {
                    unset($row['id']);
                    $data2[] = $row;
                } else {
                    $data2[$row['id']] = $row;
                }
            }

            $ret[$table] = $data2;
        }

        return $ret;
    }

    public function runBare()
    {
        try {
            return parent::runBare();
        } catch (\atk4\core\Exception $e) {
            throw new \atk4\data\tests\AgileExceptionWrapper($e->getMessage(), 0, $e);
        }
    }

    public function callProtected($obj, $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    public function getProtected($obj, $name)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getProperty($name);
        $method->setAccessible(true);

        return $method->getValue($obj);
    }
}
