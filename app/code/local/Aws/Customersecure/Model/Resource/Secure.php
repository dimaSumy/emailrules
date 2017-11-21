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
        if ($object->getId()){
            $object->setData(Aws_Customersecure_Model_Secure::CMS_PAGES, $helper->getNormalArray($object->getCmsPages()))
                    ->setData(Aws_Customersecure_Model_Secure::EMAIL_GROUP, $helper->getNormalArray($object->getEmailGroups()))
                    ->setData(Aws_Customersecure_Model_Secure::CUSTOMER_GROUP, $helper->getNormalArray($object->getCustomerGroups()));
        }
        return $this;
    }
}