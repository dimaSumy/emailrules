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

        $rules   = Mage::getModel('aws_customersecure/secure')->getCollection();
        $ruleIds = array();

        //check if email domain is the same in email secure rule
        foreach ($rules as $rule) {
            if ($result = $this->_checkRuleByDomain($rule, $domain)) {
                $ruleIds[] = $result;
            }
        }
        // add rules to customer if exists
        $this->_addRulesToCustomer($customer, $ruleIds);

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
            $rules = Mage::getSingleton('customer/session')->isLoggedIn()
                ? $this->_getCustomerRules($customer, $title)
                : $this->_getGuestRules($title);
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
    protected function _checkRuleByDomain($rule, $domain)
    {
        $id = '';
        foreach ($rule->getEmailGroups() as $emailGroup) {
            if ($domain == $emailGroup['title']) {
                $id = $rule->getId();
                return (int)$id;
            }
        }
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
//        $guestRules = array();
        $collection = Mage::getResourceModel('aws_customersecure/secure_collection')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('customer_groups', array('like'=>'%NOT LOGGED IN%'))
            ->addFieldToFilter('cms_pages', array('like' => "%$title%"));

//        foreach ($collection as $item) {
//            $guestRules[] = $this->_getRuleForSession($item);
//        }
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