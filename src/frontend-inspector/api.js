const IMG_SELECTOR = "img[data-altinator]";
const STATUS_ELEMENT_SELECTOR = `img[data-altinator] + .altinator-frontend-inspector-status`;

const activate = () => {
    window.__ALTINATOR__.active = true;
    document.body.classList.add( "has-altinator-frontend-inspector" );
    document
        .getElementById( "wp-admin-bar-altinator-inspector" )
        .setAttribute( "aria-pressed", "true" );

    // Add tabindex="0" to all images with data-altinator attribute to make them focusable
    document.querySelectorAll( IMG_SELECTOR ).forEach( ( img ) => {
        img.setAttribute( "tabindex", "0" );
    } );
};

const deactivate = () => {
    window.__ALTINATOR__.active = false;
    document.body.classList.remove( "has-altinator-frontend-inspector" );
    document
        .getElementById( "wp-admin-bar-altinator-inspector" )
        .setAttribute( "aria-pressed", "false" );

    // Remove tabindex from all images with data-altinator attribute to make them unfocusable
    document.querySelectorAll( IMG_SELECTOR ).forEach( ( statusElement ) => {
        statusElement.removeAttribute( "tabindex" );
    } );
};

const isActive = () => {
    return !! window.__ALTINATOR__?.active;
};

const toggle = () => {
    isActive() ? deactivate() : activate();
};

export { activate, deactivate, isActive, toggle };
