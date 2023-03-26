<!DOCTYPE html>
<html lang="en">

<?php
/**
 * @author Gabriel Davidenko
 */
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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
        integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
        integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

</head>


<style>
    #map {
        height: 50em;
        width: 100%;
    }

</style>

<body>

    <div id="container" class="container">

        <div class="row">
            <h1 class="col">Projet circulation Nancy</h1>

        </div>
        <div class="row">
            <h2 class="col">Carte des incidents de circulations de Nancy</h2>
            <div id="map"></div>

        </div>
        <div class="row">
            <h2>Etat de la pandémie du Covid-19 dans le département de <span id="departementCovid"></span></h2>
            <div>
                <canvas id="myChart"></canvas>
            </div>
        </div>
        <div class="row">
            <h2>Qualité de l'air à Nancy</h2>
            <p>Aujourd'hui, la qualité de l'air à Nancy est: <b id="airQuality"></b></p>
        </div>
        <div class="row" id="apiLinks">
            <table class="responsive-table highlight">
                <thead>
                    <tr>
                        <td>API</td>
                        <td>lien</td>
                    </tr>
                </thead>
                <tbody id="linkApi">
                </tbody>
            </table>
        </div>


    </div>

</body>



<script>
    const URL_API_LOCALISATION = 'https://ipapi.co/';
    const URL_API_LOCALISATION_2 = 'json/';
    const URL_API_CIRCULATION = 'https://carto.g-ny.org/data/cifs/cifs_waze_v2.json';
    const URL_API_COVID = 'https://www.data.gouv.fr/fr/datasets/r/5c4e1452-3850-4b59-b11c-3dd51d7fb8b5';
    const URL_API_AIR = 'https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D%27Nancy%27&objectIds=&time=&geometry=&geometryType=esriGeometryEnvelope&inSR=&spatialRel=esriSpatialRelIntersects&resultType=none&distance=0.0&units=esriSRUnit_Meter&returnGeodetic=false&outFields=*&returnGeometry=true&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=true&quantizationParameters=&sqlFormat=none&f=pjson&token=';

    async function getLocalisation(ip) {
        const url = ip ? `${URL_API_LOCALISATION}${ip}/${URL_API_LOCALISATION_2}` : `${URL_API_LOCALISATION}/${URL_API_LOCALISATION_2}`;
        console.log(url)
        return (await fetch(url)).json();
    }

    async function getCirculation() {
        return (await fetch(URL_API_CIRCULATION)).json()

    }
    async function getCovid() {
        return (await fetch(URL_API_COVID)).text();
    }

    async function getAirQuality() {
        return (await fetch(URL_API_AIR)).json();
    }

    function displayById(domNodeId, node) {
        document.getElementById(domNodeId).appendChild(node)
         
    }

    function buildRow(apiName, url){
        const tr = document.createElement('tr');
        
        const tdName = document.createElement('td');
        const nameTextNode = document.createTextNode(apiName); 
        tdName.appendChild(nameTextNode);

        const  tdUrl = document.createElement('td');
        const tdLink = document.createElement('a');
        tdLink.setAttribute('href', url);
        const urlTextNode = document.createTextNode(url);
        tdUrl.appendChild(tdLink);
        tdLink.appendChild(urlTextNode);

        tr.appendChild(tdName);
        tr.appendChild(tdUrl);

        return tr;        
    }
    /*
     * Original author: sturtevant 
     * found on: http://jsfiddle.net/sturtevant/AZFvQ/ 
     * function CSVToArray and CSV2JSON
    */
    function CSVToArray(strData, strDelimiter) {
        // Check to see if the delimiter is defined. If not,
        // then default to comma.
        strDelimiter = (strDelimiter || ",");
        // Create a regular expression to parse the CSV values.
        var objPattern = new RegExp((
            // Delimiters.
            "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +
            // Quoted fields.
            "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +
            // Standard fields.
            "([^\"\\" + strDelimiter + "\\r\\n]*))"), "gi");
        // Create an array to hold our data. Give the array
        // a default empty first row.
        var arrData = [[]];
        // Create an array to hold our individual pattern
        // matching groups.
        var arrMatches = null;
        // Keep looping over the regular expression matches
        // until we can no longer find a match.
        while (arrMatches = objPattern.exec(strData)) {
            // Get the delimiter that was found.
            var strMatchedDelimiter = arrMatches[1];
            // Check to see if the given delimiter has a length
            // (is not the start of string) and if it matches
            // field delimiter. If id does not, then we know
            // that this delimiter is a row delimiter.
            if (strMatchedDelimiter.length && (strMatchedDelimiter != strDelimiter)) {
                // Since we have reached a new row of data,
                // add an empty row to our data array.
                arrData.push([]);
            }
            // Now that we have our delimiter out of the way,
            // let's check to see which kind of value we
            // captured (quoted or unquoted).
            if (arrMatches[2]) {
                // We found a quoted value. When we capture
                // this value, unescape any double quotes.
                var strMatchedValue = arrMatches[2].replace(
                    new RegExp("\"\"", "g"), "\"");
            } else {
                // We found a non-quoted value.
                var strMatchedValue = arrMatches[3];
            }
            // Now that we have our value string, let's add
            // it to the data array.
            arrData[arrData.length - 1].push(strMatchedValue);
        }
        // Return the parsed data.
        return (arrData);
    }

    function CSV2JSON(csv) {
        var array = CSVToArray(csv);
        var objArray = [];
        for (var i = 1; i < array.length; i++) {
            objArray[i - 1] = {};
            for (var k = 0; k < array[0].length && k < array[i].length; k++) {
                var key = array[0][k];
                objArray[i - 1][key] = array[i][k]
            }
        }

        var json = JSON.stringify(objArray);
        var str = json.replace(/},/g, "},\r\n");

        return str;
    }




    async function main() {
        //ip et localisation
        const clientIp = '<?php $ip=getIpAddress(); echo("$ip")  ?>';
        const serverIp = '<?php echo($_SERVER['SERVER_ADDR']);?>';
        const clientLocalisation = await getLocalisation(clientIp);
        let serverLocation = undefined;
        if (clientLocalisation.city != 'Nancy') {
            serverLocation = await getLocalisation(serverIp);
        }

        //Circulation
        const ciruclation = await getCirculation()

        //Covid
        const covidList = await getCovid();
        const covidJsonList = JSON.parse(CSV2JSON(covidList));
        const clientDep = clientLocalisation.postal.substring(0, 2);
        const covidDepList = covidJsonList.filter(data => data.dep === clientDep)

        //Air
        const airQuality = await getAirQuality();
        if(airQuality){
            document.getElementById('airQuality').textContent = airQuality.features[0].attributes.lib_qual
        }else{ document.getElementById('airQuality').textContent = 'erreur avec l\'api de qualité d\'air'}

        //lat et lon
        const { userLat, userLon } = {
            userLat: serverLocation ? serverLocation.latitude : clientLocalisation.latitude,
            userLon: serverLocation ? serverLocation.longitude : clientLocalisation.longitude
        }

        //Affiche la carte aux coordonnées lat et lon 
        const map = L.map('map').setView([userLat, userLon], 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        //crée une icone pour l'utilisateur
        const userIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        //Affiche la position du client
        const clientMarker = L.marker([clientLocalisation.latitude, clientLocalisation.longitude], { icon: userIcon }).addTo(map);
        clientMarker.bindPopup("Vous êtes ici")

        //Affiche tout les lieux d'incidents
        ciruclation.incidents.forEach(incident => {
            const coord = incident.location.polyline.split(' ');
            const { lat, lon, description, startTime, endTime, street } = { lat: coord[0], lon: coord[1], description: incident.description, startTime: incident.starttime, endTime: incident.endtime, street: incident.location.street }
            const incidentMarker = L.marker([lat, lon]).addTo(map)
            incidentMarker.bindPopup(`<b>${description}</b><p>Rue: ${street}</p><p>Du: ${startTime} au ${endTime}</p>`)

        });
        
        //lien tableau
        const urlLocation = serverLocation? `${URL_API_LOCALISATION}${serverIp}/${URL_API_LOCALISATION_2}`: `${URL_API_LOCALISATION}${clientIp}/${URL_API_LOCALISATION_2}`;
        const localisationRow = buildRow('localisation', urlLocation);
        const circulationRow = buildRow('circulation', URL_API_CIRCULATION);
        const airQualityRow = buildRow('Qualité air', URL_API_AIR);
        const covidRow = buildRow('covid', URL_API_COVID);
        const apiRowArray = [localisationRow, circulationRow, covidRow, airQualityRow];
        apiRowArray.forEach((row)=>{displayById('linkApi', row)})

        //Affiche le graphique avec les données du COVID
        if (covidList) {
            document.getElementById('departementCovid').textContent = covidDepList[0].lib_dep
            //Récupèration des données pertinente pour le diagramme
            const lastDataCovid = covidDepList[covidDepList.length - 1];
            const selectedData = {
                dchosp: lastDataCovid.dchosp,
                hosp: lastDataCovid.hosp,
                incid_dchosp: lastDataCovid.incid_dchosp,
                incid_hosp: lastDataCovid.incid_hosp,
                incid_rad: lastDataCovid.incid_rad,
                incid_rea: lastDataCovid.incid_rea,
            };
            const labelsList = Object.getOwnPropertyNames(selectedData);
            const ctx = document.getElementById('myChart');

            //Diagrame
            new Chart(ctx, {
                type: 'polarArea',
                data: {
                    labels: labelsList,
                    datasets: [{
                        label: `Chiffes Covid 19 depuis le ${covidDepList[0].date}`,
                        data: Object.values(selectedData),
                        borderWidth: 1,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(75, 192, 192)',
                            'rgb(255, 205, 86)',
                            'rgb(201, 203, 207)',
                            'rgb(54, 162, 235)',
                            'rgb(238, 130, 238)',
                        ]

                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

    }

    main();



</script>


</html>