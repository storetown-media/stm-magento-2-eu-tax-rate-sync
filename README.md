# Storetown EU Tax Rate Sync for Magento 2

![Storetown Media](view/adminhtml/web/images/storetown-logo.png)

**Automatische Synchronisierung aller EU-Mehrwertsteuersätze für Magento 2**

[![Magento 2](https://img.shields.io/badge/Magento-2.4.x-orange.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Free Forever](https://img.shields.io/badge/Price-Free%20Forever-brightgreen.svg)](https://www.storetown-media.de/eu-tax-rate-sync-fuer-magento-2-community/)
[![Hyvä](https://img.shields.io/badge/Hyv%C3%A4-Compatible-FF6B35.svg)](https://www.hyva.io/)
[![Made in Germany](https://img.shields.io/badge/Made%20in-Germany-black.svg)](https://www.storetown-media.de/)

[📦 **Packagist**](https://packagist.org/packages/storetown/module-tax-sync) · [📚 **Produktseite**](https://www.storetown-media.de/eu-tax-rate-sync-fuer-magento-2-community/) · [💼 **Storetown-Media Org**](https://github.com/storetown-media)

---

## 🇪🇺 Überblick

Diese kostenlose Extension importiert und aktualisiert automatisch alle EU-Mehrwertsteuersätze in Ihrem Magento 2 Shop. Nie wieder manuelle Pflege von Steuersätzen bei Änderungen!

### ✨ Features

- **27 EU-Länder** + Schweiz, Norwegen und Großbritannien
- **Standard- und ermäßigte Sätze** werden separat importiert
- **Automatische Tax Rules** werden erstellt und mit den Rates verknüpft
- **Cron-basierte Synchronisierung** (täglich, wöchentlich oder monatlich)
- **E-Mail-Benachrichtigungen** bei Änderungen
- **Admin-Benachrichtigungen** im Magento Backend
- **CLI-Befehle** für manuelle Synchronisierung
- **Fallback-Daten** falls die API nicht erreichbar ist

---

## 📦 Installation

### Via Composer (empfohlen)

```bash
composer require storetown/module-tax-sync
bin/magento module:enable Storetown_TaxSync
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

### Manuelle Installation

1. Erstellen Sie den Ordner `app/code/Storetown/TaxSync`
2. Kopieren Sie alle Dateien in diesen Ordner
3. Führen Sie folgende Befehle aus:

```bash
bin/magento module:enable Storetown_TaxSync
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

---

## ⚙️ Konfiguration

Navigieren Sie zu **Stores → Configuration → Storetown → EU Tax Rate Sync**

### General Settings

| Option | Beschreibung |
|--------|--------------|
| Enable Auto Sync | Aktiviert die automatische Cron-Synchronisierung |
| Sync Frequency | Täglich, Wöchentlich oder Monatlich |
| Data Source | API-Quelle (vat-api.eu empfohlen) |
| Include Non-EU | CH, NO, GB mit einbeziehen |

### Tax Rules

| Option | Beschreibung |
|--------|--------------|
| Auto-Create Tax Rules | Erstellt automatisch Tax Rules für jeden Steuersatz |
| Customer Tax Classes | Kundengruppen für die Rules |
| Product Tax Classes | Produktklassen für die Rules |

### Notifications

| Option | Beschreibung |
|--------|--------------|
| Email Notifications | E-Mail bei Änderungen senden |
| Notification Email | Empfänger-Adresse |
| Admin Notification | Benachrichtigung im Admin-Panel |

---

## 🖥️ CLI-Befehle

### Manuelle Synchronisierung

```bash
bin/magento tax:sync:run
```

Mit ausführlicher Ausgabe:

```bash
bin/magento tax:sync:run -vvv
```

### Status anzeigen

```bash
bin/magento tax:sync:status
```

---

## 🌍 Unterstützte Länder

### EU-Mitgliedstaaten (27)

| Land | Code | Standard | Ermäßigt |
|------|------|----------|----------|
| Österreich | AT | 20% | 10% |
| Belgien | BE | 21% | 6% |
| Bulgarien | BG | 20% | 9% |
| Kroatien | HR | 25% | 13% |
| Zypern | CY | 19% | 5% |
| Tschechien | CZ | 21% | 12% |
| Dänemark | DK | 25% | - |
| Estland | EE | 24% | 9% |
| Finnland | FI | 25,5% | 13,5% |
| Frankreich | FR | 20% | 5,5% |
| Deutschland | DE | 19% | 7% |
| Griechenland | GR | 24% | 13% |
| Ungarn | HU | 27% | 18% |
| Irland | IE | 23% | 13,5% |
| Italien | IT | 22% | 10% |
| Lettland | LV | 21% | 12% |
| Litauen | LT | 21% | 5% |
| Luxemburg | LU | 17% | 8% |
| Malta | MT | 18% | 7% |
| Niederlande | NL | 21% | 9% |
| Polen | PL | 23% | 8% |
| Portugal | PT | 23% | 13% |
| Rumänien | RO | 21% | 11% |
| Slowakei | SK | 23% | 5% |
| Slowenien | SI | 22% | 9,5% |
| Spanien | ES | 21% | 10% |
| Schweden | SE | 25% | 6% |

### Nicht-EU (optional)

| Land | Code | Standard | Ermäßigt |
|------|------|----------|----------|
| Schweiz | CH | 8,1% | 2,6% |
| Großbritannien | GB | 20% | 5% |
| Norwegen | NO | 25% | 15% |

> **Datenquelle & ermäßigte Sätze:** Standardsätze werden live von [vat-api.eu](https://vat-api.eu/) bezogen (kostenlos, ohne API-Key); bei nicht erreichbarer API greifen die gepflegten Fallback-Daten. Da viele Länder **mehrere** ermäßigte Sätze haben (super-reduziert / ermäßigt / Park), importiert die Extension pro Land **einen** ermäßigten Hauptsatz (Bücher, Medikamente, Grundnahrungsmittel). Diesen können Sie pro Land jederzeit unter **Stores → Tax Zones and Rates** anpassen.

---

## 📋 Anforderungen

- Magento 2.4.x
- PHP 8.1 oder höher
- Cron muss konfiguriert sein (für Auto-Sync)

---

## 🔧 Fehlerbehebung

### Logs prüfen

```bash
tail -f var/log/tax_sync.log
```

### Cache leeren

```bash
bin/magento cache:flush
```

### DI neu kompilieren

```bash
rm -rf generated/code/*
bin/magento setup:di:compile
```

---

## 🐛 Bug Reports & Feature Requests

- **Bug Report:** [Issue eröffnen mit Bug-Report-Template](../../issues/new?template=bug_report.md)
- **Feature Request:** [Issue eröffnen mit Feature-Request-Template](../../issues/new?template=feature_request.md)
- **Frage oder Diskussion:** [GitHub Discussions](../../discussions)
- **Sicherheitsproblem (kritisch):** Bitte **NICHT** als Issue, sondern direkt an **security@storetown-media.de** — siehe [SECURITY.md](SECURITY.md)

Pull Requests sind willkommen! Siehe [CONTRIBUTING.md](CONTRIBUTING.md).

---

## 💼 Kommerzielle Schwester-Extensions

Diese Extension ist **dauerhaft kostenlos**. Storetown-Media finanziert die Entwicklung über **kommerzielle Magento-2-Extensions**:

| Extension | Was sie löst | Ab |
|---|---|---|
| [⚡ Smart One Page Checkout](https://github.com/storetown-media/stm-magento-2-smart-one-page-checkout) | Checkout auf 1 Seite, 8 Payment-Provider, mobile-optimiert | **389 €** |
| [🔐 B2B Register](https://github.com/storetown-media/stm-magento-2-b2b-customer-registration) | B2B-Kundenfreischaltung mit Admin-Approval | **149 €** |
| [📎 Attachment Pro](https://github.com/storetown-media/stm-magento-2-product-attachments-pro) | PDF-Datenblätter & Zertifikate am Produkt mit Auto-Zuweisung | **149 €** |
| [💰 Surcharge Max](https://github.com/storetown-media/stm-magento-2-surcharge-extension) | Zuschnitt-, Sperrgut- & Kategorie-Zuschläge transparent im Checkout | **99 €** |

Hub: [storetown-media.de/produkt-kategorie/downloads/magento-extensions/](https://www.storetown-media.de/produkt-kategorie/downloads/magento-extensions/)

---

## 📄 Changelog

Siehe [CHANGELOG.md](CHANGELOG.md)

---

## 📜 Lizenz

Diese Extension ist unter der [MIT-Lizenz](LICENSE) veröffentlicht — frei nutzbar, frei modifizierbar, kommerzielle Nutzung erlaubt.

---

## 🏢 Über Storetown Media

**Storetown Media** ist eine E-Commerce Agentur aus Hamburg/Tornesch, spezialisiert auf:

- 🛒 **Magento 2** Entwicklung & Betreuung
- 🛍️ **Shopware 6** Implementierung
- 🌐 **WooCommerce** Lösungen
- 🔗 **ERP-Integrationen** (SAP, Microsoft Dynamics, Oracle NetSuite)

### Kontakt

- 🌐 Website: [www.storetown-media.de](https://www.storetown-media.de)
- 📧 E-Mail: moin@storetown-media.de
- 📍 Standort: Hamburg / Tornesch, Deutschland

---

**Made with ❤️ in Hamburg**
