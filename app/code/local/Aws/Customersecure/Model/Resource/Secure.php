<?php

class Aws_Customersecure_Model_Resource_Secure extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('aws_customersecure/aws_secure_rules', 'entity_id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {

        $helper = Mage::helper('aws_customersecure');

        if ($object->getId()) {
            if (Mage::app()->getRequest()->getActionName() == 'edit') {
                $helper->getUnpackedData($object, true);
            } else {
                $helper->getUnpackedData($object);
            }
        }

        return $this;
    }
}