/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function getParamFromUri(paramName){
    var results = new RegExp(
            "[?&]" + 
            name.replace(/[\[\]]/g, "\\$&") + 
            "(=([^&#]*)|&|#|$)"
    ).exec(window.location.href);
    
    if(!results)
        return null;
    
    if(!results[2])
        return '';
    
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
