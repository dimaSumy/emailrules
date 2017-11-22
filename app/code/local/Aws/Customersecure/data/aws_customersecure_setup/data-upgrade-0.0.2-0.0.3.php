<?php

$helper    = Mage::helper('aws_customersecure');
$customers = Mage::getResourceModel('customer/customer_collection');
$rules     = Mage::getResourceModel('aws_customersecure/secure_collection');

//add rules ids to customer attribute
foreach ($customers as $customer) {
    $ruleIds = array();
    foreach ($rules as $rule) {
        foreach ($rule->getEmailGroups() as $emailGroup) {
            if ($helper->getDomainFromEmail($customer->getEmail()) == $emailGroup['title']) {
                $ruleIds[] = $rule->getId();
            }
        }
    }
    if (!empty($ruleIds) && is_array($ruleIds)){

        $ruleIds = implode(',', $ruleIds);
        $customer->setEmailSecureRule($ruleIds)
                 ->save();
    }
}