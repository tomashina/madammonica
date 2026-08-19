<?php
// Heading
$_['heading_title']                = 'GLS regular delivery';

// Text
$_['text_extension']               = 'Shipping';
$_['text_success']                 = 'Success: GLS regular delivery settings have been saved!';
$_['text_edit']                    = 'Edit GLS regular delivery';
$_['text_enabled']                 = 'Enabled';
$_['text_disabled']                = 'Disabled';
$_['text_all_zones']               = 'All Zones';
$_['text_none']                    = 'None';
$_['text_api_settings']            = 'MyGLS API';
$_['text_pickup_settings']         = 'Pickup address';
$_['text_label_settings']          = 'Label settings';
$_['text_price_settings']          = 'Zone prices';
$_['text_store_settings']          = 'Store settings';
$_['text_shipment_created']        = 'GLS label has been sent to MyGLS.';
$_['text_shipment_exists']         = 'GLS label has already been sent to MyGLS.';

// Entry
$_['entry_api_url']                = 'API URL';
$_['entry_username']               = 'Username';
$_['entry_password']               = 'Password';
$_['entry_client_number']          = 'Client number';
$_['entry_pickup_name']            = 'Sender name';
$_['entry_pickup_street']          = 'Sender street';
$_['entry_pickup_house_number']    = 'Sender house number';
$_['entry_pickup_house_info']      = 'House number info';
$_['entry_pickup_city']            = 'Sender city';
$_['entry_pickup_postcode']        = 'Sender postcode';
$_['entry_pickup_country']         = 'Sender country';
$_['entry_pickup_email']           = 'Sender email';
$_['entry_pickup_phone']           = 'Sender phone';
$_['entry_pickup_days']            = 'Pickup days offset';
$_['entry_order_prefix']           = 'Order number prefix';
$_['entry_content']                = 'Parcel content';
$_['entry_printer_type']           = 'Label type';
$_['entry_print_position']         = 'Print position';
$_['entry_hide_phone']             = 'Hide phones on labels';
$_['entry_cost_hr']                = 'Croatia';
$_['entry_cost_hr_special']        = 'Croatian islands and special areas';
$_['entry_cost_eu_zone_1']         = 'EU Zone 1 - Slovenia, Hungary, Slovakia, Austria, Czechia';
$_['entry_cost_eu_zone_2']         = 'EU Zone 2 - Poland, Germany, Belgium, Netherlands, Luxembourg';
$_['entry_cost_eu_zone_3']         = 'EU Zone 3 - Romania, Italy, Bulgaria, Denmark, Ireland';
$_['entry_cost_eu_zone_4']         = 'EU Zone 4 - Lithuania, Latvia, Estonia, Sweden, Greece, Finland, France';
$_['entry_cost_eu_zone_5']         = 'EU Zone 5 - Spain, Portugal, Malta, Cyprus';
$_['entry_tax_class']              = 'Tax class';
$_['entry_geo_zone']               = 'Geo zone';
$_['entry_status']                 = 'Status';
$_['entry_sort_order']             = 'Sort order';

// Help
$_['help_api_url']                 = 'GLS REST API for Croatia: %s/ParcelService.svc/json/PrepareLabels';
$_['help_prices']                  = 'Amounts are in the store currency. Defaults: Croatia 0, special areas 15, EU zones 15/18/25/30/40.';
$_['help_pickup_days']             = '0 means today, 1 means tomorrow.';
$_['help_house_number']            = 'GLS requires the house number as a separate numeric field.';

// Button
$_['button_create_shipment']       = 'Send label to GLS';
$_['button_label']                 = 'GLS label';

// Error
$_['error_permission']             = 'Warning: You do not have permission to modify GLS regular delivery!';
$_['error_missing_credentials']    = 'GLS username, password and client number are required to create shipments.';
$_['error_missing_label']          = 'GLS label was not found for this order.';
$_['error_not_gls_order']          = 'The order is not GLS regular delivery.';
