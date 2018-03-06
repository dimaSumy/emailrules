<?php

class Aws_Customersecure_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * default statuses constants
     */
    const ENABLED   = 1;
    const DISABLED  = 2;


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

    public function getGroupFilter($groups)
    {
        $groupFilter = [];
        foreach ($this->_getNormalArray($groups) as $group) {
            if ($group['customer_group_id'] != 0){
                $groupFilter[] = ['attribute' => 'group_id', 'eq' => $group['customer_group_id']];
            }
        }
        return $groupFilter;
    }

    public function getEmailFilter($emails)
    {
        $emailFilter = [];
        foreach ($this->_getNormalArray($emails) as $email) {
            $emailFilter[] = ['attribute' => 'email', 'like' => "%{$email['title']}%"];
        }
        return $emailFilter;
    }
}