<?php

class Aws_Customersecure_Model_Resource_Secure_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('aws_customersecure/secure');
    }

    protected function _afterLoad()
    {
        $helper = Mage::helper('aws_customersecure');

        foreach ($this as $model) {
            $helper->getUnpackedData($model);
        }
        return parent::_afterLoad();
    }

}