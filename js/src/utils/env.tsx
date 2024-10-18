/* eslint-disable @typescript-eslint/ban-ts-comment */
// @ts-nocheck

const APP_DOMAIN = 'r2_sunpay_invoice_data' as string
export const snake = window?.[APP_DOMAIN]?.env?.SNAKE || 'r2_sunpay_invoice'
export const appName = window?.[APP_DOMAIN]?.env?.APP_NAME || 'R2 Sunpay Invoice'
export const kebab = window?.[APP_DOMAIN]?.env?.KEBAB || 'r2-sunpay-invoice'
export const app1Selector = window?.[APP_DOMAIN]?.env?.APP1_SELECTOR || 'r2_sunpay_invoice'
export const app2Selector =
	window?.[APP_DOMAIN]?.env?.APP2_SELECTOR || 'r2_sunpay_invoice_metabox'
export const apiUrl = window?.wpApiSettings?.root || '/wp-json'
export const ajaxUrl =
	window?.[APP_DOMAIN]?.env?.ajaxUrl || '/wp-admin/admin-ajax.php'
export const siteUrl = window?.[APP_DOMAIN]?.env?.siteUrl || '/'
export const currentUserId = window?.[APP_DOMAIN]?.env?.userId || '0'
export const postId = window?.[APP_DOMAIN]?.env?.postId || '0'
export const permalink = window?.[APP_DOMAIN]?.env?.permalink || '/'
export const apiTimeout = '30000'
export const ajaxNonce = window?.[APP_DOMAIN]?.env?.nonce || ''
