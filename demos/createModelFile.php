<?php

include __DIR__ . '/init.php';

$migrator = new \atk4\schema\Migration\MySQL($db);
$sourcecode = $migrator->createModelFromTable('user','User','id','App\Models');

echo '<pre>' . $sourcecode . '</pre>';