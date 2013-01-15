function AdminPage(service, description, path) {
    var self = this;
    self.service = service;
    self.description = description;
    self.path = path;
    self.select = null;

}

function log(info) {
    $('#log-zone').html('<div class="line">[' + (new Date()).toLocaleDateString() + ' ' + (new Date()).toLocaleTimeString() + '] ' + info + '</div>' + $('#log-zone').html());
}

function AdminCenterViewModel() {
    $.ajax({
        url:"getServices.php",
        async:false,
        success:function (data) {
            var tdata = JSON.parse(data);
            var tx = "";
            var itx = 0;
            for (var i = 0; i < tdata.length; i++) {
                if (tdata[i].error == "") {
                    tx += '<li onclick="selectService(' + "'" + tdata[i].description + "'" + ',' + "'" + tdata[i].title + "'" + ',' + "'" + tdata[i].start + "'" + ');" title="' + tdata[i].description + '" >' + tdata[i].title + '</li>';
                    log('Loading service ' + tdata[i].title + '...');
                } else {
                    log(tdata[i].error);
                }
            }
            $("#pages").html(tx);
        },
        error:function (jqXHR, textStatus, errorThrown) {
            log(errorThrown);
        }
    });
}

function selectService(description, service, path) {
    $('#current-service-description').html(description);
    log('Running service ' + service + ', start at ' + path);
    $('#targetFrame').attr("src", path);
}
