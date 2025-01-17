/**
 * External dependencies
 */
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { __ } from '@wordpress/i18n';
import { getSetting } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Internal dependencies
 */
import { PAYMENT_METHOD_NAME } from './constants';

const settings = getSetting( 'woocommerce_yourpay_settings', {} );
const defaultLabel = __( 'Yourpay payment gateway', 'yourpay' ); //Should be imported with wp_localize_script
const label = decodeEntities( settings.title ) || defaultLabel;

/**
 * @typedef {import('@woocommerce/type-defs/registered-payment-method-props').RegisteredPaymentMethodProps} RegisteredPaymentMethodProps
 */

/**
 * Content component
 */
const Content = () => {
    return <div>{ decodeEntities( settings.description || '' ) }</div>;
};

/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components;
    return <PaymentMethodLabel icon="checkPayment" text={ label } />; //CHANGE!
};

/**
 * Cheque payment method config object.
 */
const yourpay_payment_method = {
        name: PAYMENT_METHOD_NAME,
        label: <Label />,
    content: <Content />,
edit: <Content />,
    icons: null,
    canMakePayment: () => true,
    ariaLabel: label,
};

registerPaymentMethod( ( Config ) => new Config( yourpay_payment_method ) );
