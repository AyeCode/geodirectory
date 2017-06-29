function gd_featured_widget_repeat($rep,$name){

    var $div = jQuery('.'+$rep+' div[class^="gdrep"]:last');

    var num = parseInt( $div.prop("class").match(/\d+/g), 10 ) +1;
    old_num = num-1;


    var $klon = $div.clone().prop('class', 'gdrep'+num );
    $klon.insertAfter( jQuery('.'+$rep+' div[class^="gdrep"]:last'));

    klone_title_text = jQuery('.'+$rep+' .gdrep'+num+' *[ data-gdrep-title-num="1"]').text();
    jQuery('.'+$rep+' .gdrep'+num+' *[ data-gdrep-title-num="1"]').html(klone_title_text.replace(old_num, num));


    jQuery('.'+$rep+' .gdrep'+num+' *[ data-gdrep-title="1"]').val('').prop('name',$name.replace("xxx", "title"+num));
    jQuery('.'+$rep+' .gdrep'+num+' *[ data-gdrep-image="1"]').val('').prop('name',$name.replace("xxx", "image"+num));
    jQuery('.'+$rep+' .gdrep'+num+' *[ data-gdrep-desc="1"]').val('').prop('name',$name.replace("xxx", "desc"+num));

}