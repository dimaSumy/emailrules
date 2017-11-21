<?php
$emailDomains = Mage::getModel('aws_customersecure/email')->getDomains();

foreach ($emailDomains as $emailDomain) {
    Mage::getModel('aws_customersecure/email')
        ->setEmailGroup($emailDomain)
        ->save();
}