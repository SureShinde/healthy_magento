<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('recipe_entity')} ADD `meta_title` varchar(255) NULL DEFAULT NULL; 
ALTER TABLE {$this->getTable('recipe_entity')} ADD `meta_keywords` varchar(3000) NULL DEFAULT NULL;
ALTER TABLE {$this->getTable('recipe_entity')} ADD `meta_description` varchar(3000) NULL DEFAULT NULL;

")->endSetup();