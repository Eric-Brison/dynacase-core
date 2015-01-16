$(document).on("ready", function () {

    var inputName = $(".format-attribute").data("inputname");
    var aid = $(".format-attribute").data("attrid");
    var famid = $(".defval-section").data("famid");
    var Htmlinput = $('[name="' + inputName + '"]');
    var Rawinput = $('[name="defval"]');
    var isModified=false;
    var getFormatValue=function () {

        var theValue=Htmlinput.val();
        if (theValue instanceof Array) {
            theValue=theValue.join("\n");
        }
        Rawinput.val(theValue);
        $(".defval-new-value .defval-value").text(theValue);
    };

    if (Htmlinput) {
        $(".format-attribute input,.format-attribute select,.format-attribute  textarea").on("change", getFormatValue);
        $(".format-attribute input.inlineButton").on("click", getFormatValue);


        $(".value-raw").on("change keyup", function () {

            $(".defval-new-value .defval-value").text(Rawinput.val());
            var $inputFile=$(".format-attribute input[type=file]");
            // delete file input
            if ($inputFile.length > 0) {
                $inputFile.val('');
            }
        });


        if (window.htmlText) {
            window.setTimeout(function () {
                if (CKEDITOR && CKEDITOR.instances[aid]) {
                    CKEDITOR.instances[aid].on("change", function () {
                        Rawinput.val(this.getData());
                        $(".defval-new-value .defval-value").text(Rawinput.val());
                    });
                }
            }, 2000);
        }

        $("#iu_"+aid).hide();
    }


    $(".defval-button-ok").on("click", function () {
        var url="?app=FREEDOM&action=MODONEDEFAULTVALUE&famid=" +
            encodeURIComponent(famid) +
            '&attrid='+ encodeURIComponent(aid) +
            "&value=" + encodeURIComponent(Rawinput.val());
        var data=null;
        var $inputFile=$(".format-attribute input[type=file]");
        if ($inputFile.length > 0) {
            var iFile=$inputFile[0];
            if (iFile.files.length > 0) {
                console.log("found file");
                data = new FormData();
                data.append('defaultFile', iFile.files[0]);
            }
        }

        $.ajax( {
      url: url,
            dataType:"json",
      type: 'POST',

      data: data,
      processData: false,
      contentType: false
    }).done(function (response) {
                $(".defval-initial-value .defval-value").text(response.value+" ");
                if (response.value === '') {
                    $(".no-message").show();
                } else {
                    $(".no-message").hide();
                }
                $(".defval-message").text(response.message).removeClass("ko");
                isModified=true;
            }).fail(function (response) {
                try {
                    var result = JSON.parse(response.responseText);
                    $(".defval-message").text(result.error).addClass("ko");
                } catch (e) {
                    alert(response.responseText);
                }

            });
    });

    $(".defval-button-close").on("click", function () {
      if (window.parent) {
          console.log("CLOSE", window.parent.$("#defvalDialog"));
          if (isModified) {
              window.parent.location.href=window.parent.location.href.split('#')[0]+'#'+aid;
              window.parent.location.reload(true);
          } else {
              window.parent.$("#defvalDialog").dialog("close");
          }
      }
    });

    $("[title]").tipsy();
});