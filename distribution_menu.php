<?php

$domain = "messages";
bindtextdomain($domain, "Modules/schedule/locale");
bind_textdomain_codeset($domain, 'UTF-8');

global $mysqli, $user, $session;
require_once "Modules/distribution/distribution_model.php";
$distribution = new Distribution($mysqli, $user);

$distro_user = $distribution->get_user($session['userid']);
if ($distro_user['role'] == 'administrator')
    $menu_dropdown[] = array('name' => dgettext($domain, "Administration"), 'icon' => 'icon-user', 'path' => "distribution/admin", 'session' => "write", 'order' => 1);

$menu_dropdown[] = array('name' => dgettext($domain, "Preparation"), 'icon' => '', 'path' => "distribution/preparation", 'session' => "write", 'order' => 1);
