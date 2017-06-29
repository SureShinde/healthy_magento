<?php
abstract class  AW_Aheadmetrics_Model_Reports_Abstract
{
    const BASE_URL = 'http://app.am.mogilev.aheadworks.com/backwardsync/';
    const REPORT_CODE = 'salesByProductAttributes';
    private $_server = null;

    protected function _sendRequest($url, $data, &$response)
    {
        if (!$this->_server) {
            $this->_server = Mage::getConfig()->getNode(
                'default/awaheadmetrics/processing'
            )->server;

        }
        $params = array(
            CURLOPT_URL => $this->_server . '/backwardsync/' . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_SSL_VERIFYPEER => false
        );
        $ch = curl_init();
        curl_setopt_array($ch,
            $params
        );

        // if (Mage::helper('awaheadmetrics')->isDebugMode()) {
             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // }

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception(curl_error($ch).__LINE__);
        }
        curl_close($ch);
        return $response;
    }

    abstract function processing();


}