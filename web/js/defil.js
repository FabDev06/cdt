var minute_scr=0;

function scrolls(direction)
{
    if(direction=='dw')
    {
        //alert('hauteur='+$('#divsfull').outerHeight());
        var hauteur = $('#divsfull').outerHeight();
        var sonshcroll = $('#divseance').scrollTop();

        if(sonshcroll<hauteur)
        minute_scr=setTimeout(function(){
            sonshcroll+=4;
            $('#divseance').scrollTop(sonshcroll);
            scrolls('dw');
                }, 4);
    }
    else
    {
        var hauteur = $('#divsfull').outerHeight();
        var sonshcroll = $('#divseance').scrollTop();

        if(sonshcroll>0)
        minute_scr=setTimeout(function(){
            sonshcroll-=4;
            $('#divseance').scrollTop(sonshcroll);
            scrolls('up');
                }, 4);
    }
}

function clearsc()
{
    clearTimeout(minute_scr);
}