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
    // check changes and output an array report
    $report = (\atk4\schema\Migration::getMigration($m))->migrate(true,\atk4\schema\MigrationReport::REPORT_TYPE_ARRAY);
    print_r($report);
} catch (\atk4\core\Exception $e) {
    echo $e->getColorfulText();
}
