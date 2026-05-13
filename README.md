# System-rezerwacji-miejsc-w-głównej-sali-Uniwersytetu-UWB

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Local%20Server-FB7A24?logo=xampp&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-Web%20Server-D22128?logo=apache&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Frontend-F7DF1E?logo=javascript&logoColor=black)
![CSS](https://img.shields.io/badge/CSS3-Styling-1572B6?logo=css3&logoColor=white)

Projekt studencki przedstawiający system rezerwacji miejsc w głównej sali Uniwersytetu UWB. Aplikacja umożliwia logowanie i rejestrację użytkowników, przegląd dostępnych wydarzeń, rezerwację miejsc oraz zarządzanie wydarzeniami i miejscami przez administratora.

## Cel projektu

Celem projektu było stworzenie prostej aplikacji webowej wspierającej organizację wydarzeń oraz rezerwację miejsc na sali. System rozdziela uprawnienia użytkowników i administratorów, dzięki czemu możliwa jest zarówno obsługa rezerwacji, jak i zarządzanie wydarzeniami w jednym miejscu.

## Funkcjonalności

### Użytkownik
- logowanie do systemu
- rejestracja nowego konta
- przegląd listy wydarzeń
- rezerwacja jednego lub wielu miejsc
- podgląd własnych rezerwacji
- anulowanie własnej rezerwacji

### Administrator
- logowanie do panelu administratora
- dodawanie nowych wydarzeń
- edycja wydarzeń
- usuwanie wydarzeń
- ręczne zarządzanie miejscami

### Gość
- przegląda listę wydarzeń
- sprawdza dostępność miejsc
- nie posiada uprawnień rezerwacyjnych

## Technologie

- PHP
- JavaScript
- CSS
- MySQL / MariaDB
- XAMPP
- Apache
- phpMyAdmin

## Struktura katalogów

```text
index.php        - główny plik aplikacji
README.md        - dokumentacja projektu
register.php     - formularz rejestracji

actions/         - logika operacji użytkownika i administratora
  cancel_own_reservation.php
  create_event.php
  delete_event.php
  login.php
  manage_seats.php
  reserve_seats.php
  update_event.php

assets/
  css/           - style aplikacji
    auth.css
    main.css
    seat-map.css
  js/            - skrypty JavaScript
    app.js
    auth.js
    seat-map.js

IMG/
  uwb_wilno_logo.png

includes/        - funkcje pomocnicze
  functions.php

queries/         - zapytania do bazy danych
  events.php

views/
  partials/      - części widoków
    auth_section.php
    create_event_modal.php
    event_card.php
    messages.php
    reservation_modal.php
```

## Uruchomienie lokalne

1. Skopiuj projekt do folderu `C:\xampp\htdocs\...`
2. Uruchom `Apache` i `MySQL` w XAMPP
3. W phpMyAdmin utwórz bazę danych `uwb_rezerwacje`
4. Zaimportuj plik `.sql`
5. Otwórz aplikację w przeglądarce z poziomu localhost

## Dane testowe

### Administrator
- login: `admin@uwb.local`
- hasło: `Admin132!`

### Użytkownik
- login: `user1@uwb.local`
- hasło: `User123!`
 
## Autorzy
[Fabian Žavoronok](https://github.com/FNKM625), [Adam Romaševski](https://github.com/AdamRoomka)