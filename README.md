# 🚀 EasyCMS

Simple, Secure and Performance-Oriented PHP CMS

EasyCMS ist ein leichtgewichtiges Content-Management-System, das den Fokus auf saubere Architektur,
maximale Sicherheit durch strikte Input-Validierung und eine hocheffiziente Asset-Verarbeitung legt.
Es kombiniert bewährte PHP-Sicherheitskonzepte mit einer modernen, flexiblen Frontend-Architektur.

---

## ✨ Key Features
* Robust Security Core: Zentralisierte Input-Filterung über den Input_Cleaner.
  Jede globale Variable (GET, POST, COOKIE) wird vor der Verarbeitung strikt
  typisiert und bereinigt.
* Smart Asset Management: Der integrierte CssLoader bündelt, filtert und minimiert
  CSS-Ressourcen on-the-fly. Inklusive automatischer Pfadkorrektur für Bibliotheken
  wie FontAwesome.
* Skin-Inheritance-System: Ein intelligentes Vererbungssystem für Designs.
  Default-Styles dienen als Fallback, während Skin-spezifische Anpassungen
  nahtlos überschrieben werden können.
* Unicode-Safe Filtering: Speziell entwickelte Routinen zur XSS-Prävention,
  die Unicode-kompatibel sind und bereits existierende HTML-Entities schützen.
* Modular Architecture: Volle Kontrolle über Frontend-Komponenten wie Gridster
  (Layout) und Splide (Slider) bei minimalem Overhead.

---

## 🛠 Tech Stack
Backend & Core
* PHP 8.0+ Ready: Optimiert für moderne PHP-Umgebungen.
* Registry Pattern: Zentrales Management von Konfigurationen und Objekten.
* PHPMailer (v6.9.1): Sicherer und zuverlässiger E-Mail-Versand.

### Frontend & UI

| Using         | Version | Link                                         |
| ------------- | ------- | -------------------------------------------- |
| PHPMailer     | 6.9.1   | https://github.com/PHPMailer/PHPMailer       |
| Bootstrap     | 3.4.1   | https://github.com/twbs/bootstrap            |
| jQuery        | 4.0.0   | https://github.com/jquery/jquery             |
| jQuery UI     | 1.14.2  | https://github.com/jquery/jquery-ui          |
| Summernote    | 0.8.20  | https://github.com/summernote/summernote/    |
| Gridster      | 0.7.0   | https://github.com/dsmorse/gridster.js/      |
| Splide        | 4.1.3   | https://github.com/Splidejs/splide           |
| Font Awesome  | 7.2.0   | https://fontawesome.com/                     |
| DancingScript | 2.001   | https://github.com/googlefonts/DancingScript |
| Inconsolata   | 3.000   | https://github.com/googlefonts/Inconsolata   |
| Optima        | --      | https://www.typewolf.com/optima              |
| FavIcon Admin | --      | https://www.pngegg.com/en/png-eeqmv          |

### FTypography & Design
* Fonts: Dancing Script, Inconsolata, Optima (System-Fallback).
* Customization: Vollständige Unterstützung von CSS-Variablen
  innerhalb der Skin-Struktur.

---

## 🔒 Security Principles

EasyCMS verfolgt einen "Zero Trust" Ansatz gegenüber Nutzer-Eingaben:
* Strict Typing: Kein Wert wird verarbeitet, ohne explizit als INT,
  STRING, BOOL etc. definiert zu sein.
* XSS Protection: Tiefenprüfung von CSS-Inhalten auf schädliche
  JavaScript-Injektionen (eval, base64, script).
* Encapsulation: Globale Superglobale werden im Input_Cleaner gekapselt,
  um unkontrollierte Zugriffe zu verhindern.

---

## 📂 Installation

1. Klonen Sie das Repository auf Ihren Webserver.
2. Konfigurieren Sie die Pfade in der config/config.php.
3. Stellen Sie sicher, dass das Verzeichnis design/ Schreibrechte
   für die Cache-Generierung besitzt (falls aktiviert).

---

## Warum EasyCMS?

Im Gegensatz zu überladenen Systemen wie WordPress bietet EasyCMS volle Transparenz über den Code-Fluss.
Es ist für Entwickler gedacht, die ein System suchen, das stabil läuft, sicher filtert und CSS/JS so
ausliefert, wie es im Jahr 2026 sein sollte: Minimiert, gebündelt und sicher.