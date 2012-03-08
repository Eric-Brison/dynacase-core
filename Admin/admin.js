function AdminPage(service, description, path) {
    var self = this;
    self.service = service;
    self.description = description;
    self.path = path;
    self.select = null;

}

function AdminCenterViewModel() {
    var self = this;

    self.log = function(info) {
	$('#log-zone').html('<div class="line">['+(new Date()).toLocaleDateString()+' '+(new Date()).toLocaleTimeString()+'] '+info+'</div>'+$('#log-zone').html());
    }

    $.ajax({
	url: "getServices.php",
	async:false,
	success: function(data){
	    tdata = JSON.parse(data);
	    var tx = new Array();
            var itx=0;
	    for (var i=0; i<tdata.length; i++) {
		if (tdata[i].error=="") {
		  tx[itx++] = new AdminPage(tdata[i].title, tdata[i].description, tdata[i].start);
		  self.log('Loading service '+tdata[i].title+'...');
                } else {
                  self.log(tdata[i].error);
                }
	    }
	    self.pages = ko.observableArray(tx);
	},
        error: function(jqXHR, textStatus, errorThrown) {
          self.log(errorThrown);
        }
    });
    
    self.selectService = function(page) {
	$('#current-service-description').html(page.description);
	self.log('Running service '+page.service+', start at '+page.path);
	$('#targetFrame').attr("src",page.path);
    }

}
			  
