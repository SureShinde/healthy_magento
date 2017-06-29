<?php

class Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Renderer_Tracking extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	
    public function render(Varien_Object $row){		
		$rowId = $row->getId();
		$html = '';	
		/////Get Most Recent Shipment
		$RecentShipment = $this->getShipment($rowId);

		if ($RecentShipment == NULL) return;
		
		foreach ($RecentShipment as $shipment) {
		
			$shipmentId = $shipment->getId();
			$trackinginfo = '';
			
			foreach ($this->getCarriers() as $_code=>$_name) {
						$carriers .= '<option value="'.$_code.'">'.$_name.'</option>';
			}
			
			if($_tracks = $shipment->getAllTracks()){
			$i=0;
		
				foreach ($_tracks as $_track){
					$i++;
				$trackinginfo .= '<a href="'.$this->getUrl('adminhtml/sales_order_shipment/print', array('invoice_id' => $shipment->getId())).'">'.$this->__('Shipment %s', $shipment->getIncrementId()).'</a><br />';	
				$trackinginfo .= '<tbody><tr class="odd"><td>'.$this->getCarrierTitle($_track->getCarrierCode()).'</td>';
				$trackinginfo .= '<td>'.$_track->getTitle().'</td>';
				if($_track->isCustom())	$trackinginfo .=  '<td>'. $_track->getNumber().'</td>';
				else $trackinginfo .= '<td><a href="#" onclick="popWin(\''.$this->getTrackInfoUrl($_track, $shipmentId).'\',\'trackorder\',\'width=800,height=600,resizable=yes,scrollbars=yes\')">'.$_track->getNumber().'</a><div id="shipment_tracking_info_response_'.$_track->getId().'"></div></td>';
				//$trackinginfo .= '<td class="last"><a href="#" onclick="deleteTrackingNumber(\''.$this->getRemoveUrl($_track, $shipmentId).'\'); return false;">Delete</a></td></tr></tbody>';
				}
			}
			
			$url = $this->getUrl('trackingimport/grid/addTrack', array('shipment_id' => $shipmentId));
			$html .= '
				<div class="field-row grid" id="shipment_tracking_info_'.$rowId.'">
					<table cellspacing="0" class="data">
						<col width="100" />
						<col />
						<col />
						<col width="80" />
						<thead>
							<tr class="headings">
								<th>Carrier</th>
								<th>Title</th>
								<th>Number</th>
								<th class="last">Action</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td>
									<select name="carrier" class="select" style="width:110px" onchange="selectCarrier_'.$rowId.'(this)">
									   '.$carriers.'
									</select>
								</td>
								<td><input class="input-text" type="text" id="tracking_title_'.$rowId.'" name="title" value="" /></td>
								<td><input class="input-text" type="text" id="tracking_number" name="number" value="" /></td>
								<td class="last"><input type="button" class="form-button" onclick ="submitAndReloadArea($(\'shipment_tracking_info_'.$rowId.'\').parentNode,\''.$url.'\')" value="Save" \/></td>			
							</tr>
						</tfoot>
			'.$trackinginfo.'
					</table>
				</div>
	
				<script type="text/javascript">
					
					function selectCarrier_'.$rowId.'(elem) {
						option = elem.options[elem.selectedIndex];
						if (option.value && option.value != \'custom\') {
							$(\'tracking_title_'.$rowId.'\').value = option.text;
						}
						else {
							$(\'tracking_title\').value = \'\';
						}
					}
					
					function deleteTrackingNumber(url) {
						if (confirm(\'Are you sure?\')) {
							submitAndReloadArea($(\'shipment_tracking_info\').parentNode, url)
						}
					}
					
				</script>';
			}
		return $html;
	}
	
	
	public function getShipment($rowId){	        
    	
		
		
		$shipments = NULL;
		$shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($rowId);
        
		if(!$this->getTrackingGridConfig('shipments'))$shipments->setOrder('created_at', 'DESC')->setPageSize(1); 
					
		$shipments->load();
		
		return $shipments;
	}

	public function getCarriers(){

        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers();
        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
			
			foreach ($carrierInstances as $code => $carrier) {
				if ($carrier->isTrackingAvailable()) {
					$carriers[$code] = $carrier->getConfigData('title');
				}
			}
			
        return $carriers;
    }
	
	public function getCarrierTitle($code){
        if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
            return $carrier->getConfigData('title');
        }
        else {
            return Mage::helper('sales')->__('Custom Value');
        }
        return false;
    }
	
	public function getTrackInfoUrl($track, $shipmentId){
        return $this->getUrl('*/*/viewTrack/', array(
            'shipment_id' => $shipmentId,
            'track_id' => $track->getId()
        ));
    }
	
	public function getRemoveUrl($track, $shipmentId){
        return $this->getUrl('*/*/removeTrack/', array(
            'shipment_id' => $shipmentId,
            'track_id' => $track->getId()
        ));
    }
	
	 public function getSubmitUrl($shipmentId){
        return $this->getUrl('*/*/addTrack/', array('shipment_id'=>$shipmentId));
    }
	
	public function getTrackingGridConfig($option){
		return Mage::getStoreConfig('trackingimport/trackinggrid/'.$option);
	}
}
