<!DOCTYPE html>
<html lang="en">

<?php
function getIpAddress()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
$opts = array('http' => array('proxy' => 'tcp://www-cache:3128', 'request_fulluri' => true));
$context = stream_context_set_default($opts);


?>

<head>
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />


    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
        integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
        integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin="">
    </script>


</head>

<style>
    #map {
        height: 50vh;
    }
</style>

<body>

    <div id="container">

        <div>
            <h2>Carte</h2>
            <div id="map"></div>

        </div>
        <div>
            <h2>Etat de la pandémie du Covid-19 dans le département de <span id="departementCovid"></span></h2>
        </div>
        <div id="apiLinks">
            <table>
                <thead>
                    <tr>
                        <td>API</td>
                        <td>lien</td>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>


    </div>

</body>

<script>


const URL_API_IP = 'https://ipapi.co/';
const URL_API_IP2 = 'json/';
const URL_API_CIRCULATION = 'https://carto.g-ny.org/data/cifs/cifs_waze_v2.json';
const URL_API_COVID = 'https://www.data.gouv.fr/fr/datasets/r/5c4e1452-3850-4b59-b11c-3dd51d7fb8b5';




function getLocalisation(ip) {
    const url = ip?`${URL_API_IP}${ip}/${URL_API_IP2}` :`${URL_API_IP}/${URL_API_IP2}`; 
    return fetch(`${url}`).then((response)=>{return response.json().then((data)=>{return data})})
}

function getCirculation() {
    return fetch(URL_API_CIRCULATION).then((response)=>{return response.json().then((data)=>{return data})})

}

function getCovid() {
    return fetch(URL_API_COVID).then((response)=>{return response.text().then((data)=>{return data})})
}

function displayUser(lat,lon){
    const clientMarker = L.marker([lat, lon], { icon: userIcon }).addTo(map);
clientMarker.bindPopup("Vous êtes ici ^^")

}

function displayIncident(data){
    data.incidents.forEach(incident => {
    const coord = incident.location.polyline.split(' ');
    const { lat, lon, description, startTime, endTime, street } = { lat: coord[0], lon: coord[1], description: incident.description, startTime: incident.starttime, endTime: incident.endtime, street: incident.location.street }
    const incidentMarker = L.marker([lat, lon]).addTo(map)
    incidentMarker.bindPopup(`<b>${description}</b><p>Rue: ${street}</p><p>Du: ${startTime} au ${endTime}</p>`)

 });

}

// function csvToJson(csv) {
//     return papaparse.parse(csv, { header: true })
// }
//---------------------------Main----------------------------


// const map = L.map('map').setView([clientLocalisation.lat, clientLocalisation.lon], 13);
// L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
//     maxZoom: 19,
//     attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
// }).addTo(map);
// const userIcon = new L.Icon({
//     iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
//     shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
//     iconSize: [25, 41],
//     iconAnchor: [12, 41],
//     popupAnchor: [1, -34],
//     shadowSize: [41, 41]
// });


const clientIp = "<?php $ip=getIpAddress(); echo("$ip")  ?>";
console.log(clientIp);
const clientLocalisation = getLocalisation()
                        .then(data=>{console.log(data);
                            //  displayUser(data.latitude, data.longitude)
                        });
const ciruclation = getCirculation().then(data=>{console.log(data);
    // displayIncident(data)
});
// const covid = await getCovid();
// const covidJson = csvToJson(covid);
// const clientDep = clientLocalisation.zip.substring(0, 2);
// const covidDep = covidJson.data.filter(data => data.dep === clientDep)

/**
 * @todo delete
*/
console.log({
    clientIp: clientIp,
    clientLocalisation: clientLocalisation,
    circulation: ciruclation,
    // covid: covid,
    // covidJson: covidJson,
    // clientDep: clientDep,
    // covidDep: covidDep
})

// document.getElementById('departementCovid').textContent = clientLocalisation.zip




/**
 * @todo graph and air
 */
</script>


</html>