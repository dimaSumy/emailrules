<?php

class Aws_Customersecure_Model_Secure extends Mage_Core_Model_Abstract
{
    const CMS_PAGES      = 'cms_pages';
    const EMAIL_GROUP    = 'email_groups';
    const CUSTOMER_GROUP = 'customer_groups';

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
}