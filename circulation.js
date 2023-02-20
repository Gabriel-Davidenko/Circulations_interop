
function getClientIp(){
return fetch('https://api.ipify.org?format=json',{
    headers:{
    "Content-Type": "application/json",

}}).then(response=>response.json())

}


function getLocalisation(ip){
    return fetch(`http://ip-api.com/json/${ip}`,
    {
        headers:{
        "Content-Type": "application/json",
    
    }}).then(response=>response.json())
    
    }

//---------------------------Main----------------------------
getClientIp().then(ip=>getLocalisation(ip.ip))
            .then(localisation=>console.log(localisation));