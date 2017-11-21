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
            $model->setData(Aws_Customersecure_Model_Secure::CMS_PAGES, $helper->getNormalArray($model->getCmsPages()))
                  ->setData(Aws_Customersecure_Model_Secure::EMAIL_GROUP, $helper->getNormalArray($model->getEmailGroups()))
                  ->setData(Aws_Customersecure_Model_Secure::CUSTOMER_GROUP, $helper->getNormalArray($model->getCustomerGroups()));
        }

        return parent::_afterLoad();
    }

}