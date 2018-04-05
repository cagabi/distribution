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

    $role = $distribution->get_role($session['userid']);
    if ($role == 'prepvol') {
        $organization = $distribution->get_organization($session['userid']);
    }

    if ($route->format == 'html') {
        if ($role == 'administrator') {
            if ($route->action == 'admin') {
                $orgs = $distribution->get_organizations_list($session['userid']);
                $result = view("Modules/distribution/Views/admin_view.php", array('organizations' => $orgs));
            }
        }
    }
    else if ($route->format == 'json') {
        if ($role == 'administrator') {
            if ($route->action == "listorganizations")
                $result = $distribution->get_organizations_list($session['userid']);
            if ($route->action == 'createorganization') {
                $result = $distribution->create_organization(get('name'));
            }
            if ($route->action == 'createuser') {
                $result = $distribution->create_user(get('name'),get('organizationid'),get('email'),post('password'),get('role'));
            }
        }
    }

    return array('content' => $result);
}
