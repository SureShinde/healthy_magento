<?php
/**
 * Amid E-Mail Marketing Module Data Upgrade Script file
 *
 * @category  Amid
 * @package   Amid_EmailMarketing
 * @copyright Copyright (c) 2015 Perficient, Inc. (http://commerce.perficient.com/)
 * @author    Andrew Versalle <andrew.versalle@perficient.com>
 * @version   0.0.2
 */

/** @var Mage_Newsletter_Model_Resource_Subscriber_Collection $collection Newsletter subscribers collection */
$collection = \Mage::getModel('newsletter/subscriber')->getCollection();

/** @var Amid_EmailMarketing_Model_Subscriber $item Amid E-Mail Marketing Module Subscriber Model class */
foreach ($collection as $item) {
    $item->delete();
}
