# Kunde Beskeder (Spjæt)

**Version:** 1.3.7
**Forfatter:** Runólfur Guðbjörnsson

Et specialbygget WordPress-plugin designet til at levere målrettede beskeder til kunder direkte på deres WooCommerce "Min Konto"-side. Løser problemer med upålidelig e-mail-levering ved at skabe et internt "opslagstavle"-system.

---

## 🚀 Nøglefunktioner

### Admin-panel (Backend)
* **Central Besked-styring:** Opretter et nyt menupunkt, "Kunde Beskeder", hvor alle beskeder kan oprettes og administreres.
* **Præcis Målretning:** Hver besked kan målrettes baseret på én af tre metoder:
    1.  **Abonnements-status:** (f.eks. `Aktiv`, `Parkeret`, `Afventende`). Flere statusser kan vælges.
    2.  **Produkt (Hold) ID:** Til alle kunder med abonnement på et specifikt produkt-ID.
    3.  **Kunde ID:** Til én enkelt, specifik kunde.
* **Admin-overblik:** En ny "Målretning"-kolonne i beskedoversigten viser tydeligt, hvem hver besked er sendt til.
* **Styring af Notifikationer:** En "Modtag E-mails"-checkbox er tilføjet til alle brugerprofiler i admin-panelet.

### Kunde-side (Frontend)
* **Ny "Min Konto"-fane:** Tilføjer en "Beskeder"-fane til WooCommerce "Min Konto"-menuen.
* **Dynamisk Indhold:** Kunder ser *kun* de beskeder, de er i målgruppen for.
* **"Læs Mere"-funktion:** Lange beskeder (over 55 ord) afkortes automatisk med en "Læs mere..."-knap for at holde siden pæn og overskuelig.
* **Selvbetjening af Notifikationer:** Kunder kan selv fravælge e-mail-notitser via en checkbox på deres "Kontooplysninger"-side.

### E-mail Notifikationer
* **Automatisk Udsendelse:** Når en *ny* besked publiceres, udløses en e-mail-notits.
* **Asynkron (WP-Cron):** E-mail-udsendelsen køres i baggrunden for at forhindre, at admin-panelet fryser eller timer ud ved udsendelse til mange kunder.
* **Respekterer Opt-out:** Sender kun e-mails til de kunder, der aktivt har tilvalgt at modtage dem.

---

## 🔧 Installation

1.  Download den seneste version som en `.zip`-fil fra dette repository (via "Code" -> "Download ZIP").
2.  I dit WordPress-adminpanel, gå til **Plugins -> Tilføj nyt -> Upload Plugin**.
3.  Vælg `.zip`-filen og klik "Installer nu".
4.  **Vigtigt:** Når pluginnet er installeret, skal du **Deaktivere** det og derefter **Gen-aktivere** det. Dette er nødvendigt for at registrere den nye "Beskeder"-side korrekt og undgå 404-fejl.
