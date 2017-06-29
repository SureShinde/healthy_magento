<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('recipe_entity')} ADD `url_key` varchar(255) NULL DEFAULT NULL; 

")->endSetup();