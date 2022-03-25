/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

$(function(){
    $('[externallist]').each(function(index, element){
        var e = $(element);
        
        alert(e.id);
        
        var t = e.offset().top;
        var l = e.offset().left;
        var w = e.width();
        var h = e.height();
        
        var b = "<input type='button' style=''>";
        
        
        
        
        
    });
});
