
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
    'create_item': function (name, regular) {
        var result = {};
        $.ajax({url: path + "distribution/createitem.json?name=" + name + "&regular=" + regular, method: 'GET', dataType: 'json', async: false, success: function (data) {
                result = data;
            }});
        return result;
    },
    'get_items': function () {
        var result = {};
        $.ajax({url: path + "distribution/getitems.json", dataType: 'json', async: false, success: function (data) {
                //var items = {regular: {}, non_regular: {}};
                var items = {};
                data.forEach(function (item) {
                    /*if (item.regular == 1)
                     items.regular[item.id] = item;
                     else
                     items.non_regular[item.id] = item;*/
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
                result = data;
            }});
        return result;
    },
    'get_last_week_preparation': function (distributionid) {
        var result = {};
        $.ajax({url: path + "distribution/getlastweekpreparation.json?distributionid=" + distributionid + "", dataType: 'text', async: false, success: function (data) {
                var last_week_preparation = JSON.parse(data);
                for (var date in last_week_preparation) {
                    result[date] = {};
                    last_week_preparation[date].forEach(function (item) {
                        result[date][item.itemid] = item;
                    });
                }
            }});
        return result;
    },
    'delete_item': function (item_id) {
        var result = {};
        $.ajax({url: path + "distribution/deleteitem.json?id=" + item_id, method: 'post', dataType: 'json', async: false, success: function (data) {
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