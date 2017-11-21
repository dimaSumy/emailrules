<?php

class Aws_Customersecure_Block_Adminhtml_Email_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('aws_customersecure_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare our grid columns
     * Init collection
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('aws_customersecure/secure_collection');
        /* @var $collection Aws_Customersecure_Model_Resource_Secure_Collection */
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     * @return $this
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('aws_customersecure');

        $this->addColumn('entity_id', array(
            'header'    => $helper->__('ID'),
            'align'     => 'left',
            'width'     => '50px',
            'index'     => 'entity_id'
        ));

        $this->addColumn('rule_name', array(
            'header'    => $helper->__('Rule Name'),
            'align'     => 'left',
            'index'     => 'rule_name'
        ));

        $this->addColumn('code', array(
            'header'    => $helper->__('Code'),
            'align'     => 'left',
            'index'     => 'code'
        ));

        $this->addColumn('email_groups', array(
            'header'    => $helper->__('Email Groups'),
            'align'     => 'left',
            'index'     => 'email_groups',
            'renderer'  => 'aws_customersecure/adminhtml_email_renderer_pages'
        ));

        $this->addColumn('customer_groups', array(
            'header'    => $helper->__('Customer Groups'),
            'align'     => 'left',
            'index'     => 'customer_groups',
            'renderer'  => 'aws_customersecure/adminhtml_email_renderer_pages'
        ));

        $this->addColumn('cms_pages', array(
            'header'    => $helper->__('Cms Pages'),
            'align'     => 'left',
            'index'     => 'cms_pages',
            'renderer'  => 'aws_customersecure/adminhtml_email_renderer_pages'
        ));

        $this->addColumn('secure_rule', array(
            'header'    => $helper->__('Comment'),
            'align'     => 'left',
            'index'     => 'secure_rule'
        ));

        $this->addColumn('is_active', array(
            'header'    => $helper->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => $helper->getStatusArray()
        ));

        $this->addColumn('action',
            array(
                'header'    => $helper->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => $helper->__('Edit'),
                        'url'       => array(
                            'base'  => '*/*/edit',
                            /*'params'=> array('store'=>$this->getRequest()->getParam('store'))*/
                        ),
                        'field'     => 'entity_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores'
            ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare massaction blocks
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $helper = Mage::helper('aws_customersecure');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $helper->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'/*, array('' => '')*/),
            'confirm' => $helper->__('Are you sure?')
        ));

        $statuses = $helper->getStatusArray();

        //add empty array to get a comfort interface in admin
        array_unshift($statuses, array('value'=>'', 'label'=>''));
        $this->getMassactionBlock()->addItem('is_active', array(
            'label'     => $helper->__(('Change status')),
            'url'       => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional'=> array(
                'visibility'  => array(
                    'name'  => 'is_active',
                    'type'  => 'select',
                    'class' => 'required-entry',
                    'label' => $helper->__('Status'),
                    'values'=> $statuses
                )
            )
        ));

        return $this;
    }

    /**
     * Make a grid row clickable
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getId()));
    }
}