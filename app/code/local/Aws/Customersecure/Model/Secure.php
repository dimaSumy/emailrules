<?php

class Aws_Customersecure_Model_Secure extends Mage_Core_Model_Abstract
{
    /**
     * Default aliases of fields
     */
    const CMS_PAGES      = 'cms_pages';
    const EMAIL_GROUP    = 'email_groups';
    const CUSTOMER_GROUP = 'customer_groups';


    protected $_customerGroups;
    protected $_emailGroups;

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('aws_customersecure/secure');
    }

    /**
     * Make changes to comfortable keeping data in db
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (Mage::app()->getRequest()->getActionName() == 'massStatus') {

            $this->_serializeArray(array(
                self::CMS_PAGES,
                self::EMAIL_GROUP,
                self::CUSTOMER_GROUP
            ));
        }

        $this->setData(self::CMS_PAGES, implode(',', $this->getData(self::CMS_PAGES)))
             ->setData(self::EMAIL_GROUP, implode(',',$this->getData(self::EMAIL_GROUP)))
             ->setData(self::CUSTOMER_GROUP, implode(',',$this->getData(self::CUSTOMER_GROUP)));

        return parent::_beforeSave();
    }

    /**
     * Set data with serialized values
     * @param array $keys
     * @return $this
     */
    protected function _serializeArray(array $keys)
    {
        foreach ($keys as $key)  {
            $this->setData($key, $this->_serializeValues( $this->getData($key) ));
        }

        return $this;
    }

    /**
     * Get an array with serialized values
     * @param $data
     * @return array
     */
    protected function _serializeValues(array $data)
    {
        $arr = array();

        foreach ($data as $key => $value) {
            $arr[$key] = serialize($value);
        }

        return $arr;
    }

    public function saveChangedAttributes(Aws_Customersecure_Helper_Data $helper)
    {
        $this->_customerGroups = $this->getCustomerGroups();
        $this->_emailGroups = $this->getEmailGroups();

        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*');

        foreach ($customers as $customer) {
            $code = Mage::getModel('customer/group')->load($customer->getGroupId())->getCustomerGroupCode();
            $domain = $helper->getDomainFromEmail($customer->getEmail());
            $ruleIds = explode(',', $customer->getEmailSecureRule());

            if (is_null($customer->getEmailSecureRule())) {
                $ruleIds = [];
            }
            $key = array_search($this->getId(), $ruleIds);

            if (is_int($key) && $this->_match($code, $domain)
                || $key === false && !$this->_match($code, $domain)) {
                continue;
            }
            if (is_int($key)){
                unset($ruleIds[$key]);
            } else {
                array_push($ruleIds, $this->getId());
            }
            $ruleIds = implode(',', $ruleIds);
            $customer->setEmailSecureRule($ruleIds)->save();
        }
    }

    public function deleteRuleFromAttribute()
    {
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('email_secure_rule')
            ->addAttributeToFilter('email_secure_rule', ['like' => "%{$this->getId()}%"]);

        foreach ($customers as $customer) {
            $ruleIds = explode(',', $customer->getEmailSecureRule());
            if (count($ruleIds) > 1) {
                $key = array_search($this->getId(), $ruleIds);
                unset($ruleIds[$key]);
            } else {
                $ruleIds = [];
            }
            $ruleIds = implode(',', $ruleIds);
            $customer->setEmailSecureRule($ruleIds)->save();
        }
    }

    public function addRuleToAttribute(Aws_Customersecure_Helper_Data $helper)
    {
        $groupFilter = $helper->getGroupFilter($this->getCustomerGroups());
        $emailFilter = $helper->getEmailFilter($this->getEmailGroups());

        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter($groupFilter)
            ->addAttributeToFilter($emailFilter);

        foreach ($customers as $customer) {
            $ruleIds = explode(',', $customer->getEmailSecureRule());
            if (is_null($customer->getEmailSecureRule())) {
                $ruleIds = [];
            }
            array_push($ruleIds, $this->getId());
            $ruleIds = implode(',', $ruleIds);
            $customer->setEmailSecureRule($ruleIds)->save();
        }
    }

    protected function _match($customerGroup, $domain)
    {
        return strpos($this->_customerGroups, $customerGroup) !== false
            && strpos($this->_emailGroups, $domain) !== false;
    }
}