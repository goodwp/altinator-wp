import domReady from "@wordpress/dom-ready";
import * as api from "./api";
import "./styles.css";

domReady( () => {
    if ( ! window.__ALTINATOR__ ) {
        return;
    }

    const config = window.__ALTINATOR__.config;
    window.__ALTINATOR__.api = api;

    if ( config.active ) {
        api.activate();
    }
} );
