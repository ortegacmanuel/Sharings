// translations
var sharingsTranslations = {
    sv: {
        sharingsLongHeadline: "Share",
        sharingsShortHeadline: "Share",
        catalogLongHeadline: "Catalog",
        catalogShortHeadline: "Catalog"

    },
    eo: {
        sharingsLongHeadline: "Kundividu objektojn kaj servojn en la reto",
        sharingsShortHeadline: "Kundividi",
        catalogLongHeadline: "Vidi la katalogon de kundividitaj objektoj kaj servoj",
        catalogShortHeadline: "Katalogo"
    },
    en: {
        sharingsLongHeadline: "Share objects and services in the network",
        sharingsShortHeadline: "Share",
        catalogLongHeadline: "Display the catalog of shared objects and services",
        catalogShortHeadline: "Catalog"
    },
    es: {
        sharingsLongHeadline: "Comparte objetos y servicios en la red",
        sharingsShortHeadline: "Compartir",
        catalogLongHeadline: "Ver el catálogo de objetos y servicios compartidos",
        catalogShortHeadline: "Catálogo"
    }
};

// add translation to Qvitter
window.pluginTranslations.push(sharingsTranslations);

// stuff that uses language needs to be done after Qvitter has set the language
$(document).bind('qvitterAfterLanguageIsSet', function() {


    // add new sharing to menu-container in UI
    $('#main-menu').append('\
        <a class="stream-selection" href="' + window.siteInstanceURL + 'main/sharings/new" data-tooltip="' + window.sL.sharingsLongHeadline + '">\
            ' + window.sL.sharingsShortHeadline + '\
            <i class="chev-right"></i>\
        </a>');

    // add show catalog to menu-container in UI
    $('#main-menu').append('\
        <a class="stream-selection" href="' + window.siteInstanceURL + 'tag/sharings" data-tooltip="' + window.sL.catalogLongHeadline + '">\
            ' + window.sL.catalogShortHeadline + '\
            <i class="chev-right"></i>\
        </a>');
});
