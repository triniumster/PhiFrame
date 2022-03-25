/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2016) Use as is without any gwaranty 
 * 
 */

function formatAmount(val){
    if(val >= 1){
        var v = roundNumber(val, 2);
        
        if(v % 1 === 0){
            return v+",-";
        }

        if((v*10) % 1 === 0){
            return v.toString()+"0";
        }
        
        return v;
    } else {
        return val.toPrecision(2);
    }
}

function roundNumber(num, scale) {
    if(!("" + num).includes("e")) {
        return +(Math.round(num + "e+" + scale)  + "e-" + scale);  
    } else {
        var arr = ("" + num).split("e");
        var sig = "";
        
        if(+arr[1] + scale > 0) {
            sig = "+";
        }
        
        return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
    }
}

function testBit(num, bit){
    return ((num>>bit) % 2 !== 0);
}

function setBit(num, bit){
    return num | 1<<bit;
}

function clearBit(num, bit){
    return num & ~(1<<bit);
}

function toggleBit(num, bit){
    return bit_test(num, bit) ? bit_clear(num, bit) : bit_set(num, bit);
}

function getIntById(id){
    var field = parseInt($('#'+id).val());

    if(isNaN(field)){
        field = 0;
    }  
    
    return field;
}

function getFloatById(id){
    var field = parseFloatDot($('#'+id).val());

    if(isNaN(field)){
        field = 0;
    }  
    
    return field;
}

function loadContent(objLink, cmd, data){    
    data.ajax = true;
    
    $.ajax({
        method: "POST",
        url: "index.php?cmd="+cmd,
        data: data
    }).done(function(result){
        $(objLink).html(result);
        
        if(typeof(TabedPane.refreshCurrent) === 'function')
            TabedPane.refreshCurrent();
    });              
}

function parseFloatDot(expresion){
    return parseFloat(expresion.replace(',', '.'));
}

function getFormObject(id){
    var inputs = $('#'+id).find('input');
    var selects = $('#'+id).find('select');
    var obj = [];
    
    inputs.each(function(idx){
        switch(inputs[idx].type()){
            case 'button':
                break;
            case 'checkbox':
                obj[selects[idx].id()] = selects[idx].prop('checked');
                break;
            default:
                obj[selects[idx].id()] = selects[idx].val();
        }
    });
    
    selects.each(function(idx){
        obj[selects[idx].id()] = selects[idx].val();
    });
    
    return obj;
}

function isprop(obj, prop){
    return typeof(obj) === 'object' && obj !== null && obj.hasOwnProperty(prop) && obj[prop] !== null && obj[prop] !== false;
}