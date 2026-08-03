(function (global) {
    'use strict';

    var GENERIC = 'No se pudo completar la acción. Inténtalo de nuevo.';
    var TECH = /HTTP|API|FastAPI|Flask|Laravel|Docker|SQL|SQLSTATE|Connection|migraci|JSON|CSRF|token|Exception|could not|Failed to fetch|NetworkError|Unknown column|<html|502|503|504|404|500|422|proxy|NUTRIKIDS/i;

    function isUserMessage(msg) {
        return msg && typeof msg === 'string' && msg.trim().length > 0 && msg.length <= 500 && !TECH.test(msg);
    }

    function fromResponseData(data) {
        if (!data || typeof data !== 'object') {
            return GENERIC;
        }
        if (Array.isArray(data.errors) && data.errors.length) {
            var parts = data.errors.map(String).filter(isUserMessage);
            if (parts.length) {
                return parts.join(', ');
            }
        }
        if (isUserMessage(data.message)) {
            return data.message.trim();
        }
        return GENERIC;
    }

    function fromCatch(error) {
        if (error && typeof error === 'object') {
            if (Array.isArray(error.errors) && error.errors.length) {
                return fromResponseData(error);
            }
            if (error.success === false && isUserMessage(error.message)) {
                return error.message.trim();
            }
            if (isUserMessage(error.detail)) {
                return String(error.detail).trim();
            }
            if (isUserMessage(error.message)) {
                return error.message.trim();
            }
        }
        return GENERIC;
    }

    global.NutriKidsMessages = {
        GENERIC: GENERIC,
        isUserMessage: isUserMessage,
        fromResponseData: fromResponseData,
        fromCatch: fromCatch,
        resolve: fromCatch
    };
})(typeof window !== 'undefined' ? window : this);
