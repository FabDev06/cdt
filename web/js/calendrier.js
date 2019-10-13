

function cal_chg_mois(mois,annee)
{
    /*
    var xhr = new XMLHttpRequest();

    xhr.open('POST', '/cal_ajap', true);
    xhr.send(mois);

    https://www.grafikart.fr/forum/topics/16342
    https://www.tutorialspoint.com/symfony/symfony_ajax_control.htm

    */
   //console.log('On envoi le mois : '+mois);
   $.ajax({
        url: '/cal_ajap',
        dataType: 'json',
        success: function(response)
                {
                    //console.log('response='+response.contenu);
                    //alert(Object.values(response.contenu));
                    $('#cal_contenu').html(response.contenu);
                },
        type: 'POST',
        data: {'mois': mois, 'annee': annee}
        });
    /*
    $.ajax({
        url : '',
        method: 'post',
        dataType : 'html',
        data: mois
        }).done(function(response)
        {
            //var htmlToDisplay = response.trim();
            $("#cal_contenu").html(response);
        });
        */
}

/*
function suiv(mois)
{
    $.ajax({
        url : '',
        method: 'post',
        dataType : 'html',
        data: mois
        }).done(function(response)
        {
            //var htmlToDisplay = response.trim();
            $("#cal_contenu").html(response);
        });
}
*/

function prot(ssis,zbew,mois,annee)
{
    //ssis.style.transform="rotate(7deg)";
    
    zbew+=.08;
    ssis.style.transform="rotate("+zbew+"deg)";

    if(zbew<=100)
    {
        console.log(zbew);
        setTimeout(prot(ssis,zbew,mois,annee), 1000);
    }
    else
        cal_chg_mois(mois,annee);
}
