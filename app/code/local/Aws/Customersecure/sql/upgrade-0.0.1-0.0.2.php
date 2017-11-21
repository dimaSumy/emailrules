<?php
/**
 * Absolute Web Services Intellectual Property
 *
 * @category     {Aws/FlexFooter}
 * @copyright    Copyright © 1999-2017 Absolute Web Services, Inc. (http://www.absolutewebservices.com)
 * @author       Absolute Web Services
 * @license      http://www.absolutewebservices.com/license-agreement/  Single domain license
 * @terms of use http://www.absolutewebservices.com/terms-of-use/
 */
/**
 * @var $this Mage_Customer_Model_Entity_Setup
 */
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId       = $setup->getEntityTypeId('customer');
$attributeSetId     = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId   = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$code               = 'email_secure_rule';

if (Mage::getResourceModel('customer/eav_attribute')->loadByCode('customer', $code)->getId()) {
    $setup->removeAttribute('customer', $code);
}

$installer->addAttribute('customer', 'rule_id', array(
    'label'             => 'Email Secure Rule',
    'type'              => 'varchar',
    'input'             => '',
    'backend'           => '',
    'frontend'          => '',
    /*'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,*/
    'source'            => '',
    'visible'           => true,
    'required'          => false,
    'default'           => '',
    'unique'            => false,
    'note'              => 'Secure rule'
));

$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'email_secure_rule');

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'email_secure_rule',
    '150' //sort_order
);

$installer->endSetup();
