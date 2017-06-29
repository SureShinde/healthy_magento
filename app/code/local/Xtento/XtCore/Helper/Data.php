<?php

/**
 * Product:       Xtento_XtCore (1.0.0)
 * ID:            f6mkdr0AjV6059XsnWMj6ywp+n8tLN1ztcK7BKTCMeA=
 * Packaged:      2013-09-18T18:58:20+00:00
 * Last Modified: 2012-12-02T16:34:18+01:00
 * File:          app/code/local/Xtento/XtCore/Helper/Data.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_XtCore_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getInstallationDate()
    {
        return Mage::getStoreConfig('xtcore/adminnotification/installation_date');
    }
}