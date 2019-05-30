<?php

include 'init.php';

class User extends \atk4\data\Model
{
    public function init()
    {
        parent::init();

        $this->addField('name',['type' => 'datetime']);
        $this->addField('email');
        $this->addField('created_dts',['type' => 'datetime']);
    }
}

$m = new User($db, 'user');

try {
    // apply migrator using dry_run
    // check changes and output a much detailed report report
    echo (\atk4\schema\Migration::getMigration($m))->migrate(true,\atk4\schema\MigrationReport::REPORT_TYPE_DETAIL) . PHP_EOL;
} catch (\atk4\core\Exception $e) {
    echo $e->getColorfulText();
}
