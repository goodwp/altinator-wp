/**
 * WordPress dependencies
 */
import { Warning } from "@wordpress/block-editor";
import { Button } from "@wordpress/components";
import { useCopyToClipboard } from "@wordpress/compose";
import { Component } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

const CopyButton = ( { text, children } ) => {
    const ref = useCopyToClipboard( text );
    return (
        <Button __next40pxDefaultSize variant="secondary" ref={ ref }>
            { children }
        </Button>
    );
};

const formattedError = ( error ) => {
    return `${ error.message } on ${ error.columnNumber }:${ error.lineNumber } in ${ error.fileName }. Stack: ${ error.stack }`;
};

export default class ErrorBoundary extends Component {
    constructor() {
        super( ...arguments );

        this.state = {
            error: null,
        };
    }

    static getDerivedStateFromError( error ) {
        return { error };
    }

    componentDidCatch( error ) {
        console.log( error );
    }

    render() {
        if ( ! this.state.error ) {
            return this.props.children;
        }

        const actions = [
            <CopyButton
                key="copy-error"
                text={ formattedError( this.state.error ) }>
                { __( "Copy Error" ) }
            </CopyButton>,
        ];

        return (
            <Warning className="editor-error-boundary" actions={ actions }>
                { __( "Something went wrong." ) }
            </Warning>
        );
    }
}
