import L from 'leaflet';

// Créer une carte centrée sur une localisation donnée
const map = L.map('map').setView([51.505, -0.09], 13); // Coordonnées de la carte

// Ajouter un "tile layer" pour OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Ajouter un marqueur à la carte
L.marker([51.5, -0.09]).addTo(map)
    .bindPopup('Hello World!')
    .openPopup();