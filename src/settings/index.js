import {
    AdminNotices,
    Container,
    Page,
} from "@goodwp/goodenberg/admin/components";
import { __experimentalVStack as VStack } from "@wordpress/components";
import domReady from "@wordpress/dom-ready";
import { createRoot } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import ErrorBoundary from "./components/error-boundary";

import "./styles/page.scss";
import Save from "./components/save";
import ModuleSettings from "./components/sections/modules";

const PageActions = () => {
    return null;
};

const SettingsPage = () => {
    return (
        <Page name="altinator-settings">
            <Page.Header
                title={ __( "Altinator Settings", "altinator" ) }
                icon="info"
                actions={ <PageActions /> }
                hasBottomMargin
            />
            <form
                name={ "altinator-settings" }
                onSubmit={ ( e ) => {
                    e.preventDefault();
                } }>
                <Container className={ "altinator-settings__content" }>
                    <VStack spacing={ 2 } justify={ "start" }>
                        <Container contained as={ "div" }>
                            <AdminNotices />
                        </Container>

                        <ModuleSettings />

                        <Save />
                    </VStack>
                </Container>
            </form>
        </Page>
    );
};

const App = () => {
    return (
        <ErrorBoundary>
            <SettingsPage />
        </ErrorBoundary>
    );
};

domReady( () => {
    const container = document.getElementById( "altinator-settings-page" );
    if ( ! container ) {
        const errorNotice = document.createElement( "div" );
        errorNotice.classList.add( "notice", "notice-error" );
        errorNotice.innerText = "Could not find container element.";
        document.getElementById( "wpbody-content" )?.append( errorNotice );
        return;
    }
    const root = createRoot( container );
    root.render( <App /> );
} );
