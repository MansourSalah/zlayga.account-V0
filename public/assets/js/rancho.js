//Personaliser Rancho
function setLang(o){
    if($(o).val()=="ar" || $(o).val()=="en"){
        $.get("/api/lang?lang="+$(o).val())
        .done(function(data,statut){
            if(statut=="success")  
                location.reload();
        });
    }
 }