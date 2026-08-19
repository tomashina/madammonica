<?php
// Heading
$_['heading_title'] = 'Ercune SalesOrder';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: you have modified Ercune SalesOrder settings!';
$_['text_edit'] = 'Edit Ercune SalesOrder settings';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_api'] = 'API';
$_['text_payload'] = 'Payload';
$_['text_catalogue'] = 'Catalogue';
$_['text_payment'] = 'Payment';

// Entry
$_['entry_status'] = 'Status';
$_['entry_api_url'] = 'API URL';
$_['entry_username'] = 'Username';
$_['entry_secret_key'] = 'Secret key';
$_['entry_token'] = 'Token';
$_['entry_send_email'] = 'Send document by email';
$_['entry_valid_until_days'] = 'Quote validity (days)';
$_['entry_default_country'] = 'Default country';
$_['entry_company_custom_field_id'] = 'Company custom field ID';
$_['entry_tax_custom_field_id'] = 'Tax/VAT custom field ID';
$_['entry_shipping_product_code'] = 'Shipping product code';
$_['entry_shipping_product_name'] = 'Shipping product name';
$_['entry_method_cod'] = 'COD payment';
$_['entry_method_bank_transfer'] = 'Bank transfer payment';
$_['entry_method_default'] = 'Default payment method';
$_['entry_ensure_products'] = 'Upsert products before sending';
$_['entry_debug'] = 'Debug log';

// Help
$_['help_api_url'] = 'Base Ercune API URL. The module appends WebServices/API.';
$_['help_send_email'] = 'Sends sendIssuedInvoiceByEmail=true in SalesOrderCreate/SalesQuoteCreate parameters.';
$_['help_custom_fields'] = 'Defaults: 1 = company, 2 = tax/VAT number, matching existing Agmedia projects.';
$_['help_ensure_products'] = 'Runs ProductImport createOrUpdate for products and shipping before SalesOrderCreate. If the API user has no ProductImport permission, the module skips that step and continues.';
$_['help_debug'] = 'Writes requests/responses to system/storage/logs/ercune_sales_order.log.';

// Error
$_['error_permission'] = 'Warning: you do not have permission to modify Ercune SalesOrder!';
$_['error_disabled'] = 'Ercune SalesOrder module is disabled.';
$_['error_api_url'] = 'API URL is required.';
$_['error_username'] = 'Username is required.';
$_['error_secret_key'] = 'Secret key is required.';
$_['error_token'] = 'Token is required.';
