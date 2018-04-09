
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
                var items = {regular: {}, non_regular: {}}
                data.forEach(function (item) {
                    items[item.id] = item;
                });
                result = items;
            }});
        return result;
    },
    'get_yesterday_preparation': function (distributionid) {
        var result = {};
        $.ajax({url: path + "distribution/getyesterdaypreparation.json?distributionid=" + distributionid, dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },
    'save_returned_item': function (value, itemid, distributionid) {
        var result = {};
        $.ajax({url: path + "distribution/savereturneditem.json?value=" + value + "&itemid=" + itemid + "&distributionid=" + distributionid, dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },
    'save_going_out_item': function (value, itemid, distributionid) {
        var result = {};
        $.ajax({url: path + "distribution/savegoingoutitem.json?value=" + value + "&itemid=" + itemid + "&distributionid=" + distributionid, dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },
    'get_today_preparation': function (distributionid) {
        var result = {};
        $.ajax({url: path + "distribution/gettodaypreparation.json?&distributionid=" + distributionid, dataType: 'json', async: false, success: function (data) {
                var items = {};
                data.forEach(function (item) {
                    items[item.itemid] = item;
                });
                result = items;
            }});
        return result;
    },
    'token_login': function (day_token) {
        var result = {};
        $.ajax({url: path + "distribution/tokenlogin.html", data: '&day_token=' + day_token + '', method: 'post', dataType: 'text', async: false, success: function (data) {
                console.log(data.responseText)
                result = data;
            }});
        return result;
    }
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