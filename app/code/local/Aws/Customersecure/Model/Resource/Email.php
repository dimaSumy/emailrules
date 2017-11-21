<?php

class Aws_Customersecure_Model_Resource_Email extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('aws_customersecure/aws_email_domain', 'entity_id');
    }

}