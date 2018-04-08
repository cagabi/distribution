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

    if ($session['admin'] == 1) {
        $role = Roles::SUPERADMINISTRATOR;
        $organizationid = 0;
    }
    else {
        $distro_user = $distribution->get_user($session['userid']);
        if (!$distro_user)
            return array('content' => false);
        $role = $distro_user['role'];
        $organizationid = $distro_user['organizationid'];
    }

    if ($route->format == 'html') {
        if ($route->action == 'admin') {
            if ($role == Roles::SUPERADMINISTRATOR || $role == Roles::ADMINISTRATOR) {
                $result = view("Modules/distribution/Views/admin_view.php", array());
            }
        }
        if ($route->action == 'preparation') { // Everybody can get to the preparation page
            if ($role == Roles::SUPERADMINISTRATOR)
                $orgs = $distribution->get_organizations();
            if ($role == Roles::ADMINISTRATOR) {
                $orgs = array($distribution->get_organization($organizationid)); //We put it in an array so it has the same structure than the one returned by $distribution->get_organizations()
            }$result = view("Modules/distribution/Views/preparation_view.php", array('organizations' => $orgs, 'organizationid' => $organizationid));
        }
    }
    else if ($route->format == 'json') {
        if ($route->action == "listorganizations") {
            if ($role == Roles::SUPERADMINISTRATOR)
                $result = $distribution->get_organizations();
            if ($role == Roles::ADMINISTRATOR) {
                $org = $distribution->get_organization($organizationid);
                $result = array($org);
            }
        }
        // Carry on here
        if ($route->action == 'createorganization') {
            if ($role == Roles::SUPERADMINISTRATOR || $role == Roles::ADMINISTRATOR)
                $result = $distribution->create_organization(get('name'));
        }
        if ($route->action == 'createuser') {
            if ($role == Roles::SUPERADMINISTRATOR || ($role == Roles::ADMINISTRATOR && $distribution->user_is_in_organization($session['userid'], $organizationid)))
                $result = $distribution->create_user(get('name'), get('organizationid'), get('email'), post('password'), get('role'));
        }
        if ($route->action == 'createdistributionpoint') {
            if ($role == Roles::SUPERADMINISTRATOR || ($role == Roles::ADMINISTRATOR && $distribution->user_is_in_organization($session['userid'], $organizationid)))
                $result = $distribution->create_distribution_point(get('name'), get('organizationid'));
        }
        if ($route->action == 'getitems') {
            $result = $distribution->get_items();
        }
        
        // Distribution preparation
        $distributionid = get('distributionid');
        $organizationid = $distribution->get_distribution_organization($distributionid);
        if ($role == Roles::SUPERADMINISTRATOR || $distribution->user_is_in_organization($session['userid'], $organizationid)) {
            if ($route->action == 'getyesterdaypreparation') {
                    $result = $distribution->get_yesterday_preparation($distributionid);
            }
            if ($route->action == 'savereturneditem') {
                    $result = $distribution->save_returned_item(get('value'), get('itemid'), get('distributionid'));
            }
            if ($route->action == 'savegoingoutitem') {
                $result = $distribution->save_going_out_item(get('value'), get('itemid'), get('distributionid'));
            }
            if ($route->action == 'gettodaypreparation') {
                $result = $distribution->get_today_preparation(get('distributionid'));
            }
        }
    }

    return array('content' => $result);
}
