$(document).ready(function() {
    Quotabar.setDefault();
    // Quotabar.getQuota();
});

var Quotabar = {
    setDefault : function() {
        Quotabar.setDefaultHandler();
    },
    setDefaultHandler : function() {
        $('#quotabarDiv').on('click', QuotabarDiv.click);
    },
    getQuota : function() {
        var ret;
        $.ajax({
            type : 'POST',
            url : OC.filePath("quotabar", "ajax", "quotabar.php"),
            async : false,
            success : function(data) {
                ret = data;
            }
        });
        return ret;
    },
};

var QuotabarDiv = {
    click : function() {
        QuotabarDiv.setLoading();
        data = Quotabar.getQuota();
        var used = data.used;
        var total = data.total;
        var relative = data.relative;
        QuotabarDiv.setQuotabar(used, total, relative);
    },
    setLoading : function() {
        var loadingImg = $('<img/>').attr('id', 'loadingImg').attr('src', OC.imagePath('core', 'loading.gif'));
        $('#quotabarDiv').html(loadingImg);
    },
    setQuotabar : function(used, total, relative) {
        var quotabar = '<div id="quotabar"></div>';
        var quotabar_relative = '<div id="quotabar_relative"></div>';
        var quotabar_value = '<div id="quotabar_value"></div>';
        $('#quotabarDiv').html('').append($(quotabar)).append($(quotabar_relative)).append($(quotabar_value));

        QuotabarDiv.setStyle();

        $("#quotabar_relative").html(relative + '%');
        $("#quotabar_value").html(used + 'G / ' + total + 'G');
        $("#quotabar").progressbar({
            value : relative,
        });
    },
    setStyle : function() {
        $('#quotabarDiv').css('height', '2.5em');
    }
};
