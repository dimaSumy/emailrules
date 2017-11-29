<?php

class Aws_Customersecure_Model_Observer
{
    /**
     * Check for domain exists and write data in database
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function checkDomainExists(Varien_Event_Observer $observer)
    {
        $customer = $observer->getCustomer();
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
     * Add rule in customer session
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addRuleToSession(Varien_Event_Observer $observer)
    {
        $customer      = $observer->getCustomer();
        $emailRules    = explode(',', $customer->getEmailSecureRule());
        $domain        = Mage::helper('aws_customersecure')->getDomainFromEmail($customer->getEmail());

        $customerRules = array();
        foreach ($emailRules as $rule) {
            $model = Mage::getModel('aws_customersecure/secure')->load($rule);
            //check for available
            if ($this->_match($model->getEmailGroups(), $domain, 'title')
                && $this->_match($model->getCustomerGroups(), $customer->getGroupId(), 'customer_group_id')
                && $this->_isActive($model->getIsActive())) {
                //set rules
                    $customerRules[] = $this->_getRuleForSession($model);
            }
        }
        //Add rules to session
        Mage::getSingleton('customer/session')->setRules($customerRules);

        return $this;
    }

    /**
     * Check is page banned and redirect customer
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function showBannedPages(Varien_Event_Observer $observer)
    {
        $controller = $observer->getControllerAction();
        /* @var $controller Mage_Cms_PageController */
        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()){
            if (!$session->hasRules()){
                $session->setRules($this->_getGuestRules());
            }
        }
        //check if homepage
        $pageId = $controller->getRequest()->getActionName() == 'index'
            ? Mage::helper('aws_customersecure')->getHomePageId()
            : $controller->getRequest()->getParams()['page_id'];

        if ($this->_pageBannedForCustomer($pageId)){
            $session->setPageId($pageId);
            //redirect customer
            $controller->getResponse()
                ->setRedirect(Mage::getUrl('customersecure/banned'))
                ->sendResponse();
        }
        return $this;
    }

    /**
     * Delete rules from session after logout
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function deleteRules(Varien_Event_Observer $observer)
    {
        Mage::getSingleton('customer/session')->unsetData('rules');

        return $this;
    }

    /**
     * @param $model
     * @param array $emailGroups
     * @param $domain
     * @return int
     */
    protected function _checkRuleByDomain($model, array $emailGroups, $domain)
    {
        $id = '';
        foreach ($emailGroups as $emailGroup) {
            if ($domain == $emailGroup['title']) {
                $id = $model->getId();
            }
        }
        return (int)$id;
    }

    /**
     * Check is needed cms page has banned
     * @param $pageId
     * @return bool
     */
    protected function _pageBannedForCustomer($pageId)
    {
        foreach (Mage::getSingleton('customer/session')->getRules() as $rule) {
            if (in_array($pageId, $rule['cms_pages'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get rules for guests
     * @return array
     */
    protected function _getGuestRules()
    {
        $guestRules = array();
        $collection = Mage::getResourceModel('aws_customersecure/secure_collection')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('customer_groups', array('like'=>'%NOT LOGGED IN%'));

        foreach ($collection as $item) {
            $guestRules[] = $this->_getRuleForSession($item);
        }
        return $guestRules;
    }

    /**
     * Get compact secure rule data
     * @param $model
     * @return array
     */
    protected function _getRuleForSession($model)
    {
        $rule = array(
            'cms_pages' => $this->_getPagesIds($model->getCmsPages()),
            'comment'   => $model->getSecureRule()
        );
        return $rule;
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
     * @param $domain
     * @return bool|void
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
        return ;
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

    /**
     * Check is rule active now
     * @param $param
     * @return bool
     */
    protected function _isActive($param)
    {
        return ($param != 2) ? true : false;
    }

    /**
     * Check is matched field value
     * @param $fields
     * @param $matched
     * @param $key
     * @return bool
     */
    protected function _match($fields, $matched, $key)
    {
        foreach ($fields as $field) {
            if ($matched == $field[$key]){
                return true;
            }
        }
        return false;
    }

    /**
     * Get an array of page ids
     * @param $pages
     * @return array
     */
    protected function _getPagesIds($pages)
    {
        $ids = array();
        foreach ($pages as $page) {
            $ids[] = $page['page_id'];
        }
        return $ids;
    }

}