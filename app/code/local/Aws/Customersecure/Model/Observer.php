<?php

class Aws_Customersecure_Model_Observer
{
    public function checkDomainExists(Varien_Event_Observer $observer)
    {
        $customer = $observer->getData('customer');
        $domain   = Mage::helper('aws_customersecure')->getDomainFromEmail($customer->getEmail());
        //check if domain extists. if not - add him
        $this->_isEmailGroupExists($domain);

        $ownRules = array();
        $rules = Mage::getResourceModel('aws_customersecure/secure_collection');

        foreach ($rules as $rule) {
            foreach ($rule->getEmailGroups() as $ruleGroup) {

                if ($domain == $ruleGroup['title']) {
                    $ownRules[] = $rule->getData();

                }
            }
        }
        $ownRules = serialize($ownRules);

        return $ownRules;
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
        $customer = $observer->getData('customer');

        /*$rules = Mage::getResourceModel('aws_customersecure/secure_collection');
        foreach ($rules as $rule) {
            $domains = $rule->getEmailGroups();

            foreach ($domains as $domain) {
                if ($customerDomain == $domain['title']) {

                }
            }
        }*/
        return;
    }
}