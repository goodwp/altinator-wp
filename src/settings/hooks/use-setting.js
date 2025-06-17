import { useEntityRecord } from "@wordpress/core-data";
import { useCallback } from "@wordpress/element";

/**
 * Data handling taken from home-template-details in gutenberg
 * @param {string} key
 * @param {*} defaultValue
 * @param {boolean} prefix
 * @returns
 */
const useSetting = ( key, defaultValue = null, prefix = true ) => {
    const {
        editedRecord = {},
        edit,
        ...record
    } = useEntityRecord( "root", "site" );

    const fullKey = prefix ? `altinator_${ key }` : key;

    const value = editedRecord?.[ fullKey ] || defaultValue;
    const setValue = useCallback(
        ( newValue ) => {
            edit( {
                [ fullKey ]: newValue,
            } );
        },
        [ edit ]
    );

    return {
        value: value,
        setValue: setValue,
        isLoading: record.isResolving,
    };
};

/**
 * A React hook for managing and retrieving settings for a given set of keys,
 * utilizing an entity record system to fetch and update data.
 *
 * @function useSettings
 * @param {string[]} keys - An array of setting keys to manage and retrieve.
 * @param {Object} [defaultValues=null] - Optional. A default object mapping keys to their default values.
 * If not provided, defaults to null for all keys.
 * @param {boolean} prefix
 * @returns {Object} An object with the following properties:
 * - `values`: An object containing the current values for the given keys, with fallback to `defaultValues`.
 * - `setValues`: A function to update multiple settings at once.
 * - `isLoading`: A boolean indicating whether the data is still resolving.
 */
const useSettings = ( keys, defaultValues = null, prefix = true ) => {
    const fullKeyMap = keys.reduce( ( acc, key ) => {
        acc[ key ] = prefix ? `altinator_${ key }` : key;
        return acc;
    }, {} );

    if ( defaultValues === null ) {
        defaultValues = keys.reduce( ( acc, key ) => {
            acc[ key ] = null;
        }, {} );
    }

    const {
        editedRecord = {},
        edit,
        ...record
    } = useEntityRecord( "root", "site" );

    const values = keys.reduce( ( acc, key ) => {
        acc[ key ] =
            editedRecord?.[ fullKeyMap[ key ] ] || defaultValues[ key ];
        return acc;
    }, {} );

    const setValues = useCallback(
        ( newValues ) => {
            const prefixedNewValues = Object.keys( newValues ).reduce(
                ( acc, key ) => {
                    acc[ fullKeyMap[ key ] ] = newValues[ key ];
                    return acc;
                },
                {}
            );
            edit( {
                ...prefixedNewValues,
            } );
        },
        [ edit ]
    );

    return {
        values: values,
        setValues: setValues,
        isLoading: record.isResolving,
    };
};

export { useSetting, useSettings };
