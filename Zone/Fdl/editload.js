
/**
 * @author Anakeen
 */

$(document).ready(function () {
    $("body").on("click", '#relTooltip', function(event){
        $(this).hide();
    });
    $("#fedit").on("click", 'input[autoinput][readonly]', function(event){
        var dTooltip=$("#relTooltip");
        var oTooltip=null;
        if (dTooltip.length == 0) {
            oTooltip=document.createElement('div');
            oTooltip.setAttribute('id','relTooltip');
            oTooltip.style.position='absolute';
            oTooltip.innerHTML="[TEXT:Click to clear button to change value]";
            oTooltip.className='relationTooltip';
            document.body.appendChild(oTooltip);
            dTooltip=$("#relTooltip");
        }
        if (dTooltip.length == 1) {
            oTooltip= dTooltip[0];
            var x=$(this).position().left;
            var y=$(this).position().top;

            oTooltip.style.left=x+'px';
            oTooltip.style.top=y+'px';
            dTooltip.show();
            setTimeout(function(){ $("#relTooltip").hide('slow'); }, 2000);
        }

    });
});