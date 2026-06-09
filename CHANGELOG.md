# Changelog

Alle wichtigen Änderungen an dieser Extension werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

## [1.3.1] - 2026-06-09

### Behoben

- **Magento 2.4.7 / 2.4.8 Kompatibilität:** Composer-Constraint `magento/module-email: ^101.2` auf `^101.1` korrigiert. Version `101.2` existiert in keinem Magento-2.4.x-Release (höchste ist `101.1.x`), wodurch `composer require storetown/module-tax-sync` mit „could not be resolved" fehlschlug. Betraf alle bisherigen Releases (1.1.0–1.3.0).

---

## [1.3.0] - 2026-05-14

### Geändert

- **Erstes öffentliches Open-Source-Release auf GitHub** unter MIT-Lizenz
- **Distribution via Packagist** — `composer require storetown/module-tax-sync` ohne Custom-Repository möglich

### Entfernt (für OSS-Release)

- `stm/module-license` Dependency aus `composer.json` — Extension läuft jetzt ohne Lizenz-Check, voll funktional für alle User
- `STM_License`-Sequence aus `etc/module.xml`
- `STM\License\Model\ExtensionRegistry`-Block aus `etc/di.xml`
- `LicenseServiceInterface`-Import + Constructor-Injection + License-Check in `Model/TaxSyncService.php`

### Hinzugefügt (OSS-Hygiene)

- `.github/ISSUE_TEMPLATE/bug_report.md` — strukturiertes Bug-Report-Template
- `.github/ISSUE_TEMPLATE/feature_request.md` — Feature-Request-Template
- `SECURITY.md` — Security-Policy mit responsible-disclosure-Workflow
- `CONTRIBUTING.md` — Contributor-Guide (PSR-12, Magento-2-Standards, Conventional Commits)
- `composer.json` — `support.issues`, `support.source`, `support.docs` aktualisiert auf den neuen GitHub-Repo
- README erweitert um Cross-Linking zu den 4 kommerziellen Schwester-Extensions

---

## [1.2.0] - 2026-03-09

### Hinzugefügt

- STM License Integration — Lizenzprüfung über `stm/module-license` Modul
- `di.xml` — Extension-Registrierung bei `STM\License\Model\ExtensionRegistry` (Key: `storetown_taxsync`)

### Geändert

- `composer.json` — Dependency `stm/module-license: ^1.0` hinzugefügt
- `module.xml` — `STM_License` in Modul-Sequence aufgenommen

---

## [1.1.0] - 2026-03-07

### Behoben

- **CRITICAL:** Cron-Job lief nie — Backend Model `CronFrequency` erstellt, das Frequenz-Auswahl in Cron-Expression schreibt
- **HIGH:** `ApiSourcePool` konnte bei fehlendem Fallback crashen — Defensive Prüfung mit `LocalizedException` hinzugefügt
- **HIGH:** `TaxSyncService::getLastSyncDate()` versuchte auf geschütztes `scopeConfig`-Property zuzugreifen — Methode in `Config` Helper verlagert
- **HIGH:** Generische Exceptions durch `LocalizedException` ersetzt
- **HIGH:** Concurrency-Schutz via `LockManagerInterface` im Sync-Prozess
- **HIGH:** Fehlendes `lockManager`-Argument in `di.xml` ergänzt
- **HIGH:** `<e>` Tag in `StatusCommand` durch `<error>` ersetzt (Symfony Console 6.x Kompatibilität)
- **MEDIUM:** Modul-Versionanzeige in Info-Block nutzt jetzt `PackageInfo` statt veraltetes `setup_version`
- **MEDIUM:** Hardcoded German Strings in `NotificationService` durch `__()` Aufrufe ersetzt (i18n-fähig)

### Hinzugefügt

- `Model/Config/Backend/CronFrequency.php` — Backend Model für Cron-Frequenz-Konfiguration
- `view/adminhtml/email/sync_error.html` — Fehler-Benachrichtigungs-E-Mail-Template
- `etc/csp_whitelist.xml` — CSP-Whitelist für euvatrates.com API-Aufrufe
- `i18n/de_DE.csv` — Deutsche Übersetzungen (34 Strings)
- `phpunit.xml.dist` — PHPUnit Konfiguration
- 9 PHPUnit Test-Dateien mit 82 Tests und 471 Assertions
- Copyright-Header auf allen PHP-Dateien
- PHPCS Magento2 Coding Standard — vollständig compliant (0 Errors, 0 Warnings)

### Geändert

- `module.xml` — `setup_version` entfernt (Magento 2.4+ Best Practice), Sequence-Dependencies erweitert
- `composer.json` — `magento/module-cron` Dependency hinzugefügt, Version-Constraints auf `^` (Caret) umgestellt
- `config.xml` — Standard Cron-Schedule `0 3 * * 1` (Montag 3:00 Uhr) als Default hinzugefügt
- Code-Qualität: Alle Dateien auf PHPCS Magento2 Standard geprüft und angepasst

---

## [1.0.3] - 2026-01-22

### Hinzugefügt

- `NotificationService` — Zentrale Klasse für E-Mail- und Admin-Benachrichtigungen
- E-Mail-Template `rate_changed.html` für Steuersatz-Änderungen
- Admin-Notification über `Magento\AdminNotification\Model\Inbox` bei Änderungen
- Konfigurationsoptionen für Benachrichtigungen unter `tax_sync/notifications/`
- `email_templates.xml` — Template-Registrierung für Transaktions-E-Mails

### Geändert

- `TaxSyncService::sync()` ruft jetzt `NotificationService` nach erfolgreicher Synchronisierung auf
- `di.xml` — `NotificationService` als Dependency für `TaxSyncService` ergänzt
- Admin-Konfiguration um Benachrichtigungs-Tab erweitert (`system.xml`)

### Behoben

- Rate-Vergleich nutzt jetzt Toleranz `abs($old - $new) > 0.001` statt striktem `!=` (Float-Precision)
- Sync-Ergebnis enthält jetzt detailliertes `changes`-Array mit altem und neuem Steuersatz

---

## [1.0.2] - 2025-12-12

### Hinzugefügt

- `Block/Adminhtml/System/Config/SyncButton.php` — Manueller Sync-Button in Admin-Konfiguration
- `Block/Adminhtml/System/Config/LastSync.php` — Anzeige des letzten Sync-Zeitpunkts
- `Block/Adminhtml/System/Config/Info.php` — Modul-Infoanzeige mit Version und Status
- `Controller/Adminhtml/Sync/Run.php` — AJAX-Controller für manuellen Sync aus Admin
- `etc/adminhtml/routes.xml` — Admin-Routen für Sync-Controller
- `etc/acl.xml` — ACL-Ressource `Storetown_TaxSync::config` für Zugriffskontrolle
- Admin-Konfiguration um Sync-Tab mit Buttons und Status-Anzeige erweitert

### Geändert

- `system.xml` — Komplette Überarbeitung der Admin-Oberfläche mit drei Tabs (Allgemein, Tax Rules, Sync)
- `Config` Helper um `getLastSyncDate()` erweitert
- `TaxSyncService::sync()` speichert jetzt Timestamp des letzten Syncs in Core-Config

### Behoben

- Country-Filter: API-Daten werden jetzt korrekt gegen konfigurierte Länderliste gefiltert
- Reduced-Rate wird nur importiert wenn > 0 (verhindert leere 0%-Steuersätze)

---

## [1.0.1] - 2025-11-18

### Hinzugefügt

- `Model/Api/VatComply.php` — Alternative API-Quelle (vatcomply.com) als zweite Datenquelle
- `Model/ApiSourcePool.php` — Source-Pool-Pattern für flexible API-Quellen-Verwaltung
- `Api/TaxRateSourceInterface.php` — Interface für austauschbare API-Quellen
- `Model/Config/Source/ApiSource.php` — Dropdown für API-Quellen-Auswahl in Admin
- Konfigurationsoption `tax_sync/general/api_source` zur Auswahl der Datenquelle
- Automatischer Fallback: Wenn primäre API nicht erreichbar, wird nächste Quelle probiert
- `StatusCommand` — CLI-Befehl `tax:sync:status` zeigt aktuellen Sync-Status und Konfiguration
- `Logger/Logger.php` und `Logger/Handler.php` — Dediziertes Logging nach `var/log/tax_sync.log`

### Geändert

- `TaxSyncService` refactored — API-Aufruf über `ApiSourcePool` statt direktem HTTP-Client
- `di.xml` — Source-Pool mit konfigurierbarer Quellen-Liste
- `system.xml` — API-Quellen-Auswahl im Admin hinzugefügt
- Logging von `Psr\Log\LoggerInterface` auf dedizierten `TaxSync\Logger\Logger` umgestellt

### Behoben

- Tax Rate Code-Format vereinheitlicht: `{COUNTRY}-Standard` / `{COUNTRY}-Reduced` (vorher inkonsistent)
- Fehlerbehandlung bei API-Timeouts: Exception wird geloggt statt Abbruch

---

## [1.0.0] - 2025-02-03

### Hinzugefügt

- Erster Release
- Import aller 27 EU-Länder Steuersätze (Standard + ermäßigt)
- Optionaler Import von CH, GB, NO (Nicht-EU-Länder)
- Automatische Tax Rule Erstellung pro Land mit konfigurierbaren Customer/Product Tax Classes
- Cron-basierte Synchronisierung (täglich/wöchentlich/monatlich wählbar)
- CLI-Befehl `tax:sync:run` für manuellen Sync mit `--force` Option
- EU VAT Rates API (euvatrates.com) als primäre Datenquelle
- `Model/Api/Fallback.php` — Statische Fallback-Daten (Stand Januar 2025)
- Vollständige Admin-Konfiguration unter Stores > Configuration > Storetown > Tax Sync
- `Helper/Config.php` — Zentrale Konfigurationsverwaltung mit allen Getter-Methoden
- `Model/TaxSyncService.php` — Kern-Service für Steuersatz-Synchronisierung
- `Cron/SyncTaxRates.php` — Cron-Handler für automatische Ausführung
- `Model/Config/Source/Frequency.php` — Frequenz-Options für Admin-Dropdown

### Datenquellen

- **Primär:** EU VAT Rates API (euvatrates.com)
- **Fallback:** Statische Daten, manuell gepflegt

### Unterstützte Magento-Versionen

- Magento 2.4.x
- PHP 8.1+

---

## Support

Bei Fragen oder Problemen kontaktieren Sie uns:

- moin@storetown-media.de
- www.storetown-media.de
