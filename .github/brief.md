# Brief Tecnico - Compagni di Viaggi

**Versione:** 1.0.0
**Autore:** Max74vr
**Data:** 2025-11-22
**Sito:** https://www.compagnidiviaggi.com

---

## 📋 Indice

1. [Panoramica Generale](#panoramica-generale)
2. [Architettura del Sistema](#architettura-del-sistema)
3. [Plugin: Compagni di Viaggi](#plugin-compagni-di-viaggi)
4. [Tema: Compagni Viaggi](#tema-compagni-viaggi)
5. [Funzionalità Dettagliate](#funzionalità-dettagliate)
6. [Database Personalizzato](#database-personalizzato)
7. [REST API](#rest-api)
8. [Sicurezza e Privacy](#sicurezza-e-privacy)
9. [Performance e Ottimizzazioni](#performance-e-ottimizzazioni)

---

## 🌐 Panoramica Generale

**Compagni di Viaggi** è una piattaforma WordPress completa per la creazione di una community di viaggiatori. Il sistema permette agli utenti di:
- Organizzare viaggi e trovare compagni di avventura
- Gestire profili personalizzati con verifica identità
- Comunicare tramite chat di gruppo e messaggi privati
- Lasciare recensioni reciproche
- Condividere storie e racconti di viaggio
- Guadagnare badge e reputazione

### Stack Tecnologico
- **CMS:** WordPress 6.0+
- **PHP:** 7.4+
- **Database:** MySQL con tabelle personalizzate
- **Frontend:** HTML5, CSS3 (Custom Properties), JavaScript (jQuery)
- **REST API:** WordPress REST API estesa con endpoint personalizzati
- **Architettura:** Plugin-based con tema dedicato

---

## 🏗️ Architettura del Sistema

### Struttura del Progetto

```
compagni-di-viaggi/
├── plugins/
│   └── compagni-di-viaggi/
│       ├── admin/                    # Pannello amministrazione
│       ├── assets/                   # CSS e JS del plugin
│       ├── includes/
│       │   ├── api/                  # REST API e JWT Auth
│       │   ├── ajax/                 # Gestori AJAX
│       │   └── class-*.php          # Classi funzionalità
│       └── compagni-di-viaggi.php   # File principale plugin
└── themes/
    └── compagni-viaggi/
        ├── assets/js/                # JavaScript tema
        ├── inc/                      # Customizer e utility
        ├── template-parts/           # Componenti riutilizzabili
        ├── page-*.php               # Template pagine
        ├── single-*.php             # Template singoli
        ├── archive-*.php            # Template archivi
        ├── functions.php            # Funzioni tema
        └── style.css                # Stili principali
```

### Pattern di Sviluppo
- **Singleton Pattern:** Classe principale del plugin
- **OOP (Object-Oriented Programming):** Tutte le funzionalità in classi separate
- **Hook-based:** Utilizzo estensivo di WordPress hooks (actions/filters)
- **Separation of Concerns:** Plugin per logica, tema per presentazione
- **REST-first:** API completa per sviluppi futuri (app mobile)

---

## 🔌 Plugin: Compagni di Viaggi

### File Principale: `compagni-di-viaggi.php`

**Informazioni Plugin:**
- Nome: Compagni di Viaggi
- Versione: 1.0.0
- Descrizione: Piattaforma completa per trovare compagni di viaggio
- Licenza: GPL v2 or later
- Text Domain: compagni-di-viaggi
- Requisiti: WordPress 6.0+, PHP 7.4+

### Costanti Definite
```php
CDV_VERSION = '1.0.0'
CDV_PLUGIN_DIR = [percorso plugin]
CDV_PLUGIN_URL = [URL plugin]
CDV_PLUGIN_BASENAME = [basename plugin]
```

### Opzioni di Default
- `cdv_max_participants`: 10 (partecipanti massimi di default)
- `cdv_min_age`: 18 (età minima)
- `cdv_chat_enabled`: true
- `cdv_reviews_enabled`: true
- `cdv_auto_approve_participants`: false
- `cdv_jwt_secret`: Generato automaticamente (64 caratteri)

---

## 🎨 Tema: Compagni Viaggi

### Design System

#### Variabili CSS (Custom Properties)

**Colori Principali:**
```css
--primary-color: #667eea (Blu/Viola)
--primary-dark: #5568d3
--primary-light: #7e92f5
--secondary-color: #764ba2 (Viola profondo)
--secondary-dark: #613a8a
--secondary-light: #8d5bb8
```

**Colori di Stato:**
```css
--success-color: #48bb78
--error-color: #f56565
--warning-color: #ed8936
--info-color: #4299e1
```

**Tipografia:**
```css
--font-primary: 'Poppins', sans-serif
--font-size-base: 16px
--line-height-base: 1.6
```

**Spacing & Layout:**
```css
--spacing-unit: 8px (sistema basato su multipli di 8)
--border-radius: 12px
--border-radius-sm: 8px
--border-radius-lg: 16px
--max-width: 1200px
```

**Shadows:**
```css
--shadow-sm: 0 1px 3px rgba(0,0,0,0.1)
--shadow-md: 0 4px 6px rgba(0,0,0,0.1)
--shadow-lg: 0 10px 15px rgba(0,0,0,0.1)
--shadow-xl: 0 20px 25px rgba(0,0,0,0.15)
```

### Template Hierarchy

**Pagine Speciali:**
- `front-page.php` - Homepage
- `home.php` - Blog
- `page-dashboard.php` - Dashboard utente
- `page-crea-viaggio.php` - Creazione viaggio
- `page-modifica-viaggio.php` - Modifica viaggio
- `page-login.php` - Login
- `page-registrazione.php` - Registrazione
- `page-profilo-utente.php` - Profilo pubblico
- `page-profilo-in-attesa.php` - Profilo in approvazione
- `page-calendario-viaggi.php` - Calendario
- `page-racconti.php` - Archivio racconti
- `page-racconta-viaggio.php` - Crea racconto
- `page-wishlist.php` - Lista desideri
- `page-privacy-settings.php` - Impostazioni privacy

**Custom Post Types:**
- `single-viaggio.php` - Singolo viaggio
- `archive-viaggio.php` - Archivio viaggi
- `single-racconto.php` - Singolo racconto
- `archive-racconto.php` - Archivio racconti

**Taxonomy:**
- `taxonomy-tipo_viaggio.php` - Filtro per tipo viaggio

### Menu Registrati
1. **primary** - Menu Principale
2. **mobile** - Menu Mobile
3. **footer** - Menu Footer

### Widget Areas
1. **sidebar-1** - Sidebar principale
2. **footer-1** - Footer colonna 1
3. **footer-2** - Footer colonna 2
4. **footer-3** - Footer colonna 3

### Dimensioni Immagini
- **Default:** 800×450 (crop)
- **travel-card:** 400×300 (crop)
- **travel-hero:** 1200×600 (crop)

---

## 🚀 Funzionalità Dettagliate

### 1. Post Types Personalizzati

#### 1.1 Viaggio (`viaggio`)
**Classe:** `CDV_Post_Types`

**Supporta:**
- Title
- Editor (WYSIWYG)
- Thumbnail (immagine di copertina)
- Author
- Comments
- Revisions

**Metadati Associati:**
- `cdv_start_date` - Data inizio (DATE)
- `cdv_end_date` - Data fine (DATE)
- `cdv_date_type` - Tipo data (exact/month)
- `cdv_destination` - Destinazione
- `cdv_country` - Nazione
- `cdv_budget` - Budget (€)
- `cdv_max_participants` - Partecipanti massimi
- `cdv_min_age` - Età minima
- `cdv_max_age` - Età massima
- `cdv_travel_status` - Stato (open/full/in_progress/completed/cancelled)
- `cdv_transport` - Mezzi di trasporto
- `cdv_accommodation` - Sistemazione
- `cdv_difficulty` - Difficoltà
- `cdv_meals` - Pasti inclusi
- `cdv_guide` - Tipo guida
- `cdv_duration_days` - Durata in giorni
- `cdv_latitude` - Coordinate GPS (lat)
- `cdv_longitude` - Coordinate GPS (lng)

**REST API:**
- Endpoint: `/wp-json/wp/v2/viaggi`
- Slug: `viaggi`
- Completamente esposto via REST API

#### 1.2 Racconto (`racconto`)
**Note:** Non presente nei file analizzati ma menzionato nei template

### 2. Tassonomie Personalizzate

#### 2.1 Tipo Viaggio (`tipo_viaggio`)
**Classe:** `CDV_Taxonomies`

**Caratteristiche:**
- Gerarchica (come categorie)
- Associata a: `viaggio`
- Slug: `tipo-viaggio`
- REST API: Attiva

**Termini Predefiniti:**
1. Avventura (`avventura`)
2. Mare (`mare`)
3. Montagna (`montagna`)
4. Città d'Arte (`citta-arte`)
5. Cultura (`cultura`)
6. Relax (`relax`)
7. Food & Wine (`food-wine`)
8. Sport (`sport`)
9. Zaino in Spalla (`zaino-spalla`)

**Funzionalità Aggiuntive:**
- Immagini per i termini (via `CDV_Taxonomy_Images`)
- Icone personalizzabili

#### 2.2 Destinazione (`destinazione`)
**Caratteristiche:**
- Gerarchica
- Associata a: `viaggio`
- Slug: `destinazione`
- REST API: Attiva

### 3. Ruoli Utente Personalizzati

**Classe:** `CDV_User_Roles`

#### 3.1 Viaggiatore (`viaggiatore`)

**Capacità:**
```php
'read' => true
'level_0' => true
'upload_files' => true
'create_viaggi' => true
'edit_own_viaggi' => true
'delete_own_viaggi' => true
'join_viaggi' => true
'use_chat' => true
'leave_reviews' => true
```

**Restrizioni:**
- ❌ Accesso backend bloccato (redirect a `/dashboard`)
- ❌ Admin bar nascosta
- ✅ Può creare e gestire solo i propri viaggi
- ✅ Accesso completo alle funzionalità frontend

**Metadati Utente:**

**Profilo Base:**
- `cdv_bio` - Biografia
- `cdv_birth_date` - Data di nascita
- `cdv_gender` - Genere
- `cdv_city` - Città
- `cdv_country` - Nazione
- `cdv_phone` - Telefono
- `cdv_languages` - Lingue parlate (array)
- `cdv_travel_styles` - Stili di viaggio preferiti (array)
- `cdv_profile_image` - Avatar personalizzato
- `cdv_cover_image` - Immagine di copertina profilo

**Approvazione e Stato:**
- `cdv_user_approved` - Stato approvazione (pending/0/1)
- `cdv_email_verified` - Email verificata (0/1)
- `cdv_verified` - Badge verificato (0/1)
- `cdv_user_approved_date` - Data approvazione
- `cdv_user_rejected_date` - Data rifiuto
- `cdv_user_rejection_reason` - Motivazione rifiuto

**Reputazione e Statistiche:**
- `cdv_reputation_score` - Punteggio reputazione (0-5)
- `cdv_total_reviews` - Numero recensioni ricevute
- `cdv_total_travels` - Totale viaggi organizzati
- `cdv_completed_travels` - Viaggi completati
- `cdv_total_participations` - Viaggi come partecipante

**Preferenze:**
- `cdv_receive_email_notifications` - Notifiche email (0/1)
- `cdv_profile_visibility` - Visibilità profilo (public/private)
- `cdv_show_email` - Mostra email pubblicamente (0/1)
- `cdv_show_phone` - Mostra telefono pubblicamente (0/1)

#### 3.2 Administrator
**Capacità Aggiuntive:**
```php
'approve_users' => true
'approve_viaggi' => true
'moderate_chat' => true
'manage_viaggiatori' => true
+ tutte le capacità di viaggiatore
```

### 4. Sistema di Partecipazione

**Classe:** `CDV_Participants`
**Tabella:** `wp_cdv_travel_participants`

#### 4.1 Workflow Partecipazione

```
1. Utente richiede partecipazione → Status: PENDING
   ↓
2. Organizzatore riceve notifica
   ↓
3. Organizzatore approva/rifiuta
   ↓
4. APPROVATO → Status: ACCEPTED
   - Accesso chat di gruppo
   - Messaggio privato attivato
   - Notifica inviata

   RIFIUTATO → Status: REJECTED
   - Conversazione bloccata
   - Notifica inviata
```

#### 4.2 Struttura Dati Partecipante

```php
id                  // ID univoco
travel_id           // ID viaggio
user_id             // ID utente
status              // 'pending' | 'accepted' | 'rejected'
message             // Messaggio richiesta
is_organizer        // 1 per organizzatore, 0 per partecipanti
requested_at        // Timestamp richiesta
updated_at          // Timestamp ultimo aggiornamento
```

#### 4.3 Metodi Pubblici

```php
// Richiesta partecipazione
CDV_Participants::request_join($travel_id, $user_id, $message)

// Accetta partecipante
CDV_Participants::accept_participant($travel_id, $user_id)

// Rifiuta partecipante
CDV_Participants::reject_participant($travel_id, $user_id)

// Verifica stato partecipazione
CDV_Participants::is_participant($travel_id, $user_id, $status = null)

// Conta partecipanti
CDV_Participants::get_participant_count($travel_id, $status = 'accepted')

// Verifica se viaggio è completo
CDV_Participants::is_travel_full($travel_id)

// Ottieni lista partecipanti
CDV_Participants::get_participants($travel_id, $status = null)

// Rimuovi partecipante
CDV_Participants::remove_participant($travel_id, $user_id)
```

#### 4.4 Validazioni Automatiche

- ✅ Non permette richieste duplicate
- ✅ Blocco se viaggio completo
- ✅ Blocco se viaggio completato/cancellato
- ✅ Creazione automatica chat di gruppo all'accettazione
- ✅ Invio messaggio privato automatico con richiesta

### 5. Sistema di Recensioni

**Classe:** `CDV_Reviews`
**Tabella:** `wp_cdv_reviews`

#### 5.1 Criteri di Valutazione

Ogni recensione valuta 4 criteri (scala 1-5):

1. **Puntualità** (`punctuality`)
2. **Spirito di Gruppo** (`group_spirit`)
3. **Rispetto** (`respect`)
4. **Adattabilità** (`adaptability`)

**Punteggio Finale:** Media dei 4 criteri

#### 5.2 Regole di Recensione

**Chi può recensire:**
- ✅ Organizzatore → Tutti i partecipanti accettati
- ✅ Partecipante accettato → Altri partecipanti + organizzatore
- ❌ Solo per viaggi con status `completed`
- ❌ Una sola recensione per coppia utente/viaggio

#### 5.3 Struttura Recensione

```php
id                  // ID recensione
travel_id           // ID viaggio
reviewer_id         // Chi lascia la recensione
reviewed_id         // Chi riceve la recensione
punctuality         // 1-5
group_spirit        // 1-5
respect             // 1-5
adaptability        // 1-5
comment             // Testo recensione
reply               // Risposta recensito
reply_date          // Data risposta
created_at          // Data recensione
```

#### 5.4 Funzionalità Avanzate

**Risposta alle Recensioni:**
```php
CDV_Reviews::add_review_reply($review_id, $user_id, $reply_text)
```
- Solo il recensito può rispondere
- Una sola risposta per recensione

**Segnalazione Recensioni Inappropriate:**
```php
CDV_Reviews::report_review($review_id, $user_id, $reason)
```
- Tabella: `wp_cdv_review_reports`
- Status: pending/reviewed/resolved

**Sistema "Utile":**
```php
CDV_Reviews::mark_review_helpful($review_id, $user_id)
```
- Tabella: `wp_cdv_review_helpful`
- Toggle: aggiunge/rimuove voto utile

**Statistiche Recensioni:**
```php
CDV_Reviews::get_user_review_stats($user_id)
```
Ritorna:
- Total reviews
- Media per ogni criterio
- Media complessiva
- Distribuzione valutazioni (1-5 stelle)

**Badge Automatici:**
```php
CDV_Reviews::get_review_badge($user_id)
```
Badge assegnati in base a media e numero recensioni:
- 🌟 **Super Host** - Media ≥4.8 + 20+ recensioni
- ✨ **Viaggiatore Fidato** - Media ≥4.5 + 10+ recensioni
- 👍 **Affidabile** - Media ≥4.0 + 5+ recensioni

#### 5.5 Aggiornamento Reputazione

Ogni nuova recensione aggiorna automaticamente:
- `cdv_reputation_score` (media di tutte le recensioni)
- `cdv_total_reviews` (conteggio totale)
- Assegnazione badge automatica

### 6. Sistema di Chat

#### 6.1 Chat di Gruppo

**Classe:** `CDV_Chat` (attualmente semplificata)
**Tabella:** `wp_cdv_travel_group_messages`

**Note:** Il sistema di chat di gruppo è presente ma semplificato. Le tabelle principali sono:
- `wp_cdv_travel_group_messages` - Messaggi di gruppo

**Funzionalità:**
- Una chat per ogni viaggio
- Accesso solo a organizzatore e partecipanti accettati
- Anti-spam: max 10 messaggi/minuto
- Recupero ultimi 50 messaggi di default
- Polling per nuovi messaggi (tramite timestamp)

#### 6.2 Messaggi Privati

**Classe:** `CDV_Private_Messages`
**Tabelle:**
- `wp_cdv_private_messages` - Messaggi
- `wp_cdv_blocked_conversations` - Conversazioni bloccate

**Struttura Messaggio:**
```php
id                  // ID messaggio
sender_id           // Mittente
receiver_id         // Destinatario
travel_id           // Viaggio di riferimento
message             // Testo
is_read             // 0 = non letto, 1 = letto
created_at          // Data invio
```

**Regole Messaggistica:**
- ✅ Messaggi legati a un viaggio specifico
- ✅ Organizzatore ↔ Richiedente (anche se pending)
- ✅ Partecipante accettato ↔ Partecipante accettato
- ✅ Partecipante accettato ↔ Organizzatore
- ❌ Utenti senza relazione col viaggio
- ❌ Conversazioni bloccate

**Blocco Automatico:**
- Quando organizzatore rifiuta richiesta → conversazione bloccata (bidirezionale)

**Notifiche Email:**
- Email automatica quando si riceve un messaggio
- Link diretto alla conversazione in dashboard
- **NO contenuto messaggio nell'email** (privacy)

**AJAX Endpoints:**
- `cdv_send_message` - Invia messaggio
- `cdv_get_conversation` - Carica conversazione
- `cdv_get_user_conversations` - Lista conversazioni utente
- `cdv_block_conversation` - Blocca/Sblocca conversazione

**Funzionalità Admin:**
- `cdv_admin_get_all_conversations` - Moderazione
- `cdv_admin_get_conversation` - Visualizza conversazione

### 7. Sistema di Notifiche

**Classe:** `CDV_Notifications`
**Tabella:** `wp_cdv_notifications`

#### 7.1 Tipi di Notifica

| Tipo | Icona | Descrizione |
|------|-------|-------------|
| `join_request` | 👋 | Nuova richiesta partecipazione |
| `request_accepted` | ✅ | Richiesta accettata |
| `request_rejected` | ❌ | Richiesta rifiutata |
| `new_message` | 💬 | Nuovo messaggio privato |
| `new_participant` | 👤 | Nuovo partecipante |
| `travel_full` | 🔒 | Viaggio completo |
| `review_received` | ⭐ | Nuova recensione |
| `travel_cancelled` | 🚫 | Viaggio cancellato |
| `travel_updated` | 📝 | Viaggio modificato |

#### 7.2 Struttura Notifica

```php
id                  // ID notifica
user_id             // Destinatario
type                // Tipo (vedi tabella)
title               // Titolo breve
message             // Testo completo
link                // URL azione
related_id          // ID entità correlata
is_read             // 0/1
created_at          // Timestamp
```

#### 7.3 Helper Functions

```php
// Notifica richiesta partecipazione
CDV_Notifications::notify_join_request($travel_id, $requester_id)

// Notifica accettazione
CDV_Notifications::notify_request_accepted($travel_id, $participant_id)

// Notifica rifiuto
CDV_Notifications::notify_request_rejected($travel_id, $participant_id)

// Notifica messaggio
CDV_Notifications::notify_new_message($recipient_id, $sender_id, $travel_id)

// Notifica recensione
CDV_Notifications::notify_new_review($user_id, $reviewer_id, $travel_id)
```

#### 7.4 AJAX Endpoints

```php
wp_ajax_cdv_get_notifications           // Carica notifiche
wp_ajax_cdv_mark_notification_read      // Segna come letta
wp_ajax_cdv_mark_all_notifications_read // Segna tutte come lette
```

#### 7.5 Cleanup Automatico

```php
CDV_Notifications::cleanup_old_notifications($days = 30)
```
- Elimina notifiche lette più vecchie di N giorni
- Consigliato eseguire via WP-Cron

### 8. Sistema di Badge

**Classe:** `CDV_Badges`
**Tabella:** `wp_cdv_user_badges`

#### 8.1 Badge Disponibili

| Badge | Icona | Nome | Criterio |
|-------|-------|------|----------|
| `early_adopter` | ⭐ | Early Adopter | Primi membri registrati |
| `verified` | ✓ | Verificato | Identità verificata |
| `first_travel` | 🚀 | Primo Viaggio | Primo viaggio organizzato |
| `first_story` | 📖 | Narratore | Primo racconto pubblicato |
| `explorer` | 🧭 | Esploratore | 5 viaggi completati come partecipante |
| `globetrotter` | ✈️ | Giramondo | 10 viaggi completati come partecipante |
| `organizer` | 📅 | Organizzatore | 5 viaggi completati come organizzatore |
| `trusted` | 🌟 | Affidabile | Reputazione ≥ 4.5 |
| `social` | 🎉 | Socievole | 10 recensioni positive date |
| `storyteller` | 📚 | Raccontastorie | 10 racconti pubblicati |

#### 8.2 Assegnazione Automatica

**Hook Utilizzati:**
```php
'user_register'              → early_adopter
'cdv_participant_accepted'   → explorer, globetrotter, organizer, trusted
'cdv_review_added'           → social
```

**Metodi:**
```php
CDV_Badges::award_badge($user_id, $badge_type)
CDV_Badges::get_user_badges($user_id)
CDV_Badges::has_badge($user_id, $badge_type)
CDV_Badges::check_travel_badges($user_id)
CDV_Badges::check_review_badges($user_id)
```

### 9. Sistema di Wishlist

**Classe:** `CDV_Wishlist`
**Storage:** User meta (`cdv_wishlist`)

#### 9.1 Funzionalità

```php
// Aggiungi a wishlist
CDV_Wishlist::add_to_wishlist($user_id, $travel_id)

// Rimuovi da wishlist
CDV_Wishlist::remove_from_wishlist($user_id, $travel_id)

// Verifica presenza
CDV_Wishlist::is_in_wishlist($user_id, $travel_id)

// Ottieni wishlist completa con dettagli
CDV_Wishlist::get_wishlist_travels($user_id)

// Conta elementi
CDV_Wishlist::get_wishlist_count($user_id)
```

#### 9.2 AJAX Endpoints

```php
wp_ajax_cdv_toggle_wishlist  // Toggle add/remove
wp_ajax_cdv_get_wishlist     // Carica wishlist completa
```

#### 9.3 Helper UI

```php
CDV_Wishlist::get_wishlist_button_html($travel_id, $class)
```
Genera HTML per pulsante wishlist:
- ♡ Salva (non salvato)
- ♥ Salvato (salvato)
- Redirect a login se utente non autenticato

### 10. Altre Funzionalità Plugin

#### 10.1 Verifica Email

**Classe:** `CDV_Email_Verification`
**Tabella:** `wp_cdv_email_verification`

**Workflow:**
1. Registrazione → token generato (64 caratteri)
2. Email inviata con link verifica
3. Click link → email verificata
4. Meta `cdv_email_verified` = 1

**Struttura Token:**
```php
id
user_id
token              // SHA256 hash
created_at
expires_at         // Default: 24 ore
verified_at        // NULL finché non verificato
```

#### 10.2 Notifiche Email

**Classe:** `CDV_Email_Notifications`

**Tipi Email:**
- Verifica email
- Approvazione account
- Richiesta partecipazione (a organizzatore)
- Accettazione/Rifiuto richiesta
- Nuovo messaggio privato
- Nuova recensione
- Promemoria viaggio

#### 10.3 Avatar Personalizzati

**Classe:** `CDV_Custom_Avatars`

- Upload avatar personalizzato
- Storage: `cdv_profile_image` (URL o attachment ID)
- Integrazione con `get_avatar()` di WordPress
- Supporto dimensioni multiple

#### 10.4 Profili Utente

**Classe:** `CDV_User_Profiles`

**Metodi:**
```php
CDV_User_Profiles::get_profile_url($user_id)
CDV_User_Profiles::update_profile($user_id, $data)
CDV_User_Profiles::get_profile_completion($user_id)
```

**Campi Profilo:**
- Dati personali (bio, città, paese, lingue)
- Preferenze viaggio
- Privacy settings
- Social links
- Avatar e cover image

#### 10.5 Approvazione Utenti

**Classe:** `CDV_Admin_Approvals`

**Stati:**
- `pending` - In attesa (default nuovi utenti)
- `1` - Approvato
- `0` - Rifiutato

**Workflow:**
```
Registrazione → pending → Admin approva → Utente può creare viaggi
                      ↘ Admin rifiuta → Accesso limitato
```

**Metodi:**
```php
CDV_User_Roles::approve_user($user_id)
CDV_User_Roles::reject_user($user_id, $reason)
CDV_User_Roles::is_user_approved($user_id)
CDV_User_Roles::get_pending_users()
CDV_User_Roles::get_pending_users_count()
```

#### 10.6 Moderazione Viaggi

**Classe:** `CDV_Travel_Moderation`

- Approvazione viaggi prima della pubblicazione
- Segnalazione contenuti inappropriati
- Cancellazione viaggi da admin

#### 10.7 Social Sharing

**Classe:** `CDV_Social_Sharing`

**Piattaforme:**
- Facebook
- Twitter
- WhatsApp
- LinkedIn
- Email
- Copy link

#### 10.8 Sistema Referral

**Classe:** `CDV_Referral_System`
**Tabella:** `wp_cdv_referrals`

**Funzionalità:**
- Codice referral univoco per utente
- Tracking registrazioni
- Premi/crediti per referente
- Statistiche referral

#### 10.9 Statistiche Organizzatore

**Classe:** `CDV_Organizer_Stats`

**Metriche:**
- Viaggi creati
- Viaggi completati
- Tasso completamento
- Partecipanti totali
- Media partecipanti per viaggio
- Recensioni ricevute
- Media recensioni
- Entrate generate (se applicabile)

#### 10.10 Racconti di Viaggio

**Classe:** `CDV_Travel_Stories`

- Pubblicazione racconti post-viaggio
- Gallery fotografica
- Commenti e like
- Correlazione con viaggio originale

#### 10.11 Gallerie Viaggi

**Classe:** `CDV_Travel_Gallery`

- Upload multiplo immagini
- Gallery responsive
- Lightbox
- Ordinamento immagini

#### 10.12 Mappe Interattive

**Classe:** `CDV_Travel_Maps`

**Funzionalità:**
- Integrazione Google Maps / OpenStreetMap
- Coordinate GPS viaggi
- Pin su mappa
- Itinerari visuali
- Mappa utenti registrati

**Meta Associati:**
- `cdv_latitude`
- `cdv_longitude`
- `cdv_map_zoom`

#### 10.13 GDPR e Privacy

**Classe:** `CDV_GDPR`

**Funzionalità:**
- Export dati utente (formato JSON)
- Cancellazione completa account
- Cookie consent
- Privacy policy acceptance
- Data retention policies
- Anonimizzazione dati

**AJAX:**
- `cdv_export_user_data`
- `cdv_delete_user_data`
- `cdv_accept_privacy_policy`

#### 10.14 Performance

**Classe:** `CDV_Performance`

**Ottimizzazioni:**
- Cache query database
- Lazy loading immagini
- Minificazione assets
- CDN support
- Database query optimization
- Transient API per cache

**Admin Page:**
- Monitoraggio performance
- Cache management
- Debug queries
- Profiling

---

## 🗄️ Database Personalizzato

### Schema Completo

#### 1. `wp_cdv_travel_participants`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
travel_id           BIGINT(20) UNSIGNED NOT NULL
user_id             BIGINT(20) UNSIGNED NOT NULL
status              VARCHAR(20) DEFAULT 'pending'  -- pending/accepted/rejected
message             TEXT
is_organizer        TINYINT(1) DEFAULT 0
requested_at        DATETIME DEFAULT CURRENT_TIMESTAMP
updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

INDEX (travel_id)
INDEX (user_id)
INDEX (status)
```

#### 2. `wp_cdv_travel_group_messages`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
travel_id           BIGINT(20) UNSIGNED NOT NULL
user_id             BIGINT(20) UNSIGNED NOT NULL
message             TEXT NOT NULL
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP

INDEX (travel_id)
INDEX (user_id)
INDEX (created_at)
```

#### 3. `wp_cdv_reviews`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
travel_id           BIGINT(20) UNSIGNED NOT NULL
reviewer_id         BIGINT(20) UNSIGNED NOT NULL
reviewed_id         BIGINT(20) UNSIGNED NOT NULL
punctuality         TINYINT(1) NOT NULL           -- 1-5
group_spirit        TINYINT(1) NOT NULL           -- 1-5
respect             TINYINT(1) NOT NULL           -- 1-5
adaptability        TINYINT(1) NOT NULL           -- 1-5
comment             TEXT
reply               TEXT
reply_date          DATETIME DEFAULT NULL
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP

UNIQUE KEY unique_review (travel_id, reviewer_id, reviewed_id)
INDEX (travel_id)
INDEX (reviewer_id)
INDEX (reviewed_id)
```

#### 4. `wp_cdv_review_reports`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
review_id           BIGINT(20) UNSIGNED NOT NULL
reporter_id         BIGINT(20) UNSIGNED NOT NULL
reason              VARCHAR(255) NOT NULL
status              VARCHAR(20) DEFAULT 'pending'
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP

INDEX (review_id)
INDEX (reporter_id)
INDEX (status)
```

#### 5. `wp_cdv_review_helpful`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
review_id           BIGINT(20) UNSIGNED NOT NULL
user_id             BIGINT(20) UNSIGNED NOT NULL
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP

UNIQUE KEY unique_helpful (review_id, user_id)
INDEX (review_id)
INDEX (user_id)
```

#### 6. `wp_cdv_user_badges`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id             BIGINT(20) UNSIGNED NOT NULL
badge_type          VARCHAR(50) NOT NULL
earned_at           DATETIME DEFAULT CURRENT_TIMESTAMP

UNIQUE KEY user_badge (user_id, badge_type)
INDEX (user_id)
```

#### 7. `wp_cdv_email_verification`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id             BIGINT(20) UNSIGNED NOT NULL
token               VARCHAR(64) NOT NULL
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP
expires_at          DATETIME NOT NULL
verified_at         DATETIME DEFAULT NULL

UNIQUE KEY token (token)
INDEX (user_id)
```

#### 8. `wp_cdv_private_messages`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
sender_id           BIGINT(20) UNSIGNED NOT NULL
receiver_id         BIGINT(20) UNSIGNED NOT NULL
travel_id           BIGINT(20) UNSIGNED NOT NULL
message             TEXT NOT NULL
is_read             TINYINT(1) DEFAULT 0
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP

INDEX (sender_id)
INDEX (receiver_id)
INDEX (travel_id)
INDEX (created_at)
```

#### 9. `wp_cdv_blocked_conversations`
```sql
id                  BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id             BIGINT(20) UNSIGNED NOT NULL
blocked_user_id     BIGINT(20) UNSIGNED NOT NULL
travel_id           BIGINT(20) UNSIGNED NOT NULL
reason              VARCHAR(255) DEFAULT NULL
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP

UNIQUE KEY unique_block (user_id, blocked_user_id, travel_id)
INDEX (user_id)
INDEX (blocked_user_id)
INDEX (travel_id)
```

#### 10. `wp_cdv_notifications`
```sql
id                  BIGINT(20) AUTO_INCREMENT PRIMARY KEY
user_id             BIGINT(20) NOT NULL
type                VARCHAR(50) NOT NULL
title               VARCHAR(255) NOT NULL
message             TEXT NOT NULL
link                VARCHAR(255) DEFAULT NULL
related_id          BIGINT(20) DEFAULT NULL
is_read             TINYINT(1) DEFAULT 0
created_at          DATETIME DEFAULT CURRENT_TIMESTAMP

INDEX (user_id)
INDEX (is_read)
INDEX (created_at)
```

---

## 🔌 REST API

### Namespace: `cdv/v1`

**Classe:** `CDV_REST_API`
**Base URL:** `https://compagnidiviaggi.com/wp-json/cdv/v1/`

### Endpoints Completi

#### Viaggi

**GET** `/travels`
- **Descrizione:** Lista viaggi con filtri
- **Autenticazione:** Pubblica
- **Parametri:**
  - `per_page` (int) - Risultati per pagina (default: 12)
  - `page` (int) - Numero pagina
  - `tipo_viaggio` (string) - Slug tipo viaggio
  - `destinazione` (string) - Slug destinazione
  - `search` (string) - Ricerca full-text

**Response:**
```json
{
  "travels": [
    {
      "id": 123,
      "title": "Trekking in Nepal",
      "content": "...",
      "excerpt": "...",
      "image": "https://...",
      "organizer": {
        "id": 45,
        "name": "Mario Rossi",
        "avatar": "https://...",
        "reputation": 4.8
      },
      "start_date": "2025-06-15",
      "end_date": "2025-06-30",
      "destination": "Kathmandu",
      "country": "Nepal",
      "budget": 1500,
      "max_participants": 8,
      "current_participants": 3,
      "status": "open",
      "tipo_viaggio": ["Avventura", "Montagna"],
      "created_at": "2025-01-15 10:30:00"
    }
  ],
  "total": 45,
  "pages": 4
}
```

---

**GET** `/travels/{id}`
- **Descrizione:** Dettagli singolo viaggio
- **Autenticazione:** Pubblica

**Response:** Oggetto singolo viaggio (come sopra)

---

**POST** `/travels/{id}/join`
- **Descrizione:** Richiedi partecipazione
- **Autenticazione:** Richiesta
- **Parametri:**
  - `message` (string) - Messaggio richiesta

**Response:**
```json
{
  "success": true,
  "id": 789
}
```

---

**GET** `/travels/{id}/participants`
- **Descrizione:** Lista partecipanti accettati
- **Autenticazione:** Pubblica

**Response:**
```json
[
  {
    "id": 45,
    "name": "Mario Rossi",
    "avatar": "https://...",
    "reputation": 4.8
  }
]
```

#### Chat

**GET** `/chats/{id}/messages`
- **Descrizione:** Messaggi chat di gruppo
- **Autenticazione:** Richiesta + Partecipante
- **Parametri:**
  - `limit` (int) - Numero messaggi (default: 50)

**Response:**
```json
[
  {
    "id": 123,
    "user": {
      "id": 45,
      "name": "Mario Rossi",
      "avatar": "https://..."
    },
    "message": "Ciao a tutti!",
    "created_at": "2025-01-20 15:30:00"
  }
]
```

---

**POST** `/chats/{id}/messages`
- **Descrizione:** Invia messaggio in chat
- **Autenticazione:** Richiesta + Partecipante
- **Parametri:**
  - `message` (string) - Testo messaggio

**Response:**
```json
{
  "success": true,
  "id": 456
}
```

#### Recensioni

**POST** `/reviews`
- **Descrizione:** Aggiungi recensione
- **Autenticazione:** Richiesta
- **Parametri:**
  - `travel_id` (int)
  - `reviewed_id` (int)
  - `punctuality` (int 1-5)
  - `group_spirit` (int 1-5)
  - `respect` (int 1-5)
  - `adaptability` (int 1-5)
  - `comment` (string)

**Response:**
```json
{
  "success": true,
  "id": 234
}
```

---

**GET** `/users/{id}/reviews`
- **Descrizione:** Recensioni ricevute da utente
- **Autenticazione:** Pubblica

**Response:**
```json
[
  {
    "reviewer": {
      "id": 67,
      "name": "Laura Bianchi",
      "avatar": "https://..."
    },
    "scores": {
      "punctuality": 5,
      "group_spirit": 5,
      "respect": 5,
      "adaptability": 4
    },
    "comment": "Ottimo compagno di viaggio!",
    "created_at": "2025-01-15 18:00:00"
  }
]
```

#### Profili Utente

**GET** `/users/{id}/profile`
- **Descrizione:** Profilo pubblico utente
- **Autenticazione:** Pubblica

**Response:**
```json
{
  "id": 45,
  "name": "Mario Rossi",
  "avatar": "https://...",
  "bio": "Appassionato di trekking...",
  "city": "Milano",
  "country": "Italia",
  "languages": ["Italiano", "Inglese"],
  "travel_styles": ["Avventura", "Montagna"],
  "verified": true,
  "reputation": 4.8,
  "total_reviews": 23
}
```

---

**GET** `/users/{id}/badges`
- **Descrizione:** Badge utente
- **Autenticazione:** Pubblica

**Response:**
```json
[
  {
    "badge_type": "globetrotter",
    "name": "Giramondo",
    "icon": "✈️",
    "description": "Ha partecipato a 10 viaggi",
    "earned_at": "2024-12-01 10:00:00"
  }
]
```

#### Dashboard

**GET** `/dashboard/my-travels`
- **Descrizione:** Viaggi dell'utente loggato
- **Autenticazione:** Richiesta

**Response:**
```json
{
  "organized": [
    { /* viaggio organizzato */ }
  ],
  "participating": [
    { /* viaggio come partecipante */ }
  ]
}
```

### JWT Authentication

**Classe:** `CDV_JWT_Auth`

**Token Generation:**
```php
POST /wp-json/cdv/v1/auth/token
Body: {
  "username": "mario",
  "password": "***"
}

Response: {
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 45,
    "name": "Mario Rossi",
    "email": "mario@example.com"
  }
}
```

**Usage:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Configurazione:**
- Secret key: `cdv_jwt_secret` (generato automaticamente)
- Expiration: 7 giorni (default)
- Algoritmo: HS256

---

## 🔒 Sicurezza e Privacy

### Misure di Sicurezza Implementate

#### 1. Autenticazione e Autorizzazione
- ✅ Ruoli personalizzati con capacità granulari
- ✅ Verifica email obbligatoria
- ✅ Approvazione manuale nuovi utenti
- ✅ JWT per API mobile
- ✅ Nonce per tutte le richieste AJAX
- ✅ Capability check su ogni operazione

#### 2. Validazione Input
- ✅ Sanitizzazione dati (`sanitize_text_field`, `sanitize_textarea_field`)
- ✅ Validazione range valori (es. recensioni 1-5)
- ✅ Prepared statements per query database
- ✅ Escape output (`esc_html`, `esc_attr`, `esc_url`)

#### 3. Anti-Spam e Rate Limiting
- ✅ Chat: max 10 messaggi/minuto
- ✅ Recensioni: una per coppia utente/viaggio
- ✅ Segnalazioni: una per utente/recensione

#### 4. Privacy
- ✅ Conversazioni private bloccabili
- ✅ Email messaggi senza contenuto
- ✅ Visibilità profilo configurabile
- ✅ Export dati GDPR
- ✅ Right to be forgotten
- ✅ Anonimizzazione dati su richiesta

#### 5. Protezioni XSS/CSRF
- ✅ Nonce validation
- ✅ Capability checks
- ✅ Output escaping
- ✅ Content Security Policy ready

---

## ⚡ Performance e Ottimizzazioni

### Ottimizzazioni Implementate

#### Database
- ✅ Indici su tutte le foreign key
- ✅ Indici su campi di ricerca frequente
- ✅ Unique constraints dove appropriato
- ✅ ON UPDATE CURRENT_TIMESTAMP per tracking

#### Query
- ✅ Prepared statements
- ✅ Selezione solo campi necessari
- ✅ LIMIT su tutte le liste
- ✅ Pagination

#### Frontend
- ✅ CSS Custom Properties (migliori performance)
- ✅ Transizioni CSS hardware-accelerated
- ✅ Immagini responsive
- ✅ Lazy loading supportato
- ✅ Minificazione assets

#### Caching
- ✅ Transient API per cache query
- ✅ Object caching ready
- ✅ Browser caching via headers

#### Best Practices
- ✅ Enqueue condizionale assets
- ✅ wp_localize_script per dati AJAX
- ✅ Hooks invece di query dirette
- ✅ Autoload disabilitato per meta grandi

---

## 📊 Metriche e Analytics

### Dati Tracciati

**Utenti:**
- Viaggi organizzati/completati
- Partecipazioni
- Reputazione media
- Badge guadagnati
- Tasso completamento profilo

**Viaggi:**
- Views
- Richieste partecipazione
- Tasso accettazione
- Tempo medio fino a completo
- Budget medio

**Engagement:**
- Messaggi inviati
- Recensioni lasciate
- Login frequency
- Retention rate

---

## 🔄 Workflow Completi

### Workflow 1: Registrazione → Primo Viaggio

```
1. Utente si registra
   ↓
2. Email verifica inviata
   ↓
3. Utente clicca link verifica
   ↓
4. Admin riceve notifica nuovo utente
   ↓
5. Admin approva utente
   ↓
6. Email approvazione inviata
   ↓
7. Utente può creare viaggi
   ↓
8. Badge "First Travel" assegnato al primo viaggio pubblicato
```

### Workflow 2: Partecipazione Viaggio

```
1. Utente trova viaggio interessante
   ↓
2. Click "Richiedi Partecipazione"
   ↓
3. Compila messaggio presentazione
   ↓
4. Richiesta salvata (status: pending)
   ↓
5. Messaggio privato automatico inviato a organizzatore
   ↓
6. Notifica in-app a organizzatore
   ↓
7a. ACCETTATO:
    - Status → accepted
    - Accesso chat gruppo
    - Notifica utente
    - Check badge explorer/globetrotter
    ↓
7b. RIFIUTATO:
    - Status → rejected
    - Conversazione bloccata
    - Notifica utente
```

### Workflow 3: Completamento Viaggio → Recensioni

```
1. Organizzatore segna viaggio come "completed"
   ↓
2. Notifiche inviate a tutti i partecipanti
   ↓
3. Partecipanti possono recensirsi reciprocamente
   ↓
4. Ogni recensione:
   - Aggiorna reputazione recensito
   - Check badge "trusted"
   - Notifica recensito
   - Incrementa counter "social" per reviewer
   ↓
5. Recensito può rispondere
   ↓
6. Altri utenti possono segnare recensione come "utile"
```

---

## 🎯 Funzionalità da Aggiungere (Tracking Futuro)

Questo spazio verrà utilizzato per documentare nuove funzionalità aggiunte al sistema.

---

### Sistema Gestione Banner Pubblicitari
**Data Aggiunta:** 2025-11-22
**Versione:** 1.1.0
**Classe:** CDV_Banner_Manager
**Tabelle DB:** wp_cdv_banners

**Descrizione:**
Sistema completo per la gestione di banner pubblicitari con supporto multi-dispositivo (Desktop, Tablet, Mobile) e posizionamenti strategici su tutte le pagine principali del sito.

**Funzionalità:**
- 20+ posizioni predefinite (Homepage, Archivi, Single, Dashboard, Profilo, Calendario)
- Gestione responsive: codice HTML separato per Desktop/Tablet/Mobile
- Editor codice per inserimento HTML/iframe/JavaScript
- Toggle attivo/disattivo per ogni banner
- Shortcode per inserimento manuale: `[cdv_banner position="nome_posizione"]`
- Statistiche performance: impressioni e click
- Anteprima banner prima del salvataggio
- Auto-detection device per visualizzazione corretta
- Sistema di tracking impressioni

**Posizioni Banner Disponibili:**

*Homepage:*
- `homepage_hero_top` - Banner sopra hero section
- `homepage_hero_bottom` - Banner sotto hero section
- `homepage_viaggi_top` - Banner sopra viaggi in evidenza

*Archivio Viaggi:*
- `archive_viaggi_top` - Banner top archivio
- `archive_viaggi_sidebar` - Banner sidebar
- `archive_viaggi_mid` - Banner metà griglia (dopo 6 cards)

*Single Viaggio:*
- `single_viaggio_top` - Banner sotto titolo
- `single_viaggio_sidebar` - Banner sidebar
- `single_viaggio_bottom` - Banner prima commenti

*Archivio Racconti:*
- `archive_racconti_top` - Banner top archivio
- `archive_racconti_sidebar` - Banner sidebar
- `archive_racconti_mid` - Banner metà griglia

*Single Racconto:*
- `single_racconto_top` - Banner sotto titolo
- `single_racconto_mid` - Banner mid-content
- `single_racconto_bottom` - Banner fine articolo

*Dashboard & Profilo:*
- `dashboard_top` - Banner top dashboard
- `dashboard_sidebar` - Banner sidebar dashboard
- `profilo_top` - Banner top profilo
- `profilo_bottom` - Banner sotto recensioni

*Calendario:*
- `calendario_top` - Banner sopra calendario

**Endpoint API:**
Nessun endpoint REST API (solo admin panel)

**AJAX Endpoints:**
- `cdv_save_banner` - Salva/Aggiorna banner
- `cdv_toggle_banner` - Attiva/Disattiva banner

**Meta Associati:**
Nessun meta (storage in tabella dedicata)

**Hook Utilizzati:**
- `admin_menu` - Aggiunge menu "Banner ADV"
- `admin_enqueue_scripts` - Carica CSS/JS admin
- `wp_enqueue_scripts` - Carica CSS frontend

**Shortcode:**
```php
[cdv_banner position="homepage_hero_top"]
[cdv_banner position="single_viaggio_sidebar"]
```

**Struttura Database:**
```sql
CREATE TABLE wp_cdv_banners (
    id bigint(20) AUTO_INCREMENT PRIMARY KEY,
    position_key varchar(100) NOT NULL,
    device varchar(20) NOT NULL,  -- desktop/tablet/mobile
    is_active tinyint(1) DEFAULT 1,
    html_code text,
    css_custom text,
    display_conditions JSON,
    start_date datetime DEFAULT NULL,
    end_date datetime DEFAULT NULL,
    click_count int DEFAULT 0,
    impression_count int DEFAULT 0,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    KEY position_device (position_key, device),
    KEY is_active (is_active)
);
```

**Files Creati:**
- `includes/class-banner-manager.php` - Classe principale
- `admin/views/banner-position-card.php` - Template card posizione
- `admin/views/banner-statistics.php` - Template statistiche
- `admin/css/banner-manager.css` - Stili admin panel
- `admin/js/banner-manager.js` - JavaScript admin panel
- `assets/css/banner-frontend.css` - Stili frontend

**Utilizzo:**
1. Admin → Banner ADV
2. Espandi posizione desiderata
3. Scegli device (Desktop/Tablet/Mobile)
4. Inserisci codice HTML/iframe/script
5. Attiva toggle
6. Salva

Oppure via shortcode nei template:
```php
<?php echo do_shortcode('[cdv_banner position="homepage_hero_top"]'); ?>
```

---

### Sistema Analytics Avanzato
**Data Aggiunta:** 2025-11-22
**Versione:** 1.2.0
**Classe:** CDV_Analytics
**Tabelle DB:** wp_cdv_analytics_snapshots

**Descrizione:**
Sistema di statistiche avanzato basato su snapshot giornalieri per monitorare performance della piattaforma senza creare log pesanti. Utilizza aggregazione dati e cache per performance ottimali.

**Funzionalità:**
- Dashboard widget con statistiche principali (Utenti, Viaggi, Partecipazioni, Recensioni)
- Pagina analytics completa con grafici interattivi (Chart.js)
- Snapshot giornalieri automatici via WP-Cron (1 record/giorno invece di milioni di log)
- Selezione periodo: 7, 30, 90 giorni
- Export dati in CSV e JSON
- Metriche aggregate real-time con sistema di cache (24h transient)
- Grafici trend per: Nuovi utenti, Nuovi viaggi, Nuove partecipazioni, Nuove recensioni
- Top 10 destinazioni e tipi di viaggio più popolari

**Metriche Monitorate:**

*Utenti:*
- Totali, Verificati, In attesa verifica
- Nuovi utenti (periodo selezionato)
- Completamento profilo medio (%)

*Viaggi:*
- Aperti, In corso, Completati, Bozza
- Nuovi viaggi pubblicati (periodo)
- Media partecipanti per viaggio
- Tasso occupazione posti (%)

*Engagement:*
- Partecipazioni totali/accettate/in attesa
- Tasso di accettazione (%)
- Nuove partecipazioni (periodo)

*Recensioni:*
- Totali recensioni
- Rating medio globale
- Nuove recensioni (periodo)

*Messaggistica:*
- Conversazioni attive
- Messaggi totali
- Nuovi messaggi (periodo)

*Conversioni:*
- % profili completi
- % viaggi con almeno 1 partecipante
- % partecipazioni accettate

**Dashboard Widget:**
Aggiunto nel Dashboard WordPress con le 4 metriche principali più importanti in un layout a griglia responsive.

**Pagina Analytics:**
Menu dedicato "Analytics" in amministrazione con:
- Header con selettore periodo e pulsanti export
- Overview grid (6 sezioni stats)
- Charts grid (4 grafici trend)
- Top lists (Destinazioni e Tipi viaggio più popolari)

**AJAX Endpoints:**
- `cdv_get_chart_data` - Carica dati per grafici (con parametri metric e period)
- `cdv_export_analytics` - Esporta dati in CSV o JSON

**Hook Utilizzati:**
- `admin_menu` - Aggiunge menu "Analytics"
- `wp_dashboard_setup` - Aggiunge widget dashboard
- `admin_enqueue_scripts` - Carica CSS/JS e Chart.js
- `cdv_daily_analytics_snapshot` - Cron job giornaliero (4:00 AM)

**Cron Jobs:**
- **Evento:** `cdv_daily_analytics_snapshot`
- **Frequenza:** Giornaliera (4:00 AM)
- **Funzione:** `CDV_Analytics::save_daily_snapshot()`
- **Scopo:** Salva snapshot giornaliero invece di logging continuo

**Struttura Database:**
```sql
CREATE TABLE wp_cdv_analytics_snapshots (
    id bigint(20) AUTO_INCREMENT PRIMARY KEY,
    snapshot_date date NOT NULL,
    metric_type varchar(50) NOT NULL,
    metric_value decimal(10,2) NOT NULL,
    metadata JSON,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY snapshot_metric (snapshot_date, metric_type),
    KEY snapshot_date (snapshot_date),
    KEY metric_type (metric_type)
);
```

**Sistema di Cache:**
- Transient API WordPress (24h TTL)
- Key: `cdv_analytics_overview_stats`
- Invalidazione: Automatica dopo 24h o save_daily_snapshot()
- Benefici: Riduce query DB da ~20 a 0 per richiesta

**Approccio Lightweight:**
Invece di tracciare ogni azione in tempo reale (approccio log-heavy):
- ❌ Log ogni visualizzazione pagina → milioni di record/mese
- ❌ Log ogni click → milioni di record/mese
- ✅ Snapshot giornaliero aggregato → ~50 record/giorno
- ✅ Cache 24h per stats real-time → 0 query su richieste successive

**Grafici Chart.js:**
- Line charts con riempimento area
- Responsive e interattivi
- Tooltip con valori
- Animazioni smooth
- Colori brand-consistent

**Export Funzionalità:**
```javascript
// CSV Export
Data,Utenti Totali,Viaggi Attivi,Partecipazioni,...
2025-11-22,1523,342,892,...

// JSON Export
{
  "date": "2025-11-22",
  "users_total": 1523,
  "viaggi_active": 342,
  ...
}
```

**Files Creati:**
- `includes/class-analytics.php` - Classe principale con logica
- `admin/views/analytics-dashboard-widget.php` - Widget dashboard
- `admin/views/analytics-page.php` - Pagina analytics completa
- `admin/css/analytics.css` - Stili analytics
- `admin/js/analytics.js` - Chart.js integration e export

**Utilizzo:**
1. Admin → Analytics (menu principale)
2. Seleziona periodo (7/30/90 giorni)
3. Visualizza grafici trend e statistiche
4. Export CSV o JSON se necessario

**Widget Dashboard:**
Visibile automaticamente nel Dashboard WordPress per amministratori con quick stats.

**Performance:**
- Prima richiesta: ~20 query DB (poi cache)
- Richieste successive: 0 query (24h cache)
- Storage: ~50 records/giorno = ~18,000/anno (vs milioni con logging)
- Cron job: 1 volta/giorno (basso impatto)

---

### Sistema Tracking Visite (GDPR Compliant)
**Data Aggiunta:** 2025-11-22
**Versione:** 1.3.0
**Classe:** CDV_Analytics_Tracking
**Tabelle DB:** wp_cdv_page_views

**Descrizione:**
Sistema di tracking delle visite alle pagine completamente conforme al GDPR. Traccia le visualizzazioni di pagina in modo anonimo senza salvare dati personali o IP completi. Integrato con il sistema Analytics esistente.

**Caratteristiche Privacy (GDPR Compliant):**
- **IP Anonimizzati**: Solo primi 2 ottetti IPv4 (192.168.x.x → 192.168.0.0) o 48 bit IPv6
- **Session Hash Giornaliero**: Hash che cambia ogni giorno, impedendo tracking a lungo termine
- **Nessun Cookie Terze Parti**: Solo cookie tecnici di prima parte
- **Auto-Cleanup**: Dati eliminati automaticamente dopo 90 giorni
- **Opt-Out**: Gli utenti possono disattivare il tracking tramite cookie `cdv_analytics_optout`
- **Nessun PII**: Non vengono salvate informazioni personalmente identificabili

**Funzionalità Tracking:**
- Visite per tipo di pagina (homepage, viaggi, racconti, archivi, profili)
- Breakdown per device (Desktop/Tablet/Mobile)
- Sorgenti traffico (Direct, Search, Social, Internal, Other)
- Top 10 Viaggi più visitati
- Top 10 Racconti più visitati
- Trend giornaliero visite
- 1 record per sessione/pagina/giorno (no doppi conteggi)

**Metriche Analytics:**
- Visite totali oggi
- Visite settimana
- Visite mese
- Distribuzione per device
- Distribuzione per referrer
- Contenuti più popolari

**Pagine Tracciate:**
- Homepage (`homepage`)
- Single Viaggio (`viaggio` + post_id)
- Single Racconto (`racconto` + post_id)
- Archivio Viaggi (`archive_viaggi`)
- Archivio Racconti (`archive_racconti`)
- Destinazione (`destinazione` + term_id)
- Tipo Viaggio (`tipo_viaggio` + term_id)
- Dashboard (`dashboard`)
- Profilo Utente (`profilo` + user_id)
- Calendario (`calendario`)
- Trova Compagni (`trova_compagni`)

**Struttura Database:**
```sql
CREATE TABLE wp_cdv_page_views (
    id bigint(20) AUTO_INCREMENT PRIMARY KEY,
    view_date date NOT NULL,
    page_type varchar(50) NOT NULL,
    post_id bigint(20) DEFAULT NULL,
    device_type varchar(20) NOT NULL,
    referrer_type varchar(50) DEFAULT NULL,
    session_hash varchar(64) NOT NULL,
    view_count int DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,

    KEY view_date (view_date),
    KEY page_type (page_type),
    KEY post_id (post_id),
    KEY session_date (session_hash, view_date)
);
```

**Anonimizzazione IP:**
```php
// IPv4: 192.168.1.100 → 192.168.0.0
// IPv6: 2001:0db8:85a3:0000:0000:8a2e:0370:7334 → 2001:0db8:85a3::

private static function get_anonymized_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];

    // IPv4: mantiene solo primi 2 ottetti
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        return $parts[0] . '.' . $parts[1] . '.0.0';
    }

    // IPv6: mantiene solo primi 48 bit
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        return implode(':', array_slice($parts, 0, 3)) . '::';
    }
}
```

**Session Hash (Rotating Daily):**
```php
// Hash cambia ogni giorno per impedire tracking a lungo termine
$daily_salt = date('Y-m-d') . wp_salt('auth');
$hash_input = $anonymized_ip . '|' . $user_agent . '|' . $daily_salt;
$session_hash = hash('sha256', $hash_input);
```

**Anti-Doppi Conteggi:**
Il sistema verifica se la stessa sessione ha già visitato la stessa pagina nello stesso giorno e evita di contare nuovamente la vista.

**Opt-Out Utente:**
```php
// L'utente può disattivare il tracking settando questo cookie
setcookie('cdv_analytics_optout', '1', time() + YEAR_IN_SECONDS, '/');

// Il tracking salta automaticamente se il cookie è presente
if (isset($_COOKIE['cdv_analytics_optout']) && $_COOKIE['cdv_analytics_optout'] === '1') {
    return; // Skip tracking
}
```

**Retention Policy:**
Dati eliminati automaticamente dopo 90 giorni via cron job giornaliero:
```php
public static function cleanup_old_data() {
    $retention_days = 90;
    $delete_before = date('Y-m-d', strtotime("-{$retention_days} days"));

    $wpdb->query("DELETE FROM wp_cdv_page_views WHERE view_date < '$delete_before'");
}
```

**Integrazione con Analytics:**
Le metriche di tracking vengono integrate nella dashboard Analytics esistente:
- Sezione "Visite Pagine (Tracking GDPR Compliant)"
- Tabelle "Viaggi Più Visitati" e "Racconti Più Visitati"
- Privacy notice informativa nella pagina Analytics

**Device Detection:**
```php
$device_type = wp_is_mobile() ? 'mobile' : 'desktop';
if (wp_is_mobile() && self::is_tablet()) {
    $device_type = 'tablet';
}
```

**Referrer Classification:**
- `direct`: Nessun referrer o referrer vuoto
- `internal`: Traffico interno al sito
- `search`: Google, Bing, Yahoo, DuckDuckGo, Yandex, Baidu
- `social`: Facebook, Twitter, Instagram, LinkedIn, Pinterest, TikTok, YouTube
- `other`: Altri siti esterni

**Files Creati:**
- `includes/class-analytics-tracking.php` - Classe tracking GDPR compliant

**Files Modificati:**
- `includes/class-analytics.php` - Integrazione metriche visite
- `admin/views/analytics-page.php` - UI sezioni visite e privacy notice
- `compagni-di-viaggi.php` - Init e create table

**Hook Utilizzati:**
- `wp` - Track page view su frontend (non-admin)
- `cdv_daily_analytics_snapshot` - Cleanup dati vecchi

**Utilizzo:**
Il tracking è automatico su tutte le pagine frontend. Le statistiche sono visibili in:
1. Admin → Analytics
2. Sezione "Visite Pagine (Tracking GDPR Compliant)"
3. Tabelle top content con link ai post

**Privacy Notice:**
Disclaimer informativo nella pagina Analytics spiega:
- Tracking completamente anonimo
- Nessun IP completo salvato
- Nessun cookie terze parti
- Auto-cleanup dopo 90 giorni
- Possibilità opt-out

**Performance:**
- Lightweight: 1 check + eventuale 1 insert per page view
- Indexed queries per report veloci
- Auto-cleanup previene crescita eccessiva database
- Cache integration con sistema Analytics esistente

---

### Sistema Gruppi di Interesse
**Data Aggiunta:** 2025-11-23
**Versione:** 1.5.0
**Classe:** CDV_Interest_Groups
**Tabelle DB:** Nessuna (usa taxonomy + user meta + options)

**Descrizione:**
Sistema completo per la gestione di gruppi di interesse tematici. Permette agli utenti di iscriversi a massimo 3 gruppi di interesse per trovare compagni di viaggio con passioni comuni. Ogni gruppo ha un'identità visiva distinta con icone, colori e tag specifici.

**10 Gruppi Predefiniti:**
1. 📸 **Fotografia** - Viaggi fotografici, workshop, location iconiche
2. 🥾 **Trekking** - Escursioni, sentieri, montagna
3. 🏃 **Sport & Avventura** - Attività outdoor, adrenalina, sfide
4. 🍷 **Enogastronomia** - Tour del gusto, cantine, cucina locale
5. 🏛️ **Cultura & Storia** - Musei, siti archeologici, patrimonio UNESCO
6. 🧘 **Benessere & Relax** - Spa, yoga retreat, meditazione
7. 🎒 **Backpacking** - Viaggi low-cost, ostelli, libertà
8. 🚗 **Road Trip** - On the road, libertà, scoperta
9. 🏖️ **Mare & Coste** - Spiagge, snorkeling, vita costiera
10. 🎨 **Arte & Design** - Gallerie, mostre, architettura

**Funzionalità:**
- **Limite 3 Gruppi**: Ogni utente può iscriversi a massimo 3 gruppi
- **Tracking Membri**: Conteggio automatico membri per gruppo (salvato in options)
- **AJAX Real-time**: Join/Leave senza reload pagina
- **Validazioni**: Controlli su max gruppi, duplicati, permessi
- **Member Badges**: Badge "Membro" sui card dei gruppi iscritti
- **Integrazione Viaggi**: Filtro viaggi per gruppo di interesse
- **Statistiche Header**: Mostra gruppi attivi, iscrizioni utente, totale membri
- **Info Section**: Spiegazione funzionamento gruppi con design glassmorphism
- **Responsive Design**: Layout ottimizzato per mobile/tablet/desktop

**Storage Dati:**
- **User Meta**: `cdv_interest_groups` - Array con keys dei gruppi (es: ['fotografia', 'trekking'])
- **Options**: `cdv_group_member_count_{group_key}` - Conteggio membri per gruppo
- **Taxonomy**: `gruppo_interesse` - Tassonomia WordPress per categorizzare viaggi

**Template Pagina:**
- File: `page-gruppi-interesse.php`
- Richiede: Login utente (redirect a login se non autenticato)
- Layout: Grid responsive 3 colonne (desktop) → 1 colonna (mobile)

**Card Design:**
Ogni card gruppo include:
- **Header Gradient**: Colore personalizzato per gruppo
- **Icona Grande**: Emoji/icon identificativo
- **Nome Gruppo**: Titolo prominente
- **Descrizione**: Breve spiegazione interesse
- **Tag**: Lista parole chiave correlate
- **Statistiche**: Numero membri attuali
- **Actions**:
  - Non iscritto: "Iscriviti" + "Esplora Viaggi"
  - Iscritto: "Iscritto ✓" + "Vedi Viaggi"
- **Member Badge**: Badge "⭐ Membro" per gruppi iscritti

**AJAX Endpoints:**
- `cdv_join_group` - Iscrive utente a gruppo
  - Nonce: `cdv_groups_nonce`
  - Params: `group_key`
  - Response: `{success: true, message: "...", member_count: 123}`
  - Validazioni: max 3 gruppi, no duplicati, gruppo valido

- `cdv_leave_group` - Rimuove utente da gruppo
  - Nonce: `cdv_groups_nonce`
  - Params: `group_key`
  - Response: `{success: true, message: "...", member_count: 122}`
  - Conferma: Richiede conferma utente via JavaScript confirm()

- `cdv_get_group_members` - Lista membri gruppo (future use)
  - Nonce: `cdv_groups_nonce`
  - Params: `group_key`, `limit` (default: 20)
  - Response: Array con user data

**Metodi Pubblici:**
```php
// Ottiene tutti i gruppi
CDV_Interest_Groups::get_all_groups()

// Ottiene gruppi dell'utente
CDV_Interest_Groups::get_user_groups($user_id)

// Verifica se utente è in gruppo
CDV_Interest_Groups::is_user_in_group($user_id, $group_key)

// Aggiunge utente a gruppo
CDV_Interest_Groups::add_user_to_group($user_id, $group_key)

// Rimuove utente da gruppo
CDV_Interest_Groups::remove_user_from_group($user_id, $group_key)

// Ottiene conteggio membri
CDV_Interest_Groups::get_group_member_count($group_key)

// Ottiene membri di un gruppo
CDV_Interest_Groups::get_group_members($group_key, $limit = 20)
```

**Validazioni Automatiche:**
- ✅ Max 3 gruppi per utente (blocco lato server + client)
- ✅ Gruppo deve esistere nella lista predefinita
- ✅ No richieste duplicate (controllo prima di aggiungere)
- ✅ Utente deve essere autenticato
- ✅ Nonce security per tutte le richieste AJAX
- ✅ Auto-reload quando si raggiungono 3 gruppi (per disabilitare altri pulsanti)

**UI/UX Features:**
- **Toast Notifications**: Messaggi di successo/errore in alto a destra
  - Verde (#27ae60) per successo
  - Rosso (#e74c3c) per errori
  - Auto-dismiss dopo 3 secondi con fade-out
- **Loading States**: Pulsanti disabilitati durante AJAX con classe .loading
- **Hover Effects**: Card sollevano e cambiano ombra su hover
- **Smooth Animations**: Transizioni CSS per tutti gli stati
- **Disabled States**: Pulsanti "Iscriviti" disabilitati quando max gruppi raggiunto

**Styling (groups.css):**
- File: `themes/compagni-viaggi/assets/css/groups.css`
- Linee: 457
- Features:
  - Card design moderno con border-radius 16px
  - Gradient headers personalizzati per gruppo
  - Hover effects (translateY + box-shadow)
  - Color-coded badges per gruppi
  - Responsive grid (3 col → 2 col → 1 col)
  - Toast notification system
  - Glassmorphism per info section
  - Icon sizing e spacing consistente

**JavaScript (groups.js):**
- File: `themes/compagni-viaggi/assets/js/groups.js`
- Linee: 212
- Features:
  - Event delegation per pulsanti dinamici
  - AJAX handlers con error handling
  - Loading state management
  - UI updates dinamici (pulsanti, badges, contatori)
  - Validazione client-side max 3 gruppi
  - Toast notification system
  - Auto-reload strategico

**Integrazione Viaggi:**
- Taxonomy `gruppo_interesse` permette di categorizzare viaggi per gruppo
- Url filtro: `/archivio-viaggi/?gruppo={group_key}`
- Pulsante "Vedi Viaggi" porta all'archivio filtrato per gruppo
- Futura implementazione: Auto-suggest gruppi in base a interessi viaggio

**Hook Utilizzati:**
- `init` - Registra taxonomy `gruppo_interesse`
- `wp_ajax_cdv_join_group` - Handler join
- `wp_ajax_cdv_leave_group` - Handler leave
- `wp_ajax_cdv_get_group_members` - Handler get members
- `wp_enqueue_scripts` - Enqueue CSS/JS per template

**Enqueue Condizionale:**
Scripts caricati solo su template `page-gruppi-interesse.php`:
```php
wp_enqueue_style('cdv-groups', .../groups.css, [], CDV_THEME_VERSION);
wp_enqueue_script('cdv-groups', .../groups.js, ['jquery'], CDV_THEME_VERSION, true);
wp_localize_script('cdv-groups', 'cdvGroups', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('cdv_groups_nonce'),
    'viaggiUrl' => home_url('/archivio-viaggi/'),
    'maxGroups' => 3
]);
```

**Files Creati:**
- `plugins/compagni-di-viaggi/includes/class-interest-groups.php` (569 righe)
- `themes/compagni-viaggi/page-gruppi-interesse.php` (Template pagina)
- `themes/compagni-viaggi/assets/css/groups.css` (457 righe)
- `themes/compagni-viaggi/assets/js/groups.js` (212 righe)

**Files Modificati:**
- `plugins/compagni-di-viaggi/compagni-di-viaggi.php` - Require e init classe
- `themes/compagni-viaggi/functions.php` - Enqueue scripts

**Utilizzo:**
1. Crea nuova pagina WordPress
2. Seleziona template "Gruppi di Interesse"
3. Pubblica pagina
4. Utenti possono visitare e iscriversi ai gruppi (max 3)
5. I gruppi vengono visualizzati nei profili utente
6. I viaggi possono essere categorizzati per gruppo

**Esempi Dati:**
```php
// Struttura gruppo
[
    'name' => 'Fotografia',
    'icon' => '📸',
    'slug' => 'fotografia',
    'description' => 'Viaggi fotografici, workshop, location iconiche...',
    'color' => '#e74c3c',
    'tags' => ['fotografia', 'photography', 'paesaggi', 'ritratti']
]

// User meta
get_user_meta($user_id, 'cdv_interest_groups', true);
// Returns: ['fotografia', 'trekking', 'benessere']

// Member count
get_option('cdv_group_member_count_fotografia', 0);
// Returns: 127
```

**Roadmap Future:**
- [ ] Sistema di suggerimenti viaggi basati su gruppi
- [ ] Feed dedicato per gruppo con ultime attività
- [ ] Badge speciali per membri attivi di gruppi
- [ ] Eventi/incontri per gruppo
- [ ] Chat di gruppo per membri
- [ ] Statistiche engagement per gruppo
- [ ] Ranking membri più attivi per gruppo

**Performance:**
- Lightweight: No tabelle DB aggiuntive
- User meta: 1 array serializzato per utente
- Options: 10 contatori (1 per gruppo)
- AJAX: Singole query ottimizzate
- Cache: User meta cached by WordPress
- Indexed: Taxonomy terms indicizzate

---

### Template per Nuova Funzionalità

```markdown
### [Nome Funzionalità]
**Data Aggiunta:** YYYY-MM-DD
**Versione:** X.X.X
**Classe:** Nome_Classe
**Tabelle DB:** wp_cdv_nuova_tabella

**Descrizione:**
[Breve descrizione]

**Funzionalità:**
- Feature 1
- Feature 2

**Endpoint API:**
- GET /endpoint
- POST /endpoint

**Meta Associati:**
- cdv_meta_name

**Hook Utilizzati:**
- action_name
- filter_name
```

---

## 📝 Note Finali

### Convenzioni di Codifica
- **Prefisso:** `cdv_` per funzioni, `CDV_` per classi
- **Text Domain:** `compagni-di-viaggi`
- **Hook Naming:** `cdv_{action}_{context}`
- **Database Prefix:** `cdv_`

### Compatibilità
- **WordPress:** 6.0+
- **PHP:** 7.4+
- **MySQL:** 5.7+
- **Browser:** Tutti i moderni browser (Chrome, Firefox, Safari, Edge)

### Licenza
GPL v2 or later

### Supporto
- GitHub: https://github.com/max74vr
- Documentazione: (da definire)

---

**Documento creato il:** 2025-11-22
**Ultima modifica:** 2025-11-22
**Versione Brief:** 1.3.0
