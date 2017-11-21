<?php

class Aws_Customersecure_Model_Email extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('aws_customersecure/email');
    }

    public function getDomains()
    {
        $collection = $this->getCollection();
        return $collection->getCustomerEmailDomains();
    }

}