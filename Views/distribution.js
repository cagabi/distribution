
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
    'create_distribution_point': function (name, organizationid) {
        var result = {};
        $.ajax({url: path + "distribution/createdistributionpoint.json?name=" + name + "&organizationid=" + organizationid, method: 'GET', dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },    
    'get_items': function () {
        var result = {};
        $.ajax({url: path + "distribution/getitems.json", dataType: 'json', async: false, success: function (data) {
                var items={regular:[], non_regular:[]}
                data.forEach(function(item){
                    if (item.regular !=='0')
                        items.regular.push(item);
                    else
                        items.non_regular.push(item);
                }); 
                result = items;
                console.log(items)
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