<?php

class Aws_Customersecure_Block_Secure extends Mage_Core_Block_Template
{
    /**
     * Get an error messages for customer
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = array();
        $session = Mage::getSingleton('customer/session');
        foreach ($session->getRules() as $rule) {
            //add message only for banned pages
            if (in_array($session->getPageId(), $rule['cms_pages'])) {
                $messages[] = $rule['comment'];
            }
        }
        return $messages;
    }

}