// translations
var sharingsTranslations = {
    sv: {
        sharingsLongHeadline: "Share",
        sharingsShortHeadline: "Share"
    },
    eo: {
        sharingsLongHeadline: "Kundividu objektojn kaj servojn en la reto",
        sharingsShortHeadline: "Kundividi"
    },
    en: {
        sharingsLongHeadline: "Share objects and services in the network",
        sharingsShortHeadline: "Share"
    },
    es: {
        sharingsLongHeadline: "Comparte objetos y servicios en la red",
        sharingsShortHeadline: "Compartir"
    }
};

// add translation to Qvitter
window.pluginTranslations.push(sharingsTranslations);

// stuff that uses language needs to be done after Qvitter has set the language
$(document).bind('qvitterAfterLanguageIsSet', function() {


    // add new sharing to menu-container in UI
    $('#main-menu').append('\
        <a class="stream-selection" href="' + window.siteInstanceURL + 'main/sharings/new" data-tooltip="' + window.sL.sharinsLongHeadline + '">\
            ' + window.sL.sharingsShortHeadline + '\
            <i class="chev-right"></i>\
        </a>');
});
