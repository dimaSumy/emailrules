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
 * @var $this Mage_Core_Model_Resource_Setup
 */

$installer = $this;

$tableName = $installer->getTable('aws_customersecure/aws_email_domain');

if ($installer->getConnection()->isTableExists($tableName)){
    $installer->getConnection()->dropTable($tableName);
}

$tableEmail = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true
    ), 'Entity ID')
    ->addColumn('email_group', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false
    ), 'Email Group')
    ->setComment('Email Domain Table');

$installer->getConnection()->createTable($tableEmail);

$tableName = $installer->getTable('aws_customersecure/aws_secure_rules');

if ($installer->getConnection()->isTableExists($tableName)){
    $installer->getConnection()->dropTable($tableName);
}

$tableSecure = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true
    ), 'Entity ID')
    ->addColumn('rule_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Rule Name')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Code')
    ->addColumn('email_groups', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true
    ))
    ->addColumn('customer_groups', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false
    ), 'Customer Group')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
        'nullable'  => false,
        'default'   => 2
    ), 'Status')
    ->addColumn('cms_pages', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Cms Pages')
    ->addColumn('secure_rule', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Permission rules')
    ->setComment('Secure Rules Table');

$installer->getConnection()->createTable($tableSecure);
$installer->endSetup();