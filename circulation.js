import papaparse from 'https://cdn.jsdelivr.net/npm/papaparse@5.3.2/+esm'


const URL_API_IP = 'http://ip-api.com/json/';
const URL_API_CIRCULATION = 'https://carto.g-ny.org/data/cifs/cifs_waze_v2.json';
const URL_API_COVID = 'https://www.data.gouv.fr/fr/datasets/r/5c4e1452-3850-4b59-b11c-3dd51d7fb8b5';

async function fetchUrl(url, contentTypeHeader) {
    const options = {
        headers: {
            "Content-Type": `${contentTypeHeader}`,
        }
    }
    const response = fetch(url, contentTypeHeader ? options : undefined
    )
    return (await response)
}

async function getClientIp() {
    const url = 'https://api.ipify.org?format=json';
    return (await fetchUrl(url)).json();

}

async function getLocalisation(ip) {
    return (await fetchUrl(`${URL_API_IP}${ip}`)).json();
}

async function getCirculation() {
    return (await fetchUrl(URL_API_CIRCULATION)).json()

}
async function getCovid() {
    return (await fetchUrl(URL_API_COVID, 'application/text')).text()
}

function csvToJson(csv) {
    return papaparse.parse(csv, { header: true })
}
//---------------------------Main----------------------------
const clientIp = await getClientIp();
const clientLocalisation = await getLocalisation(clientIp.ip)
const ciruclation = await getCirculation()
const covid = await getCovid();
const covidJson = csvToJson(covid);
const clientDep = clientLocalisation.zip.substring(0, 2);
const covidDep = covidJson.data.filter(data => data.dep === clientDep)

/**
 * @todo delete
*/
console.log({
    clientIp: clientIp,
    clientLocalisation: clientLocalisation,
    circulation: ciruclation,
    covid: covid,
    covidJson: covidJson,
    clientDep: clientDep,
    covidDep: covidDep
})

document.getElementById('departementCovid').textContent = clientLocalisation.zip


const map = L.map('map').setView([clientLocalisation.lat, clientLocalisation.lon], 13);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
const userIcon = new L.Icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const clientMarker = L.marker([clientLocalisation.lat, clientLocalisation.lon], { icon: userIcon }).addTo(map);
clientMarker.bindPopup("Vous êtes ici ^^")
ciruclation.incidents.forEach(incident => {
    const coord = incident.location.polyline.split(' ');
    const { lat, lon, description, startTime, endTime, street } = { lat: coord[0], lon: coord[1], description: incident.description, startTime: incident.starttime, endTime: incident.endtime, street: incident.location.street }
    const incidentMarker = L.marker([lat, lon]).addTo(map)
    incidentMarker.bindPopup(`<b>${description}</b><p>Rue: ${street}</p><p>Du: ${startTime} au ${endTime}</p>`)

});

/**
 * @todo graph and air
 */