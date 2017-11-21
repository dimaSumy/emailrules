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

$tableName = $installer->getTable('aws_customersecure/aws_secure_rules');

$installer->getConnection()->changeColumn($tableName, 'email_groups', 'email_groups', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => null
));

$installer->getConnection()->changeColumn($tableName, 'cms_pages', 'cms_pages', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => null
));

$installer->getConnection()->changeColumn($tableName, 'customer_groups', 'customer_groups', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => null
));

$installer->endSetup();