# jQuery Update Erforderlich

## ⚠️ Sicherheitskritische Updates

Die folgenden jQuery-Versionen im Plugin sind veraltet und haben bekannte Sicherheitslücken:

### Aktuelle Versionen:
- **jQuery 3.5.1** (von 2020) → **Update auf 3.7.1** empfohlen
- **jQuery UI 1.12.1** (von 2016) → **Update auf 1.13.2** empfohlen

### Sicherheitslücken in jQuery 3.5.1:
- CVE-2020-11022: Cross-Site Scripting (XSS) Schwachstelle
- CVE-2020-11023: Cross-Site Scripting (XSS) Schwachstelle

## Betroffene Dateien

### jQuery 3.5.1:
```
themes/default/js/jquery-3.5.1.min.js
themes/stripes_red/js/jquery-3.5.1.min.js
themes/stripes_green/js/jquery-3.5.1.min.js
themes/stripes_orange/js/jquery-3.5.1.min.js
```

### jQuery UI 1.12.1:
```
themes/default/js/jquery-ui-1.12.1.custom.min.js
themes/stripes_red/js/jquery-ui-1.12.1.custom.min.js
themes/stripes_green/js/jquery-ui-1.12.1.custom.min.js
themes/stripes_orange/js/jquery-ui-1.12.1.custom.min.js
```

### Eingebunden in:
- `lib/forms/start_page.php` (Zeile 16, 18)
- `lib/forms/partials/header.php` (Zeile 15)

## Update-Anleitung

### 1. jQuery 3.7.1 herunterladen
```bash
# Komprimierte Version
wget https://code.jquery.com/jquery-3.7.1.min.js -O themes/default/js/jquery-3.7.1.min.js
```

### 2. jQuery UI 1.13.2 herunterladen
```bash
# Mit ThemeRoller custom build erstellen oder standard Version verwenden
wget https://code.jquery.com/ui/1.13.2/jquery-ui.min.js -O themes/default/js/jquery-ui-1.13.2.min.js
```

### 3. Dateien in allen Theme-Ordnern ersetzen
```bash
# Für jedes Theme (default, stripes_red, stripes_green, stripes_orange)
cp themes/default/js/jquery-3.7.1.min.js themes/stripes_red/js/
cp themes/default/js/jquery-3.7.1.min.js themes/stripes_green/js/
cp themes/default/js/jquery-3.7.1.min.js themes/stripes_orange/js/

cp themes/default/js/jquery-ui-1.13.2.min.js themes/stripes_red/js/
cp themes/default/js/jquery-ui-1.13.2.min.js themes/stripes_green/js/
cp themes/default/js/jquery-ui-1.13.2.min.js themes/stripes_orange/js/
```

### 4. PHP-Dateien aktualisieren

#### In `lib/forms/start_page.php`:
Zeile 16 & 18 ändern von:
```php
<script type='text/javascript' src="<?php echo WDEB_PLUGIN_THEME_URL ?>/js/jquery-3.5.1.min.js"></script>
<script type='text/javascript' src="<?php echo WDEB_PLUGIN_THEME_URL ?>/js/jquery-ui-1.12.1.custom.min.js"></script>
```

Zu:
```php
<script type='text/javascript' src="<?php echo WDEB_PLUGIN_THEME_URL ?>/js/jquery-3.7.1.min.js"></script>
<script type='text/javascript' src="<?php echo WDEB_PLUGIN_THEME_URL ?>/js/jquery-ui-1.13.2.min.js"></script>
```

#### In `lib/forms/partials/header.php`:
Zeile 15 ändern von:
```php
<script type='text/javascript' src='<?php echo WDEB_PLUGIN_THEME_URL ?>/js/jquery-ui-1.12.1.custom.min.js'></script>
```

Zu:
```php
<script type='text/javascript' src='<?php echo WDEB_PLUGIN_THEME_URL ?>/js/jquery-ui-1.13.2.min.js'></script>
```

### 5. Alte Dateien entfernen (optional)
```bash
# Nach erfolgreichen Tests
find themes/ -name "jquery-3.5.1.min.js" -delete
find themes/ -name "jquery-ui-1.12.1.custom.min.js" -delete
```

## Testing

Nach dem Update bitte testen:
- [ ] Dashboard-Widgets funktionieren
- [ ] Drag & Drop für Menüelemente
- [ ] Wizard-Funktionalität
- [ ] Tooltips
- [ ] Alle Theme-Switcher
- [ ] Admin-Bereich im Easy-Modus

## Hinweis

✅ Alle PHP 8 Kompatibilitätsprobleme wurden bereits behoben
✅ Deprecated jQuery-Methoden werden NICHT verwendet (geprüft: `.live()`, `.bind()`, etc.)

Datum: 22. Februar 2026
