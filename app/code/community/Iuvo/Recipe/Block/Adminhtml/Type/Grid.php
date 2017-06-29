<?php

class Iuvo_Recipe_Block_Adminhtml_Type_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('typeGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('recipe/type')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      
//	  Other Columns: action, entity_id, etc...
  	
      $this->addColumn('type_id', array(
          'header'    => Mage::helper('recipe')->__('ID'),
          'align'     =>'left',
      	  'width'     => '100px',
          'index'     => 'type_id',
      ));
      $this->addColumn('dishtype', array(
			'header'    => Mage::helper('recipe')->__('Dish Type'),
      		'align'     =>'left',
			'index'     => 'dishtype',
      ));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('recipe');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('recipe')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('recipe')->__('Are you sure?')
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}