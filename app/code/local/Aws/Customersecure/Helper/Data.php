<?php

class Aws_Customersecure_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * default statuses constants
     */
    const ENABLED   = 1;
    const DISABLED  = 2;

    /**
     * default aliases of fields
     */
    const CMS       = 'cms';
    const EMAIL     = 'email';
    const CUSTOMER  = 'customer';

    public function getDomainFromEmail($email)
    {
        return substr($email, strpos($email, '@')+1);
    }

    public function getStatusArray()
    {
        return array(
            self::ENABLED  => Mage::helper('aws_customersecure')->__('Enabled'),
            self::DISABLED => Mage::helper('aws_customersecure')->__('Disabled')
        );
    }


    public function customToOptionArray($collection, $valueField='id', $labelField='name')
    {
        $res = array();

        $additional['value'] = $valueField;
        $additional['label'] = $labelField;

        foreach ($collection as $item) {
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
            $data['value'] = serialize(array($valueField => $data['value'],
                                             'title'     => $data['label']));

            $res[] = $data;
        }

        return $res;
    }

    public function getNormalArray($data)
    {
        $newData = array();

        $data = explode(',', $data);
        foreach ($data as $key => $value) {
            $newData[$key] = unserialize($value);
        }

        return $newData;
    }

}