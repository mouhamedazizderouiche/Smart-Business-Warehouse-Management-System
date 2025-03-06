const Encore = require('@symfony/webpack-encore');
const path = require('path');

// Configure Webpack Encore
Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

// Entrées pour les fichiers JavaScript
.addEntry('app', './assets/app.js')
    .addEntry('map', './assets/map.js')

// Active le support pour le SCSS (facultatif)
.enableSassLoader()

// Active la gestion des cartes sources (utile pour le développement)
.enableSourceMaps(!Encore.isProduction())

// Active la gestion de la version des fichiers pour la mise en cache
.enableVersioning()

// Exporte la configuration
module.exports = Encore.getWebpackConfig();