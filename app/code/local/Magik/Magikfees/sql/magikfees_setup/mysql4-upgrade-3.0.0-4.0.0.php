<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('magikfees'),'store_id', 'TEXT NULL') ;

$installer->run("
 
DROP TABLE IF EXISTS {$this->getTable('magikfeestype')};
 
");





