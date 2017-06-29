<?php
/**
 * Amid Bundle Image Module Dynamic Controller file
 *
 * @category  Amid
 * @package   Amid_BundleImage
 * @copyright Copyright (c) 2015 Perficient, Inc. (http://commerce.perficient.com/)
 * @author    Andrew Versalle <andrew.versalle@perficient.com>
 */

/**
 * Amid Bundle Image Module Dynamic Controller class
 *
 * @category  Amid
 * @package   Amid_BundleImage
 * @copyright Copyright (c) 2015 Perficient, Inc. (http://commerce.perficient.com/)
 * @author    Andrew Versalle <andrew.versalle@perficient.com>
 */
class Amid_BundleImage_DynamicController extends \Mage_Core_Controller_Front_Action
{
    /**
     * Response array for API
     *
     * @var array
     */
    private $_jsonResponse = array();

    /**
     * Amid Bundle Image Module Image Helper
     *
     * @var Amid_BundleImage_Helper_Image
     */
    private $_helper;

    /**
     * Class constructor method
     */
    public function _construct()
    {
        // Get helper
        $this->_helper = \Mage::helper('amid_bundleimage/image');
    }

    /**
     * Method to get bundle image
     */
    public function getAction()
    {
        $data = $this->getRequest()->getParams();

        try {
            $bundleImage = $this->_helper->getBundleImage($data);

            $this->_setSuccess($bundleImage);
        } catch (\Exception $e) {
            \Mage::log('Error Getting Dynamic Image: ' . $e->getMessage(), \Zend_Log::ERR, 'amid_bundleimage.log');

            $this->_setError($e);
        }

        $this->_sendResponse();
    }

    /**
     * Method to send encoded response
     */
    private function _sendResponse()
    {
        // Send response
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($this->_jsonResponse));
    }

    /**
     * Method to set success and return image
     *
     * @param string $image Image URL
     */
    private function _setSuccess($image)
    {
        $this->_jsonResponse['status']          = 'success';
        $this->_jsonResponse['data']['image']   = $image;
    }

    /**
     * Method to set error after exception
     *
     * @param Exception $exception
     */
    private function _setError(\Exception $exception)
    {
        // Set response
        $this->_jsonResponse['status']  = 'error';
        $this->_jsonResponse['message'] = $exception->getMessage();
    }
}
