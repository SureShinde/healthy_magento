<?php
/**
 * Amid Bundle Image Module Image Helper file
 *
 * @category  Amid
 * @package   Amid_BundleImage
 * @copyright Copyright (c) 2015 Perficient, Inc. (http://commerce.perficient.com/)
 * @author    Andrew Versalle <andrew.versalle@perficient.com>
 */

/**
 * Amid Bundle Image Module Image Helper class
 *
 * @category  Amid
 * @package   Amid_BundleImage
 * @copyright Copyright (c) 2015 Perficient, Inc. (http://commerce.perficient.com/)
 * @author    Andrew Versalle <andrew.versalle@perficient.com>
 */
class Amid_BundleImage_Helper_Image extends \Mage_Core_Helper_Abstract
{
    /**
     * Extended catalog product model
     *
     * @var Amid_Catalog_Model_Product
     */
    private $_model;

    /**
     * Product entity resource model
     *
     * @var Mage_Catalog_Model_Resource_Product
     */
    private $_resource;

    /**
     * Catalog image helper
     *
     * @var Mage_Catalog_Helper_Image
     */
    private $_helper;

    /**
     * Store model
     *
     * @var Mage_Core_Model_Store
     */
    private $_store;

    /**
     * URL key for Product ID
     */
    const PRODUCT_ID_KEY = 'productId';

    /**
     * URL key for component IDs
     */
    const COMPONENT_IDS_KEY = 'componentIds';

    /**
     * Bundle Image
     */
    const IMAGE_CODE = 'build_component_image';

    /**
     * Bundle Image Position - Top attribute code
     */
    const IMAGE_POSITION_TOP_CODE = 'build_image_position_top';

    /**
     * Bundle Image Position - Left attribute code
     */
    const IMAGE_POSITION_LEFT_CODE = 'build_image_position_left';

    /**
     * Bundle Image Position - Z Index attribute code
     */
    const IMAGE_POSITION_ZINDEX_CODE = 'build_image_position_zindex';

    /**
     * Class constructor method
     */
    public function __construct()
    {
        // Initialize properties
        $this->_model       = \Mage::getModel('catalog/product');
        $this->_resource    = \Mage::getResourceModel('catalog/product');
        $this->_helper      = \Mage::helper('catalog/image');
        $this->_store       = \Mage::app()->getStore();
    }

    /**
     * Method to get bundle image
     *
     * @param array $data Array of data to build image
     *
     * @return string
     * @throws Exception
     */
    public function getBundleImage(array $data = array())
    {
        $this->_validate($data);

        try {
            $componentImages = $this->_getComponentImages($data[self::COMPONENT_IDS_KEY]);

            $image = $this->_buildImage($componentImages);

            return $this->_saveImage($image);
        } catch (\Exception $e) {
            \Mage::log('Error with Dynamic Image: ' . $e->getMessage(), \Zend_Log::ERR, 'amid_bundleimage.log');

            $baseImage = $this->_getBaseImage($data[self::PRODUCT_ID_KEY]);

            if ($baseImage === '') {
                throw $e;
            } else {
                return $baseImage;
            }
        }
    }

    /**
     * Method to validate GET data
     *
     * @param array $data GET data
     *
     * @throws Exception
     */
    private function _validate(array $data)
    {
        if (empty($data)) {
            throw new \Exception(\Mage::helper('amid_bundleimage')->__('Insufficient data.'));
        }

        if (empty($data[self::PRODUCT_ID_KEY])) {
            throw new \Exception(\Mage::helper('amid_bundleimage')->__('Missing Product ID.'));
        }

        if (empty($data[self::COMPONENT_IDS_KEY])) {
            throw new \Exception(\Mage::helper('amid_bundleimage')->__('Missing Component IDs'));
        }
    }

    /**
     * Method to get base bundle image
     *
     * @param int $productId ID of product to load
     *
     * @return string
     */
    private function _getBaseImage($productId)
    {
        // Load product
        $this->_model->load($productId);

        // Return image URL
        return (string) $this->_helper->init($this->_model, 'image');
    }

    /**
     * Method to get component images for bundle product
     *
     * @param array $componentIds Array of bundle option IDs
     *
     * @return array
     */
    private function _getComponentImages(array $componentIds)
    {
        // Declare array
        $componentImages = array();

        // Set counter
        $counter = 0;

        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection Product collection */
        $collection = $this->_model->getCollection();

        // Get image attribute
        $collection->addAttributeToSelect(self::IMAGE_CODE);

        // Set collection
        $collection->addAttributeToFilter('entity_id', array('in' => $componentIds));

        /** @var Amid_Catalog_Model_Product $product Extended catalog product model */
        foreach ($collection as $product) {
            // Get image filename
            $imageUrl                           = (string) $this->_helper->init($product, self::IMAGE_CODE);
            $componentImages[$counter]['image'] = str_replace(
                array(\Mage::getBaseUrl('media'), '/'),
                array(\Mage::getBaseDir('media') . DS, DS),
                $imageUrl
            );

            // Get image resource
            $componentImages[$counter]['resource'] = $this->_getImageResource($componentImages[$counter]['image']);

            // Add remaining elements to array
            $componentImages[$counter]['top']       = (int) $this->_resource->getAttributeRawValue(
                $product->getId(), self::IMAGE_POSITION_TOP_CODE, $this->_store
            );
            $componentImages[$counter]['left']      = (int) $this->_resource->getAttributeRawValue(
                $product->getId(), self::IMAGE_POSITION_LEFT_CODE, $this->_store
            );
            $componentImages[$counter]['zindex']    = (int) $this->_resource->getAttributeRawValue(
                $product->getId(), self::IMAGE_POSITION_ZINDEX_CODE, $this->_store
            );

            // Increment
            $counter++;
        }

        // Return array
        return $componentImages;
    }

    /**
     * Method to get image resource from URL
     *
     * @param string $filename Filename of image
     *
     * @return resource
     * @throws Exception
     */
    private function _getImageResource($filename)
    {
        // Open image
        $im = imagecreatefromjpeg($filename);

        // If error
        if (!$im) {
            // Throw exception
            throw new \Exception(\Mage::helper('amid_bundleimage')->__('Error loading image.'));
        }

        // Return image resource
        return $im;
    }

    /**
     * Method to build image from component images
     *
     * @param array $componentImages Array of image resources
     *
     * @return resource
     */
    private function _buildImage(array $componentImages)
    {
        // Get initial image
        $image = $this->_buildInitialImage($componentImages[0]['image']);

        // Get images count
        $count = count($componentImages);

        // Loop through images
        for ($i = 1; $i < $count; $i++) {
            list($width_x, $height_x) = getimagesize($componentImages[0]['image']);
            list($width_y, $height_y) = getimagesize($componentImages[$i]['image']);

            $image = imagecreatetruecolor($width_x + $width_y, $height_x);

            imagecopy($image, $componentImages[0]['resource'], 0, 0, 0, 0, $width_x, $height_x);
            imagecopy($image, $componentImages[$i]['resource'], $width_x, 0, 0, 0, $width_y, $height_y);
        }

        return $image;
    }

    /**
     * Method to save image as file
     *
     * @param resource $image Image resource
     *
     * @return string
     * @throws Exception
     */
    private function _saveImage($image)
    {
        $filename = \Mage::getBaseDir('media') . DS . 'tmp' . DS . uniqid() . '.jpeg';

        if (imagejpeg($image, $filename) === false) {
            throw new \Exception(\Mage::helper('amid_bundleimage')->__('Error saving image.'));
        }

        imagedestroy($image);

        return \Mage::getBaseUrl('web') . str_replace(\Mage::getBaseDir() . DS, '', $filename);
    }

    /**
     * Method to create initial image
     *
     * @param string $filename Filename of initial image
     *
     * @return resource
     */
    private function _buildInitialImage($filename)
    {
        // Get image dimensions
        list($width, $height) = getimagesize($filename);

        // Create image
        return imagecreatetruecolor($width, $height);
    }
}
