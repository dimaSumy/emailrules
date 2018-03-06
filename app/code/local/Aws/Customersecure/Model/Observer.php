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
        $actionName = Mage::app()->getRequest()->getActionName();
        if ($actionName == 'createpost' || $actionName == 'editPost') {
            $customer = $observer->getCustomer();
            $domain   = Mage::helper('aws_customersecure')->getDomainFromEmail($customer->getEmail());
            //check if domain extists. if not - add him
            $this->_isEmailGroupExists($domain);
            //get rules collection and check for rules exists
            $rules   = Mage::getModel('aws_customersecure/secure')->getCollection();
            $ruleIds = array();
            //check if email domain is the same in email secure rule
            foreach ($rules as $rule) {
                if ($this->_checkRuleByDomain($rule, $domain) && $this->_checkRuleByGroup($rule, $customer)) {
                    $ruleIds[] = $rule->getId();
                }
            }
            // add rules to customer if exists
            $this->_addRulesToCustomer($customer, $ruleIds);
        }
        return $this;
    }

    /**
     * Check is page banned and redirect customer
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function showBannedPages(Varien_Event_Observer $observer)
    {
        $ids = array();
        /* @var $condition Varien_Object */
        $controller = $observer->getControllerAction();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $pageIdentifier = trim($controller->getRequest()->getRequestString(),'/');
        if ($pageIdentifier){
            $title = Mage::getModel('cms/page')->load($pageIdentifier, 'identifier')->getTitle();
            //get needed rules collection
            $rules = Mage::getSingleton('customer/session')->isLoggedIn()
                ? $this->_getCustomerRules($customer, $title)
                : $this->_getGuestRules($title);
            //if rules exist
            if ($rules->getSize()){
                foreach ($rules as $rule) {
                    $ids[] = $rule->getId();
                }
                Mage::getSingleton('customer/session')->setRules($ids);
                $controller->getResponse()
                    ->setRedirect(Mage::getUrl('customersecure/banned'))
                    ->sendResponse();
            }
        }
        return $this;
    }


    protected function _getCustomerRules($customer, $title)
    {
        $ruleIds = explode(',', $customer->getEmailSecureRule());
        $ruleIds = array_map('intval', $ruleIds);
        $collection = Mage::getModel('aws_customersecure/secure')
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('entity_id', array('in' => $ruleIds))
            ->addFieldToFilter('cms_pages', array('like' => "%$title%"));

        return $collection;
    }

    /**
     * @param $model
     * @param array $emailGroups
     * @param $domain
     * @return int
     */
    protected function _checkRuleByDomain(Aws_Customersecure_Model_Secure $rule, $domain)
    {
        foreach ($rule->getEmailGroups() as $emailGroup) {
            if ($domain == $emailGroup['title']) {
                return true;
            }
        }
        return false;
    }

    protected function _checkRuleByGroup(Aws_Customersecure_Model_Secure $rule, $customer)
    {
        foreach ($rule->getCustomerGroups() as $customerGroup) {
            $code = Mage::getModel('customer/group')->load($customer->getGroupId())->getCustomerGroupCode();
            if ($code == $customerGroup['title']) {
                return true;
            }
        }
        return false;
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
    protected function _getGuestRules($title)
    {
        $collection = Mage::getResourceModel('aws_customersecure/secure_collection')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('customer_groups', array('like'=>'%NOT LOGGED IN%'))
            ->addFieldToFilter('cms_pages', array('like' => "%$title%"));

        return $collection;
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
            $customer->setEmailSecureRule($ruleIds);
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
        return $param != 2;
    }
}