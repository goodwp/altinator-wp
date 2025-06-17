import { SnackbarList } from "@wordpress/components";
import { useDispatch, useSelect } from "@wordpress/data";
import domReady from "@wordpress/dom-ready";
import { createRoot } from "@wordpress/element";
import { store as noticesStore } from "@wordpress/notices";

import "./styles.css";

const NOTICE_CONTEXT = "altinator";

/**
 * Renders a list of snackbar notifications, just like in the block editor.
 *
 * @param {Object} props Any props passed to the SnackbarList component.
 * @return {React.ReactElement}
 * @class
 */
const NotificationsList = ( props ) => {
    const notices = useSelect(
        ( select ) => select( noticesStore ).getNotices( NOTICE_CONTEXT ),
        []
    );
    const { removeNotice } = useDispatch( noticesStore );
    const snackbarNotices = notices.filter(
        ( { type } ) => type === "snackbar"
    );
    return (
        <SnackbarList
            { ...props }
            className={ "altinator-notices" }
            id={ "AltinatorNotices-List" }
            notices={ snackbarNotices }
            onRemove={ ( noticeId ) =>
                removeNotice( noticeId, NOTICE_CONTEXT )
            }
        />
    );
};

domReady( () => {
    const wpBodyContent = document.getElementById( "wpbody-content" );
    if ( ! wpBodyContent ) {
        const errorNotice = document.createElement( "div" );
        errorNotice.classList.add( "notice", "notice-error" );
        errorNotice.innerText = "Could not find container element.";
        document.getElementById( "wpbody-content" )?.append( errorNotice );
        return;
    }

    const container = document.createElement( "div" );
    container.setAttribute( "id", "AltinatorNotices" );
    wpBodyContent.appendChild( container );
    const root = createRoot( container );
    root.render( <NotificationsList /> );
} );
