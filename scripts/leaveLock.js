
function needAcceptOnLeave(message){
    window.addEventListener("beforeunload", function(e){
        (e || window.event).returnValue = message; //Gecko + IE
        return message;                            //Webkit, Safari, Chrome
    });
}