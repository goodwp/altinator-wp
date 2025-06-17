import { Container } from "@goodwp/goodenberg/admin/components";
import { __experimentalHStack as HStack, Button } from "@wordpress/components";
import { store as coreDataStore } from "@wordpress/core-data";
import { useDispatch, useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import { store as noticesStore } from "@wordpress/notices";

import "./styles.scss";

const Save = () => {
    const { saveEditedEntityRecord } = useDispatch( coreDataStore );
    const { createSuccessNotice, createErrorNotice } =
        useDispatch( noticesStore );
    const { getLastEntitySaveError: getSaveError } = useSelect( coreDataStore );
    const { hasEdits, isSaving } = useSelect( ( select ) => {
        const { isSavingEntityRecord, hasEditsForEntityRecord } =
            select( coreDataStore );
        return {
            hasEdits: hasEditsForEntityRecord( "root", "site" ),
            isSaving: isSavingEntityRecord( "root", "site" ),
        };
    } );
    const handleSave = async () => {
        const saved = await saveEditedEntityRecord( "root", "site" );
        if ( saved ) {
            createSuccessNotice( __( "Settings saved!", "altinator" ), {
                type: "snackbar",
            } );
        } else {
            const lastError = getSaveError( "root", "site" );
            const message = lastError?.message || __( "There was an error." );
            createErrorNotice( message, {
                type: "snackbar",
            } );
        }
    };
    return (
        <Container contained as={ "div" }>
            <HStack justify={ "end" }>
                <Button
                    variant={ "primary" }
                    label={ __( "Save Settings", "altinator" ) }
                    onClick={ handleSave }
                    disabled={ ! hasEdits }
                    isBusy={ isSaving }>
                    { __( "Save" ) }
                </Button>
            </HStack>
        </Container>
    );
};

export default Save;
