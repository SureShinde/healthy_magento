<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp2
 * @version    2.2.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'aw_sarp2/notification'
 */
$installer->run("
    CREATE TABLE IF NOT EXISTS `{$installer->getTable('aw_sarp2/notification')}` (
        `entity_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL default 'new_profile',
        `recipient` varchar(255) NOT NULL default 'customer',
        `profile_statuses` varchar(255) NOT NULL default '-',
        `days_before` int(11) NOT NULL default '0',
        `email_template` varchar(255) NOT NULL default 'sarp_template',
        `status` tinyint(2) NOT NULL default '1',
        `store_ids` text NOT NULL,
        PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Notification Table';
");

$installer->endSetup();