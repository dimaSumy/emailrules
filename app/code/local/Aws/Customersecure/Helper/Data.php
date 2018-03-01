<?php

class Aws_Customersecure_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * default statuses constants
     */
    const ENABLED   = 1;
    const DISABLED  = 2;

    protected $_customerGroups;
    protected $_emailGroups;

    /**
     * Get email domain string
     * @param $email
     * @return bool|string
     */
    public function getDomainFromEmail($email)
    {
        return substr($email, strpos($email, '@')+1);
    }

    /**
     * Get values for Status "select" field
     * @return array
     */
    public function getStatusArray()
    {
        return [
            self::ENABLED  => Mage::helper('aws_customersecure')->__('Enabled'),
            self::DISABLED => Mage::helper('aws_customersecure')->__('Disabled')
        ];
    }


    /**
     * Get needed multiselect values based on collection
     * @param $collection
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    public function customToOptionArray($collection, $valueField='id', $labelField='name')
    {
        $res = [];

        $additional['value'] = $valueField;
        $additional['label'] = $labelField;

        foreach ($collection as $item) {
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
            $data['value'] = serialize( array($valueField => $data['value'],
                                             'title'     => $data['label']));

            $res[] = $data;
        }

        return $res;
    }

    /**
     * Unserialize db data
     * @param $data
     * @return array
     */
    protected function _getNormalArray($data)
    {
        $newData = [];

        $data = explode(',', $data);
        foreach ($data as $key => $value) {
            $newData[$key] = unserialize($value);
        }

        return $newData;
    }

    /**
     * Get values after load model/collection
     * @param $model
     * @param bool $form
     * @return mixed
     */
    public function getUnpackedData($model, $form = false)
    {
        $model->setData(Aws_Customersecure_Model_Secure::CMS_PAGES,
                        $form ? explode(',', $model->getCmsPages()) : $this->_getNormalArray($model->getCmsPages()))
              ->setData(Aws_Customersecure_Model_Secure::EMAIL_GROUP,
                        $form ? explode(',', $model->getEmailGroups()) : $this->_getNormalArray($model->getEmailGroups()))
              ->setData(Aws_Customersecure_Model_Secure::CUSTOMER_GROUP,
                        $form ? explode(',', $model->getCustomerGroups()) : $this->_getNormalArray($model->getCustomerGroups()));

        return $model;
    }

    public function saveChangedAttributes(Aws_Customersecure_Model_Secure $rule)
    {
        $this->_customerGroups = $rule->getCustomerGroups();
        $this->_emailGroups = $rule->getEmailGroups();

        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->load();

        foreach ($customers as $customer) {
            $code = Mage::getModel('customer/group')->load($customer->getGroupId())->getCustomerGroupCode();
            $domain = $this->getDomainFromEmail($customer->getEmail());
            $ruleIds = explode(',', $customer->getEmailSecureRule());

            if (is_null($customer->getEmailSecureRule())) {
                $ruleIds = [];
            }
            $key = array_search($rule->getId(), $ruleIds);

            if (is_int($key) && $this->_match($code, $domain)
                || $key === false && !$this->_match($code, $domain)) {
                continue;
            }
            if (is_int($key)){
                unset($ruleIds[$key]);
            } else {
                array_push($ruleIds, $rule->getId());
            }
            $ruleIds = implode(',', $ruleIds);
            $customer->setEmailSecureRule($ruleIds)->save();
        }
    }

    protected function _match($customerGroup, $domain)
    {
        return (strpos($this->_customerGroups, $customerGroup) !== false) && (strpos($this->_emailGroups, $domain) !== false);
    }
}