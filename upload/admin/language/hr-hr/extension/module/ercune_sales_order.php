<?php
// Heading
$_['heading_title'] = 'Ercune SalesOrder';

// Text
$_['text_extension'] = 'Prosirenja';
$_['text_success'] = 'Uspjesno: promijenili ste Ercune SalesOrder postavke!';
$_['text_edit'] = 'Uredi Ercune SalesOrder postavke';
$_['text_enabled'] = 'Ukljuceno';
$_['text_disabled'] = 'Iskljuceno';
$_['text_api'] = 'API';
$_['text_payload'] = 'Payload';
$_['text_catalogue'] = 'Katalog';
$_['text_payment'] = 'Placanje';

// Entry
$_['entry_status'] = 'Status';
$_['entry_api_url'] = 'API URL';
$_['entry_username'] = 'Username';
$_['entry_secret_key'] = 'Secret key';
$_['entry_token'] = 'Token';
$_['entry_send_email'] = 'Posalji dokument emailom';
$_['entry_valid_until_days'] = 'Valjanost ponude (dana)';
$_['entry_default_country'] = 'Zadana drzava';
$_['entry_company_custom_field_id'] = 'Custom field ID za tvrtku';
$_['entry_tax_custom_field_id'] = 'Custom field ID za OIB/VAT';
$_['entry_shipping_product_code'] = 'Sifra dostave';
$_['entry_shipping_product_name'] = 'Naziv dostave';
$_['entry_method_cod'] = 'Placanje pouzece';
$_['entry_method_bank_transfer'] = 'Placanje bank transfer';
$_['entry_method_default'] = 'Zadani nacin placanja';
$_['entry_ensure_products'] = 'Upsert artikle prije slanja';
$_['entry_debug'] = 'Debug log';

// Help
$_['help_api_url'] = 'Bazni URL Ercune API-ja, npr. https://.../ ili https://.../WebServices/. Modul dodaje WebServices/API endpoint.';
$_['help_send_email'] = 'Salje sendIssuedInvoiceByEmail=true u SalesOrderCreate/SalesQuoteCreate parametrima.';
$_['help_custom_fields'] = 'Zadano: 1 = tvrtka, 2 = OIB/VAT kao u postojecim Agmedia projektima.';
$_['help_ensure_products'] = 'Prije slanja radi ProductImport createOrUpdate za artikle i dostavu. Ako API user nema ProductImport prava, modul preskace taj korak i nastavlja slanje.';
$_['help_debug'] = 'Upisuje request/response u system/storage/logs/ercune_sales_order.log.';

// Error
$_['error_permission'] = 'Upozorenje: nemate ovlasti za izmjenu Ercune SalesOrder modula!';
$_['error_disabled'] = 'Ercune SalesOrder modul nije ukljucen.';
$_['error_api_url'] = 'API URL je obavezan.';
$_['error_username'] = 'Username je obavezan.';
$_['error_secret_key'] = 'Secret key je obavezan.';
$_['error_token'] = 'Token je obavezan.';
