// translations
var sharingsTranslations = {
    sv: {
        sharingsShortHeadline: "Share"
    },
    eo: {
        sharingsShortHeadline: "Kundividi"
    },
    en: {
        sharingsShortHeadline: "Share"
    },
    es: {
        sharingsShortHeadline: "Compartir"
    }
};

// add translation to Qvitter
window.pluginTranslations.push(sharingsTranslations);

// stuff that uses language needs to be done after Qvitter has set the language
$(document).bind('qvitterAfterLanguageIsSet', function() {


    // add new sharing to menu-container in UI
    $('#main-menu').append('\
        <a class="stream-selection" href="' + window.siteInstanceURL + 'sharings/new" data-tooltip="' + window.sL.sharinsLongHeadline + '">\
            ' + window.sL.sharingsShortHeadline + '\
            <i class="chev-right"></i>\
        </a>');
});
