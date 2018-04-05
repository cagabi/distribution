<?php

/*
  All Emoncms code is released under the GNU Affero General Public License.
  See COPYRIGHT.txt and LICENSE.txt.
  ---------------------------------------------------------------------
  Emoncms - open source energy visualisation
  Part of the OpenEnergyMonitor project: http://openenergymonitor.org
 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

function distribution_controller() {
    global $mysqli, $user, $session, $route;

    require_once "Modules/distribution/distribution_model.php";
    $distribution = new Distribution($mysqli, $user);

    // There are no actions in the distribution module that can be performed with less than write privileges
    if (!$session['write'])
        return array('content' => false);

    $result = false;

    $distro_user = $distribution->get_user($session['userid']);

    if ($route->format == 'html') {
        if ($route->action == 'admin') {
            if ($distro_user['role'] == 'administrator') {
                $orgs = $distribution->get_organizations_list($distro_user['userid']);
                $result = view("Modules/distribution/Views/admin_view.php", array('organizations' => $orgs));
            }
        }
    }
    else if ($route->format == 'json') {
        if ($route->action == "listorganizations") {
            if ($distro_user['role'] == 'administrator')
                $result = $distribution->get_organizations_list($distro_user['userid']);
        }
        if ($route->action == 'createorganization') {
            if ($distro_user['role'] == 'administrator')
                $result = $distribution->create_organization(get('name'));
        }
        if ($route->action == 'createuser') {
            if ($distro_user['role'] == 'administrator')
                $result = $distribution->create_user(get('name'), get('organizationid'), get('email'), post('password'), get('role'));
        }
    }

    return array('content' => $result);
}
