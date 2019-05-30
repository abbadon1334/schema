<?php

include 'init.php';

class User extends \atk4\data\Model
{
    public function init()
    {
        parent::init();

        $this->addField('name');
        $this->addField('email');
    }
}

$m = new User($db, 'user');

try {
    // apply migrator using dry_run
    // check changes and output a simple report
    echo (\atk4\schema\Migration::getMigration($m))->migrate(true);
} catch (\atk4\core\Exception $e) {
    echo $e->getColorfulText();
}
