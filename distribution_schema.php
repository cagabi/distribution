<?php
$schema['distribution_users'] = array(
    'id' => array('type' => 'int(11)'),
    'role' => array('type' => 'varchar(64)'),
    'organizationid' => array('type' => 'varchar(64)')
);
$schema['distribution_organizations'] = array(
    'id' => array('type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Extra' => 'auto_increment'),
    'name' => array('type' => 'varchar(64)'),
);
$schema['distribution_points'] = array(
    'id' => array('type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Extra' => 'auto_increment'),
    'name' => array('type' => 'varchar(64)'),
    'organizationid' => array('type' => 'int(11)'),
);
