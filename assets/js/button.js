function enable_register(){
    console.log("Hit");
    if (document.getElementById("read_terms").checked === true){
        document.getElementById("terms").setAttribute('style', 'color: white');
        document.getElementById("submit").removeAttribute('disabled');
        jQuery("#submit").css("opacity","1");
    }else{
        document.getElementById("submit").setAttribute('disabled','');
        document.getElementById("terms").setAttribute('style', 'color: red');
        jQuery("#submit").css("opacity","0.5");
    }
}
function forgot_password(){
    if(jQuery("#popup1").css("display") === "none") {
        jQuery("#popup1").css("display","block");
    } else {
        jQuery("#popup1").css("display","none");
    }
}