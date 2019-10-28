

function cal_chg_mois(mois,annee)
{
   $.ajax({
        url: '/cal_ajap',
        dataType: 'json',
        success: function(response)
                {
                    $('#cal_contenu').html(response.contenu);
                    $('#btng').fadeIn("fast");
                    $('#btnd').fadeIn("fast");
                    $('#btng').css("transform","none");
                    $('#btnd').css("transform","none");
                },
        type: 'POST',
        data: {'mois': mois, 'annee': annee}
        });
}

function prot_g(mois,annee,zbew)
{
    zbew-=2;
    if(zbew>-4)
        $('#btng').fadeOut("fast");

    if(zbew>=-50)
    {
        setTimeout(function(){
                    $('#btng').css("transform","translate("+zbew+"px)");
                    prot_g(mois,annee,zbew);
                        }, 4);
    }
    else
        cal_chg_mois(mois,annee);
}

function prot_d(mois,annee,zbew)
{
    zbew+=2;
    if(zbew<4)
        $('#btnd').fadeOut("fast");

    if(zbew<=50)
    {
        setTimeout(function(){
                    $('#btnd').css("transform","translate("+zbew+"px)");
                    prot_d(mois,annee,zbew);
                        }, 4);
    }
    else
        cal_chg_mois(mois,annee);
}

                    //console.log('response='+response.contenu);
                    //alert(Object.values(response.contenu));

        /*
    var xhr = new XMLHttpRequest();

    xhr.open('POST', '/cal_ajap', true);
    xhr.send(mois);
    */
   //console.log('On envoi le mois : '+mois);

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
    https://www.grafikart.fr/forum/topics/16342
    https://www.tutorialspoint.com/symfony/symfony_ajax_control.htm
}
*/
