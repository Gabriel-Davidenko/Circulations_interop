const URL_API_IP = 'http://ip-api.com/json/';
const URL_API_CIRCULATION = 'https://carto.g-ny.org/data/cifs/cifs_waze_v2.json';
async function fetchUrl(url) {
    const response = fetch(url, {
        // headers: {
        //     "Content-Type": "application/json",
        // }
    }
    )
    return (await response).json();
}

async function getClientIp() {
    const url = 'https://api.ipify.org?format=json';
    return await fetchUrl(url);

}


async function getLocalisation(ip) {
    return await fetchUrl(`${URL_API_IP}${ip}`);
}

async function getCirculation() {
    return await fetchUrl(URL_API_CIRCULATION)

}
async function getCovid() {
    return await fetch('https://www.data.gouv.fr/5e7e104ace2080d9162b61d8')
}
//---------------------------Main----------------------------
const clientIp = await getClientIp();
const clientLocalisation = await getLocalisation(clientIp.ip)
const ciruclation = await getCirculation()
// const covid = await getCovid();
console.log({
    clientIp: clientIp,
    clientLocalisation: clientLocalisation,
    circulation: ciruclation,
    // covid: covid
})




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