<?php

    $domain = "messages";
    bindtextdomain($domain, "Modules/schedule/locale");
    bind_textdomain_codeset($domain, 'UTF-8');

    $menu_dropdown[] = array('name'=> dgettext($domain, "Admin users and orgs"),'icon'=>'icon-user', 'path'=>"distribution/admin" , 'session'=>"write", 'order' => 1);
