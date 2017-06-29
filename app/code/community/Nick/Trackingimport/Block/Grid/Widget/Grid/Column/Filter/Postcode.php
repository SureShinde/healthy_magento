<?php
class Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Filter_Postcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders products data
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        return str_replace('###', '<br/>',$row->getPostcode());
                
    }
}
?>
