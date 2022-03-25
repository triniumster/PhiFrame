/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function randomKey(len){
    len = isNaN(len) && len > 0 ? len : 12;
    var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    var string = "";
    
    for(var i = 0; i < len; i++)
        string += chars.charAt(Math.floor(Math.random() * chars.length));

    return string;
}