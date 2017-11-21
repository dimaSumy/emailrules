<?php

class Aws_Customersecure_Model_Resource_Email_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('aws_customersecure/email');
    }

    public function toOptionArray()
    {
        return $this->_toMyOptionArray($this, 'entity_id', 'email_group');
    }


    
    public function getCustomerEmailDomains()
    {
        $domains = array();
        $emailsCollection = Mage::getResourceModel('customer/customer_collection')->groupByEmail();

        foreach ($emailsCollection as $email) {
            $domains[] = Mage::helper('aws_customersecure')->getDomainFromEmail($email->getEmail());
        }
        $domains = array_unique($domains);

        return $domains;
    }

}