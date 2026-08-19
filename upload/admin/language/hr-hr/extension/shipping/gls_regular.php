<?php
// Heading
$_['heading_title']                = 'GLS obična dostava';

// Text
$_['text_extension']               = 'Dostava';
$_['text_success']                 = 'Uspješno: GLS obična dostava je spremljena!';
$_['text_edit']                    = 'Izmjeni GLS običnu dostavu';
$_['text_enabled']                 = 'Omogućeno';
$_['text_disabled']                = 'Onemogućeno';
$_['text_all_zones']               = 'Sve zone';
$_['text_none']                    = 'Nema';
$_['text_api_settings']            = 'MyGLS API';
$_['text_pickup_settings']         = 'Adresa preuzimanja';
$_['text_label_settings']          = 'Postavke naljepnice';
$_['text_price_settings']          = 'Cijene po zonama';
$_['text_store_settings']          = 'Postavke trgovine';
$_['text_shipment_created']        = 'GLS naljepnica je poslana u MyGLS.';
$_['text_shipment_exists']         = 'GLS naljepnica je već poslana u MyGLS.';

// Entry
$_['entry_api_url']                = 'API URL';
$_['entry_username']               = 'Korisničko ime';
$_['entry_password']               = 'Lozinka';
$_['entry_client_number']          = 'Client number';
$_['entry_pickup_name']            = 'Naziv pošiljatelja';
$_['entry_pickup_street']          = 'Ulica pošiljatelja';
$_['entry_pickup_house_number']    = 'Kućni broj pošiljatelja';
$_['entry_pickup_house_info']      = 'Dodatak kućnom broju';
$_['entry_pickup_city']            = 'Grad pošiljatelja';
$_['entry_pickup_postcode']        = 'Poštanski broj pošiljatelja';
$_['entry_pickup_country']         = 'Država pošiljatelja';
$_['entry_pickup_email']           = 'Email pošiljatelja';
$_['entry_pickup_phone']           = 'Telefon pošiljatelja';
$_['entry_pickup_days']            = 'Pickup za dana';
$_['entry_order_prefix']           = 'Prefix broja narudžbe';
$_['entry_content']                = 'Sadržaj pošiljke';
$_['entry_printer_type']           = 'Tip naljepnice';
$_['entry_print_position']         = 'Pozicija ispisa';
$_['entry_hide_phone']             = 'Sakrij telefone na labeli';
$_['entry_cost_hr']                = 'Hrvatska';
$_['entry_cost_hr_special']        = 'Hrvatski otoci i posebna područja';
$_['entry_cost_eu_zone_1']         = 'EU Zona 1 - Slovenija, Mađarska, Slovačka, Austrija, Češka';
$_['entry_cost_eu_zone_2']         = 'EU Zona 2 - Poljska, Njemačka, Belgija, Nizozemska, Luksemburg';
$_['entry_cost_eu_zone_3']         = 'EU Zona 3 - Rumunjska, Italija, Bugarska, Danska, Irska';
$_['entry_cost_eu_zone_4']         = 'EU Zona 4 - Litva, Latvija, Estonija, Švedska, Grčka, Finska, Francuska';
$_['entry_cost_eu_zone_5']         = 'EU Zona 5 - Španjolska, Portugal, Malta, Cipar';
$_['entry_tax_class']              = 'Porezna stopa';
$_['entry_geo_zone']               = 'Geo zona';
$_['entry_status']                 = 'Status';
$_['entry_sort_order']             = 'Redoslijed sortiranja';

// Help
$_['help_api_url']                 = 'GLS REST API za Hrvatsku: %s/ParcelService.svc/json/PrepareLabels';
$_['help_prices']                  = 'Iznosi su u valuti trgovine. Zadano: Hrvatska 0, posebna područja 15, EU zone 15/18/25/30/40.';
$_['help_pickup_days']             = '0 znači danas, 1 znači sutra. GLS API prima datum preuzimanja u zahtjevu za labelu.';
$_['help_house_number']            = 'GLS traži kućni broj kao zasebno numeričko polje.';

// Button
$_['button_create_shipment']       = 'Pošalji naljepnicu u GLS';
$_['button_label']                 = 'GLS labela';

// Error
$_['error_permission']             = 'Upozorenje: Nemate ovlasti mijenjati GLS običnu dostavu!';
$_['error_missing_credentials']    = 'GLS korisničko ime, lozinka i client number moraju biti upisani za kreiranje pošiljke.';
$_['error_missing_label']          = 'GLS labela nije pronađena za ovu narudžbu.';
$_['error_not_gls_order']          = 'Narudžba nije GLS obična dostava.';
