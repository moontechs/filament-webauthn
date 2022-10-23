import {
    create,
    get,
    parseCreationOptionsFromJSON,
    parseRequestOptionsFromJSON,
    supported,
} from "@github/webauthn-json/browser-ponyfill";

const FilamentWebauthn = {
    create: async function (options) {
        return create(options);
    },

    parseCreationOptionsFromJSON: function (requestJSON) {
        return parseCreationOptionsFromJSON(requestJSON);
    },

    parseRequestOptionsFromJSON: function (requestJSON) {
        return parseRequestOptionsFromJSON(requestJSON);
    },

    get: async function (options) {
        return get(options);
    },

    supported: function () {
        return supported();
    }
}
window.FilamentWebauthn = FilamentWebauthn
