<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
        integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
        integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=" crossorigin=""></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

</head>



<?php
/**
 * @author Gabriel Davidenko
 */

/**
 * Constantes : Lien API
 */
const API_IP_URL = "http://ip-api.com/xml/";
const API_WEATHER_URL = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=";
const API_WEATHER_AUTH_KEY = '&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2';
const API_VELO_STATIONS_INFORMATIONS = 'https://transport.data.gouv.fr/gbfs/nancy/station_information.json';
const API_VELO_STATIONS_STATUS = 'https://transport.data.gouv.fr/gbfs/nancy/station_status.json';
const API_AIR_QUALITY = 'https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D%27Nancy%27&objectIds=&time=&geometry=&geometryType=esriGeometryEnvelope&inSR=&spatialRel=esriSpatialRelIntersects&resultType=none&distance=0.0&units=esriSRUnit_Meter&returnGeodetic=false&outFields=*&returnGeometry=true&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=true&quantizationParameters=&sqlFormat=none&f=pjson&token=';

/**
 * Récupération de l'ip du client
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

/**
 * Appel à l'api pour récupérer la localisation du client 
 */
function getLocalisation()
{
    try {
        //récupère la localisation du client
        $location = simplexml_load_file(constant('API_IP_URL') . getIpAddress());
        //si le client est pas sur Nancy
        //on utilise l'ip de Chalemagne
        if ($location->city != 'Nancy') {
            $ipCharlemagne = $_SERVER['SERVER_ADDR'];
            $location = simplexml_load_file(constant('API_IP_URL') . $ipCharlemagne);
        }
    } catch (\Throwable $th) {
        echo ("erreur 404 - l'api de localisation ne fonctionne pas");
    }
    return $location;
}

/**
 * Appel a l'api de météo et utilise la feuille xsl pour générer un tableau html avec la météo
 */
function getWeather($latitude, $longitude)
{
    try {
        $rawWeatherXML = simplexml_load_file(constant('API_WEATHER_URL') . $latitude . ',' . $longitude . constant('API_WEATHER_AUTH_KEY'));
        $xsl = new DomDocument;
        $xsl->load('meteo.xsl');
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl);
        return $proc->transformToXML($rawWeatherXML);
    } catch (\Throwable $th) {
        printf("404 - le service de météo n'a pas été trouvé");
        printf($th);
    }
}

/**
 * Récupère les données à l'url passé en paramètre
 */
function curl($url)
{
    try {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_PROXY, 'tcp://www-cache:3128');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $res = curl_exec($handle);
        curl_close($handle);
        print_r($res);
        return $res;
    } catch (\Throwable $th) {
        echo ("L'api que vous avez demandé a cessé de fonctionner.");
    }
}

/**
 * Récupère le status des stations de vélo
 */
function getVeloStatus()
{
    $veloStatus = json_decode(curl(constant('API_VELO_STATIONS_STATUS')));
    return $veloStatus;
}

/**
 * Récupère les informations des stations de vélo
 */
function getVeloInfo()
{
    $veloInfo = json_decode(curl(constant('API_VELO_STATIONS_INFORMATIONS')));
    return $veloInfo;
}

function getAirQuality()
{
    $airQuality = json_decode(curl(constant('API_AIR_QUALITY')));
    return $airQuality;
}

//----------------------------Main----------------------------

$opts = array('http' => array('proxy' => 'tcp://www-cache:3128', 'request_fulluri' => true));
$context = stream_context_set_default($opts);
?>

<style>
    #map {
        height: 30em;
    }
    a { overflow: hidden;}
    td { overflow: hidden;}
    table{ overflow: hidden;}
</style>



<?php
//HTML
echo ('<body>');

//Appel aux différentes API
$location = getLocalisation();
$weather = getWeather($location->lat, $location->lon);
echo ('<div hidden>');
$veloStatus = getVeloStatus();
$veloInfo = getVeloInfo();
$airQuality = getAirQuality();
echo ('</div>');


if ($location) {

    //Meteo
    if ($weather) {
        echo ('<div class="container">');
        echo ('<h1 class="row">Projet à bicyclette</h1>');
        echo ('<div>');
        echo ('<h2>Meteo à ' . $location->city . '</h2>');
        echo ($weather);
        echo ('</div>');

    }

    //Carte et velo
    echo ('<div class="row">');
    echo ('<h2>Carte des stations de vélo de Nancy</h2>');
    echo ('<div id="map"></div>');
    echo ('</div>');
    
    $city = $airQuality->features[0]->attributes->lib_zone;
    $quality = $airQuality->features[0]->attributes->lib_qual;
    //Air
    echo ('<div class="row">');
    echo ("<h2>Qualité de l'air à $city</h2>");
    echo ("<p>Aujourd'hui, la qualité de l'air à $city est $quality.</p>");
    echo ('<div>');

    echo ('<div class="row">');
    echo ("<h2>Lien des api</h2>");

    $urlIp = constant('API_IP_URL').getIpAddress();
    $urlWeather = constant('API_WEATHER_URL'). $location->lat . ',' . $location->lon .constant('API_WEATHER_AUTH_KEY');
    $urlBikeStatus = constant('API_VELO_STATIONS_STATUS');
    $urlBikeInfo= constant('API_VELO_STATIONS_INFORMATIONS');
    $urlAirQuality = constant('API_AIR_QUALITY');
    echo ("<table class='responsive-table striped'>
        <thead>
            <tr>
                <th>API</th>
                <th>Lien</th>
            </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ip</td>
                <td><a href=\"$urlIp\">$urlIp</a></td>
            </tr>
            <tr>
                <td>Meteo</td>
                <td><a href=\"$urlWeather\">$urlWeather</a></td>
            </tr>
            <tr>
                <td>Status vélo</td>
                <td><a href=\"$urlBikeStatus\">$urlBikeStatus</a></td>
            </tr>
            <tr>
                <td>Info vélo</td>
                <td><a href=\"$urlBikeInfo\">$urlBikeInfo</a></td>
            </tr>
            <tr>
                <td>Qualité de l'air</td>
                <td><a href=\"$urlAirQuality\">$urlAirQuality</a></td>
            </tr>

        </tbody>
    </table>
    ");
    echo ('<div>');
    echo ('<div>');



    echo ('</body>');
    echo ('<script>');
    echo ("
    const map = L.map('map').setView([$location->lat, $location->lon], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a>'
    }).addTo(map);
    const userIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    const iutIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    const clientMarker = L.marker([$location->lat, $location->lon], { icon: userIcon }).addTo(map);
    clientMarker.bindPopup('Vous êtes ici')
    const iutMarker = L.marker([48.68279, 6.16099], { icon: iutIcon }).addTo(map);
    iutMarker.bindPopup('IUT Nancy-Charlemagne')
    ");
    for ($i = 0; $i < sizeof($veloStatus->data->stations); $i++) {
        //Aglomère les deux objet vélo obtenu avec les deux appel en un objet

        $veloAggregate = array_merge((array) $veloStatus->data->stations[$i], (array) $veloInfo->data->stations[$i]);
        $lat = $veloAggregate["lat"];
        $lon = $veloAggregate["lon"];
        $station_id = $veloAggregate["station_id"];
        $name = $veloAggregate["name"];
        $num_bikes_available = $veloAggregate["num_bikes_available"];
        $num_docks_available = $veloAggregate["num_docks_available"];
        $constName = "station$station_id";
        echo ("const $constName = L.marker([$lat, $lon]).addTo(map)
        $constName.bindPopup(`<div>
        <b>$name</b>
        <ul>
        <li>nombre de velo disponible: $num_bikes_available </li>
        <li>nombre de places disponible: $num_docks_available </li>
        </ul>
        </div>
        `)
        ");

    }
    echo ('</script>');
}else{
    echo("Nous rencontrons une erreur avec une de nos application, veuillez réessayer plus tard");
}


?>


</html>