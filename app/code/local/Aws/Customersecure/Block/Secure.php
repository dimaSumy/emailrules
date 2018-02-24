<?php

class Aws_Customersecure_Block_Secure extends Mage_Core_Block_Template
{
    protected $_rules;

    /**
     * Get an error messages for customer
     * @return array
     */
    public function getRules()
    {
        if (!isset($this->_rules)){
            $this->_getRules();
        }
        return $this->_rules;
    }

    protected function _getRules()
    {
        $ruleIds = array_map('intval', Mage::getSingleton('customer/session')->getRules());
        $rules = Mage::getModel('aws_customersecure/secure')
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('entity_id', array('in' => $ruleIds));
        $this->_rules = $rules;
    }
}