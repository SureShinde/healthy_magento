<?php

class Magik_Magikfees_Block_Adminhtml_Magikfees_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('magikfeesGrid');
		$this->setDefaultSort('magikfees_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('magikfees/magikfees')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('magikfees_id', array(
		'header'    => Mage::helper('magikfees')->__('ID'),
		'width'     => '200px',
		'align'     =>'right',
		'width'     => '50px',
		'index'     => 'magikfees_id',
		));
		
		$this->addColumn('magikfees_id', array(
		'header'    => Mage::helper('magikfees')->__('Fee Name'),
		'align'     =>'left',
		'index'     => 'title',	        
		));
		
		$this->addColumn('feetype', array(
		'header'    => Mage::helper('magikfees')->__('Type'),
		'align'     =>'left',
		'index'     => 'feetype',	        
		));	
		
		$this->addColumn('feeamount', array(
		'header'    => Mage::helper('magikfees')->__('Amount'),
		'align'     =>'left',
		'index'     => 'magikfees_id',	
		'renderer'  => new Magik_Magikfees_Block_Adminhtml_Renderer_Feedetail(),      
		));		
			
		$this->addColumn('apply_to', array(
		'header'    => Mage::helper('magikfees')->__('Applied to'),
		'width'     => '150px',
		'index'     => 'apply_to',		
		));
		
		$this->addColumn('mandatory', array(
		'header'    => Mage::helper('magikfees')->__('Is Mandatory'),
		'width'     => '150px',
		'index'     => 'mandatory',		
		));

		$this->addColumn('flatfee', array(
		'header'    => Mage::helper('magikfees')->__('Flat Fee'),
		'width'     => '150px',
		'index'     => 'flatfee',		
		));

		$this->addColumn('status', array(
		'header'    => Mage::helper('magikfees')->__('Status'),
		'width'     => '150px',
		'index'     => 'status',
		'type'      => 'options',
		'options'   => array(
				      1 => 'Enabled',
				      2 => 'Disabled',
				  ),
		));

		/*$this->addColumn('tax_apply', array(
		'header'    => Mage::helper('magikfees')->__('Tax to apply'),
		'width'     => '150px',
		'index'     => 'magikfees_id',	
		'renderer'  => new Magik_Magikfees_Block_Adminhtml_Renderer_Taxdetail(),	
		));*/

		$this->addColumn('action',
		array(
		'header'    =>  Mage::helper('magikfees')->__('Action'),
		'width'     => '100',
		'type'      => 'action',
		'getter'    => 'getId',
		'actions'   => array(
		array(
		'caption'   => Mage::helper('magikfees')->__('Edit'),
		'url'       => array('base'=> '*/*/edit'),
		'field'     => 'id'
		)
		),
		'filter'    => false,
		'sortable'  => false,
		'index'     => 'stores',
		'is_system' => true,
		));

		//$this->addExportType('*/*/exportCsv', Mage::helper('magikfees')->__('CSV'));
		//$this->addExportType('*/*/exportXml', Mage::helper('magikfees')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('magikfees_id');
		$this->getMassactionBlock()->setFormFieldName('magikfees');

		$this->getMassactionBlock()->addItem('delete', array(
		'label'    => Mage::helper('magikfees')->__('Delete'),
		'url'      => $this->getUrl('*/*/massDelete'),
		'confirm'  => Mage::helper('magikfees')->__('Are you sure?')
		));
		
		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

}
