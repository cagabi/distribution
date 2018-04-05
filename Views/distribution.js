
var distribution = {
    'list_organizations': function () {
        var result = {};
        $.ajax({url: path + "distribution/listorganizations.json", dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },
    'create_organization': function (name) {
        var result = {};
        $.ajax({url: path + "distribution/createorganization.json?name=" + name, dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },
    'create_user': function (name, organizationid, email, password, role) {
        var result = {};
        $.ajax({url: path + "distribution/createuser.json?name=" + name + "&organizationid=" + organizationid + "&email=" + email + "&role=" + role, data: 'password=' + password, method: 'POST', dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },
};

/*********
 function () {
 var result = {};
 $.ajax({url: path + "distribution/listorganizations.json", dataType: 'json', async: false, success: function (data) {
 result = data;
 }});
 return result;
 },
 */