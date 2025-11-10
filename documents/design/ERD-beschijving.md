# Ecopowrrr – Full ERD

---

## Customers
| Column | Type | Notes |
|--------|------|-------|
| **customer_id** | PK | Unique customer ID |
| first_name | varchar |  |
| last_name | varchar |  |
| email | varchar | Contact email |
| phone | varchar | Contact phone |
| iban | varchar | Bank account |
| status | enum(prospect, active, inactive) | Current customer status |
| created_at | datetime | When customer was added |

---

## DeviceType
| Column | Type | Notes |
|--------|------|-------|
| **device_type_id** | PK | Unique ID |
| name | varchar (unique) | e.g. solar, pump |
| description | varchar (nullable) | Optional description |

---

## Devices
| Column | Type | Notes |
|--------|------|-------|
| **device_id** | PK | Unique device identifier |
| customer_id | FK → Customers.customer_id | Linked customer |
| device_name | varchar | Display name |
| serial_nummer | varchar (unique) | Serial number from manufacturer |
| device_type_id | FK → DeviceType.device_type_id | Device type |
| status | enum(active, inactive) | Current status |
| installed_at | datetime | Installation date |
| decommissioned_at | datetime (nullable) | When device was removed |

---

## Address
| Column | Type | Notes |
|--------|------|-------|
| **address_id** | PK | Unique ID |
| postcode | varchar | Postal code |
| house_number | varchar | House number |
| street | varchar | Street name |
| city | varchar | City name |
| municipality_id | FK → Municipality.municipality_id | Linked municipality |
| latitude | decimal | Geo coordinate |
| longitude | decimal | Geo coordinate |
| UNIQUE(postcode, house_number) | constraint | Prevent duplicates |

---

## Municipality
| Column | Type | Notes |
|--------|------|-------|
| **municipality_id** | PK | Unique ID |
| name | varchar | Municipality name |

---

## CustomerAddress
| Column | Type | Notes |
|--------|------|-------|
| **customer_address_id** | PK | Unique ID |
| customer_id | FK → Customers.customer_id | Linked customer |
| address_id | FK → Address.address_id | Linked address |
| is_primary | boolean | Is this the main address? |
| valid_from | date | Start date |
| valid_to | date (nullable) | End date |

---

## DeviceMessage
| Column | Type | Notes |
|--------|------|-------|
| **message_id** | PK | Random hash from payload |
| device_id | FK → Devices.device_id | Linked device |
| device_status | enum(active, inactive) | Status at time of message |
| message_datetime | datetime | Time message sent |
| raw_json | text/json | Raw data payload |
| processed_at | datetime (nullable) | Processing timestamp |
| processing_status | enum(ok, error) | Message handling result |

---

## ReadingBatch
| Column | Type | Notes |
|--------|------|-------|
| **batch_id** | PK | Unique batch ID |
| collected_at | datetime | Timestamp when readings collected |
| total_usage_kwh | decimal | Total household usage (kWh) |
| source_message_id | FK → DeviceMessage.message_id (nullable) | Originating message |

---

## DeviceReading
| Column | Type | Notes |
|--------|------|-------|
| **device_reading_id** | PK | Unique ID |
| batch_id | FK → ReadingBatch.batch_id | Linked batch |
| device_id | FK → Devices.device_id | Linked device |
| reading_timestamp | datetime | Time of reading |
| device_total_yield_kwh | decimal | Total energy produced |
| device_month_yield_kwh | decimal | Monthly energy produced |
| device_status | enum(active, inactive) | Status at that time |
| UNIQUE(device_id, reading_timestamp) | constraint | Prevent duplicates |

---

## BuyPricePeriod
| Column | Type | Notes |
|--------|------|-------|
| **price_period_id** | PK | Unique ID |
| valid_from | date/datetime | Start of price period |
| valid_to | date/datetime (nullable) | End of price period (null = current) |
| buy_price_per_kwh | decimal(10,4) | Price per kWh (EUR) |
| constraint | no-overlap | Ensure periods don’t overlap |

---

## EnergyPurchase
| Column | Type | Notes |
|--------|------|-------|
| **purchase_id** | PK | Unique record ID |
| customer_id | FK → Customers.customer_id | Linked customer |
| device_id | FK → Devices.device_id (nullable) | Linked device |
| reading_id | FK → DeviceReading.device_reading_id | Reading used for calculation |
| price_period_id | FK → BuyPricePeriod.price_period_id | Price applied |
| kwh | decimal | Energy bought (kWh) |
| amount_eur | decimal | Calculated `kwh × price` |
| purchase_date | datetime | When purchase occurred |

---

## Relationships (Summary)
| Relationship | Type |
|--------------|------|
| Customers 1—* Devices | A customer can own many devices |
| DeviceType 1—* Devices | A type can belong to many devices |
| Customers 1—* CustomerAddress *—1 Address | Customers have addresses |
| Address —* Municipality | Addresses belong to a municipality |
| Devices 1—* DeviceMessage | Each device sends many messages |
| ReadingBatch 1—* DeviceReading | Each batch contains many readings |
| Devices 1—* DeviceReading | Each device produces readings |
| BuyPricePeriod 1—* EnergyPurchase | Each period used by multiple purchases |
| Customers 1—* EnergyPurchase | Each customer generates purchases |
| DeviceReading 1—* EnergyPurchase | Purchases linked to readings |
