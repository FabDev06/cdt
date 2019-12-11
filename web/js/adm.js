

function adm_ajout2()
{
    $('#page_contenu').addClass('opack');
    $('#loate').css('display', 'block');
    var dato = $('form[name=appbundle_devoir]').serialize();

    $.ajax({url:'/admin-ajout',
        type:'POST',
        data:dato,
        success:function(response)
        {
            etape2_tr(response);
        },
        dataType:'html'});
}

function etape2_tr(data)
{
    $('#loate').css('display', 'none');
    $('#page_contenu').removeClass('opack');

    defil(0,data);
}

function defil(zbew,data)
{
    zbew-=8; //*(-zbew*.2);

    if(zbew>=-900)
    {
        setTimeout(function(){
                    $('#page_contenu').css("transform","translate("+zbew+"px)");
                    defil(zbew,data);
                        }, 4);
    }
    else
    {
        $("#page_contenu").html(data);
        $('#page_contenu').css("transform",'none');
    }
}

function ajout_ed()
{
    var ordre=1+parseInt($('input[name=ordre]').val());
    $('input[name=ordre]').val(ordre);
    $.get("/admin-form",{iddev:$('input[name=id_dev]').val(), ordre:ordre, typec:"texte"},function(response)
    {
        $('#div_enonces').append(response);
        // ajout du fuceditor
        CKEDITOR.replace('editor'+ordre, //$("input[name='appbundle_enonce[contenu]'"),
        {
            language:'fr',
            uiColor:'#397e40',
            startupFocus:true
        });
    });
}

function ajout_im()
{
   var ordre=1+parseInt($('input[name=ordre]').val());
   $('input[name=ordre]').val(ordre);
   $.get("/admin-form",{iddev:$('input[name=id_dev]').val(), ordre:ordre, typec:"image"},function(response)
   {
       $('#div_enonces').append(response);
   });
}

function ajout_en()
{
    $('#page_contenu').addClass('opack');
    $('#loate').css('display', 'block');
    
    var maxForm = $('input[name=ordre]').val();
    if(maxForm>0)
    {
        $.each($('form'),function(key, value)
        {
            if(key>0)
            {
                //console.log('typec='+this.elements['appbundle_enonce[typec]'].value);
                //if($('input[name="appbundle_enonce[typec]"]').val()=='image')
                if(this.elements['appbundle_enonce[typec]'].value=='image')
                {
                    // traitement image (only)
                    var daton = new FormData(value);

                    $.ajax({url:'/admin-enonce',
                            type:'POST',
                            data:daton,
                            processData:false,
                            mimeType:'multipart/form-data',
                            success:function()
                            {
                                // on quitte à la fin du dernier post form
                                if(key==maxForm)
                                {
                                    $.get('/admin-devoir-'+$('input[name="appbundle_enonce[idDev]"]').val(),null,function(response)
                                                {
                                                    $("#page_contenu").html(response);
                                                    $('#loate').css('display', 'none');
                                                    $('#page_contenu').removeClass('opack');
                                                }
                                        );
                                }
                                else
                                    $(this).css('display', 'none');
                            },
                        });
                }
                else
                {
                    // depuis modif fuceditor, move du contenu de editorX vers contenu invisible
                    fini=false;
                    if(CKEDITOR.instances)
                    $.each(CKEDITOR.instances, function(cle)
                    {
                        if(fini===false)
                        {
                            if(CKEDITOR.instances[cle])
                            {
                                ck_contenu = CKEDITOR.instances[cle].getData();
                                $(this).css('display', 'none');
                                CKEDITOR.instances[cle].destroy();
                                fini=true;
                            }
                        }
                    });

                    //console.log('ck_contenu='+ck_contenu);
                    if(ck_contenu==null || ck_contenu=='')
                        ck_contenu='vide...';

                    this.elements['appbundle_enonce[contenu]'].value=ck_contenu;
                    //$('input[name="appbundle_enonce[contenu]"]').val(ck_contenu);

                    var datou = $(this).serialize();
                    $.ajax({url:'/admin-enonce',
                        type:'POST',
                        data:datou,
                        success:function()
                        {
                            // on quitte à la fin du dernier post form
                            if(key==maxForm)
                            {
                                $.get('/admin-devoir-'+$('input[name="appbundle_enonce[idDev]"]').val(),null,function(response)
                                            {
                                                $("#page_contenu").html(response);
                                                $('#loate').css('display', 'none');
                                                $('#page_contenu').removeClass('opack');
                                            }
                                    );
                            }
                            else
                                $(this).css('display', 'none');
                        },
                        dataType:'html'});
                }
            }
        });
    }
}

function sel_edt(obj,value)
{
    // routine eff. autres div
    $('#ourder').children('div.div_edt').css('border', 'none');
    $(obj).css('border', '2px solid red'); //'display', 'none');
    $('input[name="appbundle_devoir[classe]"]').val(value);

    $.ajax(
        {
            url:'/admin-classe-'+value,
            type:'GET',
            data:value,
            success:function(response)
            {
                $('#div_classe').html(response);
            }
        });
}

function daton_test()
{
    var daton = new FormData($('form')[0]);
    //console.log($('form')[0]);
    //daton.append('fichimage', new Blob(['image.jpg']), $('input[name="appbundle_enonce[contenu]"]').val());

    $('#divtest').html('<strong>Loading...</strong>');
    $.ajax({url:'/admin-test',
            type:'POST',
            data:daton,
            processData:false,
            mimeType:'multipart/form-data',
            success:function(response)
            {
                $('#divtest').html(response);
            }
        });
}

function swap(moteule,typec)
{
    if(typec=='texte')
    {
        $('#div_raw'+moteule).css('display', 'none');
        CKEDITOR.replace('tata'+moteule,
        {
            language:'fr',
            uiColor:'#397e40',
            startupFocus:true
        });
        $('#div_rep_txt'+moteule).css('display', 'block');
    }
    else
    {
        $('#cimg'+moteule).css('width', '33%');
        $('#div_rep_img'+moteule).css('display', 'block');
    }
    $('#div_btn'+moteule).css('display', 'none');
    //$('#div_btn'+moteule).html('<a style="cursor:pointer;position:absolute;bottom:0px" onclick=""><img class="btndev" src="ims/devupd.png" alt="Enregistrer" />nregistrer</a>');
}

/*
        NOTES / DIVERS
*/

                // $(this).serialize();
                // pas possible de serialize, car il renvoi des strings et pas d'upload file : https://api.jquery.com/serialize/#serialize
                //daton.append('contenu',$("input:file"));

            //alert('submit ajap ok');

    //var dato = $('form[name=appbundle_devoir]').serializeToJSON();
    //var dato = $('form[name=appbundle_devoir]').serializeArray();
    //var formData = new FormData(document.querySelector('form')); //{}; //JSON.stringify(dato);
    
/*
    dato.forEach(function(item) {
        console.log("'"+item.name+"':'"+item.value+"',"),
        formData+="'"+item.name+"':'"+item.value+"',";
    });

        success:function(response)
        {
            if(response!==0)
                etape2_tr(response);
            else
                alert('Tous les champs ne sont pas remplis');
        },

*/

/*
function televerse(zdex)
{
    var miniform = 'form[name=formim'+zdex+']';
    var daton = $(miniform).serialize();
    console.log(daton);

    $('#page_contenu').addClass('opack');
    $('#loate').css('display', 'block');

    $.ajax({url:'/admin-televerse',
        type:'POST',
        data:daton,
        success:function(response)
        {
            console.log(response);
            $('#loate').css('display', 'none');
            $('#page_contenu').removeClass('opack');
        },
        dataType:'html'});
}
*/

//$("#form_enonces").append('<input type="file" id="" onchange="handleFiles(this.files)"/><br/>');

        /*
    var ordre=1+parseInt($('input[name=compteur]').val());
    $('input[name=compteur]').val(ordre);
    $('form').append('Pour ajouter une illustration, appuyer sur :');
    $('form').append('<form name="formim'+ordre+'" method="post" enctype="multipart/form-data">');
    $('form').append('<input type="file" name="imup" onchange="televerse('+ordre+')" />');
    $('form').append('</form>');
    $('form').append('<input type="text" name="imlien'+ordre+'" />');
    $('form').append('<br/>');
    */
/*

   $.each($('form'),function(key, value )
    key = l'index par ex. [0] [1] d'une array
    la value = xxx[key]=value par ex. $('form')[0]=value
    */

    /*
function ajout_txt(fform, cle, typec)
{
    // traitement texte + datas image et persist Bdd
    var maxForm = $('input[name=ordre]').val();
    var datou = $(fform).serialize();

    if(typec=='image')
        console.log('appel ajout_txt('+cle+') = '+$(fform)+' datou='+datou);

    $.ajax({url:'/admin-enonce',
        type:'POST',
        data:datou,
        success:function()
        {
            // on quitte à la fin du dernier post form
            if(cle==maxForm)
                $.get('/admin-devoir-'+$('input[name="appbundle_enonce[idDev]"]').val(),null,function(response)
                            {
                                $("#page_contenu").html(response);
                                $('#loate').css('display', 'none');
                                $('#page_contenu').removeClass('opack');
                            }
                    );
        },
        dataType:'html'});
}

        CKEDITOR.replace('editor'+ordre,
        {
            //cloudServices_tokenUrl:'https://example.com/cs-token-endpoint',
//            customConfig:'ckeditor/config.js',
            language:'fr',
//            plugin:'image',
//            replacedWith:'easyimage',
            //removeButtons:'elementspath,save',
            //sharedSpaces:{bottom:''},
            uiColor:'#397e40',
            startupFocus:true
//            imageUploadUrl:'../ims/devoirs/'
        });

    */
                    /*
                    var cle=key;
                    $.each(CKEDITOR.instances, function(key, value)
                    {
                        if(key==cle)
                        {
                            console.log('l.118 : obl = '+obl+' / CKEDITOR.instances='+CKEDITOR.instances[obl]); //.obl.getData());
                            $('input[name="appbundle_enonce[contenu]"]').val(CKEDITOR.instances[obl].getData());
                            CKEDITOR.instances.$('editor'+ordre).destroy();
                        }
                    });
                    */
                        //console.log('CKEDITOR.instances.value='+value); //.obl.getData());
                        //console.log('value='+CKEDITOR.instances[key].getData());
