<?php

$schema['distribution_users'] = array(
    'id' => array('type' => 'int(11)', 'Null' => false),
    'role' => array('type' => 'varchar(64)', 'Null' => false, 'default' => 'prepvol'),
    'organizationid' => array('type' => 'varchar(64)', 'Null' => false)
);
$schema['distribution_organizations'] = array(
    'id' => array('type' => 'int(11)', 'Null' => false, 'Key' => 'PRI', 'Extra' => 'auto_increment'),
    'name' => array('type' => 'varchar(64)'),
);
$schema['distribution_points'] = array(
    'id' => array('type' => 'int(11)', 'Null' => false, 'Key' => 'PRI', 'Extra' => 'auto_increment'),
    'name' => array('type' => 'varchar(64)'),
    'organizationid' => array('type' => 'int(11)', 'Null' => false),
);
$schema['distribution_items'] = array(
    'id' => array('type' => 'int(11)', 'Null' => false, 'Key' => 'PRI', 'Extra' => 'auto_increment'),
    'name' => array('type' => 'varchar(64)'),
    'regular' => array('type' => 'tinyint(1)', 'Null' => false, 'default' => '0'),
    'deleted' => array('type' => 'tinyint(1)', 'Null' => false, 'default' => '0'),
);
$schema['distribution_distributions'] = array(
    'date' => array('type' => 'date', 'Null' => false, 'Index' => true),
    'itemid' => array('type' => 'int(11)', 'Index' => true),
    'quantity' => array('type' => 'int(11)'),
    'distribution_point_id' => array('type' => 'int(11)', 'Index' => true),
);
$schema['distribution_preparation'] = array(
    'date' => array('type' => 'date', 'Null' => false, 'Index' => true),
    'itemid' => array('type' => 'int(11)', 'Index' => true),
    'distribution_point_id' => array('type' => 'int(11)', 'Index' => true),
    'quantity_out' => array('type' => 'int(11)'),
    'quantity_returned' => array('type' => 'int(11)', 'Null' => true),
);
