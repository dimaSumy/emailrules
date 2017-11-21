<?php

class Aws_Customersecure_Block_Adminhtml_Email extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'aws_customersecure';
        $this->_controller = 'adminhtml_email';
        $this->_headerText = Mage::helper('aws_customersecure')->__('Customer Email Rules');
        $this->_addButtonLabel = Mage::helper('aws_customersecure')->__('Add New Rule');

        parent::__construct();
    }
}