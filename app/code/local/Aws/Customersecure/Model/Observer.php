<?php

class Aws_Customersecure_Model_Observer
{
    public function checkDomainExists(Varien_Event_Observer $observer)
    {
        $customer = $observer->getData('customer');
        $domain   = Mage::helper('aws_customersecure')->getDomainFromEmail($customer->getEmail());
        //check if domain extists. if not - add him
        $this->_isEmailGroupExists($domain);

        $rules   = Mage::getResourceModel('aws_customersecure/secure_collection');
        $ruleIds = array();

        //check if email domain is the same in email secure rule
        foreach ($rules as $rule) {
            $ruleIds[] = $this->_checkRuleByDomain($rule, $rule->getEmailGroups(), $domain);
        }

        // add rules to customer if exists
        $this->_addRulesToCustomer($customer, $ruleIds);

        return $this;
    }

    /**
     * @param $model Aws_Customersecure_Model_Secure
     * @param array $emailGroups
     * @param $domain
     * @return array
     */
    protected function _checkRuleByDomain($model, array $emailGroups, $domain)
    {
        $id = '';
        foreach ($emailGroups as $emailGroup) {
            if ($domain == $emailGroup['title']) {
                $id = $model->getId();
            }
        }
        return $id;
    }

    /**
     * @param $customer
     * @param array $ruleIds
     * @return $this
     */
    protected function _addRulesToCustomer($customer, array $ruleIds)
    {
        if (!empty($ruleIds) && is_array($ruleIds)) {
            $ruleIds = implode(',', $ruleIds);
            $customer->setEmailSecureRule($ruleIds)->save();
        }

        return $this;
    }

    /**
     * Checks is email group of registered user already exists
     * @param $domain
     */
    protected function _isEmailGroupExists($domain)
    {
        $model = Mage::getModel('aws_customersecure/email');

        foreach ($model->getCollection() as $item) {
            if ($item->getEmailGroup() == $domain){
                return true;
            }
        }
        $this->_addDomain($model, $domain);
    }

    /**
     * Add domain into database
     * @param $model
     * @param $domain
     */
    protected function _addDomain($model, $domain)
    {
        $model->setEmailGroup($domain)
              ->save();
        return ;
    }

    public function doSomething(Varien_Event_Observer $observer)
    {
        /*$customer = $observer->getData('customer');
        $customerRules = explode(',', $customer->getEmailSecureRule());
        foreach ($customerRules as $rule) {
            $model = Mage::getModel('aws_customersecure/secure')->load($rule);

        }
        return;*/
    }
}