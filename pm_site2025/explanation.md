# PM Site 2025 - Uitleg

De files zijn via WeTransfer gestuurd.

Het geheel bestaat uit drie hoofddelen:

1. Account registratie
2. Boekingen
3. Administratie

## Account Registratie

Het eerste wat elke club moet doen is een gebruikersaccount aanmaken waarmee ze vervolgens een boeking kunnen aanmaken en wijzigen (en dat doen ze veel!). Ook een administratie-gebruiker van jullie club maakt een account aan. Die kun je daarna administrator maken door dat in de `users` tabel aan te geven. Bij het aanmaken van een account horen ook functies als activatie, password reset en dergelijke.

## Boekingen

De boekingen lopen vooral via alle files die beginnen met `booking_`, waarbij de meeste functionaliteit in `BookingClass.php` en `BookingItemsClass.php` zit.

- `BookingClass` heeft alles voor een boeking
- Een boeking bestaat uit items zoals: contacts, delegates, guests, rooms, t-shirts, travels en payments

### Stage vs. Submitted

Als een boeking wordt aangemaakt wordt deze eerst in de `stage` tabellen opgeslagen. Wanneer de boeking wordt gesubmit gaat die data naar de `_submitted` tabellen. Dit is zodat een club kan beginnen met invoeren zonder dat je voor elke punt en komma een mail krijgt dat er een nieuwe of aangepaste boeking is.

### Clubs

- Per club is maar één boeking mogelijk
- Er is een clubnaam "Other", die is gebruikt voor mensen die niet bij een club horen (zoals het FH-DCE secretariaat)
- De lijst met clubs staat in tabel `fhdce_clubs`

### Email bij submit

Als een boeking wordt gesubmit gaat er een email naar:

- Het emailadres van het account
- De contactpersoon van de club
- Een BCC naar `SITE_EMAIL`

## Administratie

Dit zit in de `admin_*` files, maar ook in `BookingClass` en `BookingItemsClass`. Als iemand inlogt en het admin-vlaggetje staat aan, wordt hij/zij automatisch naar het admin-gedeelte gestuurd.

### Functionaliteit

- Inhoud van elke boekings-gerelateerde tabel bekijken
- Data aanpassen indien nodig
- Een hele boeking verwijderen
- Herinneringsemails sturen (registratie afmaken, boeking afmaken, betalen)
- Boekingen downloaden als spreadsheet (draft of submitted), inclusief handige overzichten
- Extra items met kosten toevoegen aan een boeking (bv. extra t-shirts)
- Betalingen invoeren — de club krijgt een email met bevestiging en openstaand bedrag
- Hotel kamers toewijzen (niet gebruikt in de praktijk)

### Handmatige acties

- De eerste administrator moet met de hand in de `users` tabel als admin gezet worden. Daarna kan elke admin een andere gebruiker admin maken.
- Om het aantal guests, hotel kamers etc. hoger te zetten dan de standaard limiet: pas `max_delegates`, `max_guests`, `max_rooms` en `max_travels` aan in de `booking` tabel via phpMyAdmin.

## Configuratie

- Veel instellingen staan in `config.php`: site URL, namen, emails, adressen, logo's, prijzen, datums etc.
- Database details (inclusief uid & pwd) staan in `pmdb_secret.php` — die moet je aanpassen.
- Een item (bv. travels) kun je waarschijnlijk uitschakelen door in de class constructor `enabled` op `false` te zetten, maar dit is niet getest in de laatste versies.

## Tip

Het beste is om wat te gaan kijken en proberen, en dan een keer af te spreken om via Teams samen te kijken.
