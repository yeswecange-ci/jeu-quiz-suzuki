# 🏗️ Architecture du système Quiz Suzuki CAN

## Vue d'ensemble

```
┌─────────────────┐
│   Participant   │
│   (WhatsApp)    │
└────────┬────────┘
         │
         │ Message WhatsApp
         ▼
┌─────────────────────────────────────────┐
│         Twilio WhatsApp API             │
│  (Reçoit et envoie des messages)        │
└────────┬────────────────────────────────┘
         │
         │ Déclenche
         ▼
┌─────────────────────────────────────────┐
│         Twilio Studio Flow              │
│  ┌───────────────────────────────────┐  │
│  │ 1. welcome_message                │  │
│  │ 2. ready_question (Oui/Non)       │  │
│  │ 3. question1 → split_question1    │  │
│  │    ↓                              │  │
│  │    http_submit_q1 ──────────────┐ │  │
│  │ 4. question2 → split_question2  │ │  │
│  │    ↓                            │ │  │
│  │    http_submit_q2 ──────────────┤ │  │
│  │ 5. question3 → split_question3  │ │  │
│  │    ↓                            │ │  │
│  │    http_submit_q3 ──────────────┤ │  │
│  │ 6. question4 → split_question4  │ │  │
│  │    ↓                            │ │  │
│  │    http_submit_q4 ──────────────┤ │  │
│  │ 7. final_message                │ │  │
│  └─────────────────────────────────┘ │  │
└────────┬─────────────────────────────┘  │
         │                                 │
         │ HTTP POST                       │
         │ (Soumet les réponses)           │
         ▼                                 │
┌──────────────────────────────────────────┘
│
│  https://quiz-suzuki-can.ywcdigital.com
│
▼
┌─────────────────────────────────────────┐
│     Nginx / Apache (Web Server)         │
│         SSL/HTTPS (Port 443)            │
└────────┬────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────┐
│     Laravel 12 Application              │
│  ┌───────────────────────────────────┐  │
│  │     API Routes (/api/game)        │  │
│  │  ┌─────────────────────────────┐  │  │
│  │  │ POST /submit-answer         │  │  │
│  │  │ GET  /questions/{id}        │  │  │
│  │  │ GET  /participant-status    │  │  │
│  │  │ GET  /leaderboard/{id}      │  │  │
│  │  │ GET  /participant/{number}  │  │  │
│  │  └─────────────────────────────┘  │  │
│  │                                   │  │
│  │     Web Routes (Dashboard)        │  │
│  │  ┌─────────────────────────────┐  │  │
│  │  │ /dashboard                  │  │  │
│  │  │ /contests (CRUD)            │  │  │
│  │  │ /questions (CRUD)           │  │  │
│  │  │ /login, /register           │  │  │
│  │  └─────────────────────────────┘  │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │         Controllers               │  │
│  │  • GameApiController              │  │
│  │  • ContestController              │  │
│  │  • QuestionController             │  │
│  │  • DashboardController            │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │           Models                  │  │
│  │  • Contest                        │  │
│  │  • Question                       │  │
│  │  • Participant                    │  │
│  │  • Response                       │  │
│  │  • Winner                         │  │
│  │  • User                           │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │         Services                  │  │
│  │  • WinnerService                  │  │
│  │    - selectWinnersForWeek()       │  │
│  │    - getWeekLeaderboard()         │  │
│  │    - selectWinners()              │  │
│  └───────────────────────────────────┘  │
└────────┬────────────────────────────────┘
         │
         │ Eloquent ORM
         ▼
┌─────────────────────────────────────────┐
│      MySQL Database                     │
│  ┌───────────────────────────────────┐  │
│  │  Tables:                          │  │
│  │  • contests                       │  │
│  │  • questions                      │  │
│  │  • participants                   │  │
│  │  • responses                      │  │
│  │  • winners                        │  │
│  │  • users                          │  │
│  │  • sessions                       │  │
│  │  • cache                          │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
         │
         │ Lecture (pour Admin)
         ▼
┌─────────────────────────────────────────┐
│      Dashboard Web (Blade/Vite)         │
│  ┌───────────────────────────────────┐  │
│  │  Interfaces:                      │  │
│  │  • Liste des concours             │  │
│  │  • Gestion des questions          │  │
│  │  • Vue des participants           │  │
│  │  • Classements                    │  │
│  │  • Sélection des gagnants         │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
         ▲
         │
    ┌────┴────┐
    │  Admin  │
    │  User   │
    └─────────┘
```

---

## Flux de données : Soumission d'une réponse

```
1. Participant répond "3" sur WhatsApp
   │
   ▼
2. Twilio reçoit le message
   │
   ▼
3. Studio Flow : widget "question1" capte la réponse
   │
   ▼
4. Studio Flow : widget "split_question1" valide (1, 2 ou 3)
   │
   ▼
5. Studio Flow : widget "http_submit_q1" appelle l'API
   │
   │  POST https://quiz-suzuki-can.ywcdigital.com/api/game/submit-answer
   │  {
   │    "contest_id": 1,
   │    "whatsapp_number": "+2250701234567",
   │    "question_id": 1,
   │    "answer": 3,
   │    "conversation_sid": "CHxxxxxxxx"
   │  }
   │
   ▼
6. Laravel : Route → GameApiController@submitAnswer
   │
   ▼
7. Validation des données (Validator)
   │
   ▼
8. Vérification du contest (existe + actif)
   │
   ▼
9. Vérification de la question (existe + appartient au contest + active)
   │
   ▼
10. Création/Récupération du participant
    │  Participant::findOrCreateByWhatsApp("+2250701234567")
    │
    ▼
11. Enregistrement de la réponse
    │  Response::recordAnswer(contest_id, participant_id, question_id, 3)
    │
    │  • Calcul de is_correct (answer == correct_answer)
    │  • Calcul de points_earned (is_correct ? points : 0)
    │  • Insertion dans la table responses
    │
    ▼
12. Calcul du score et de la progression
    │  • getParticipantScore() → Total des points
    │  • getParticipantProgress() → Nombre de questions répondues
    │
    ▼
13. Retour JSON vers Twilio
    │  {
    │    "success": true,
    │    "message": "Bonne réponse !",
    │    "data": {
    │      "is_correct": true,
    │      "points_earned": 1,
    │      "total_score": 1,
    │      "progress": {
    │        "total": 4,
    │        "answered": 1,
    │        "percentage": 25
    │      }
    │    }
    │  }
    │
    ▼
14. Twilio Studio Flow : continue vers question2
```

---

## Flux de données : Sélection des gagnants

```
1. Admin se connecte au dashboard
   │
   ▼
2. Navigue vers Contests → "Scan & Gagne"
   │
   ▼
3. Clique sur "Sélectionner les gagnants de la semaine X"
   │
   ▼
4. Laravel : ContestController@selectWeekWinners
   │
   ▼
5. Appel au WinnerService
   │  selectWinnersForWeek(contest_id, week_number)
   │
   ▼
6. Récupération des scores de la semaine
   │  • Filtrer les réponses par date (week_start → week_end)
   │  • Grouper par participant
   │  • Calculer le total_score par participant
   │
   ▼
7. Filtrage par score minimum
   │  • Garder seulement ceux avec score >= min_score_to_win
   │
   ▼
8. Tri par score décroissant
   │
   ▼
9. Sélection des top N (max_winners = 10)
   │
   ▼
10. Insertion dans la table winners
    │  Pour chaque gagnant:
    │  • rank (1, 2, 3, ...)
    │  • total_score
    │  • week_number
    │  • week_start_date, week_end_date
    │  • notified = false
    │
    ▼
11. Affichage de la liste des gagnants dans le dashboard
    │
    ▼
12. Admin clique sur "Notifier les gagnants"
    │  (Optionnel : envoi d'emails/SMS)
```

---

## Structure de la base de données

```
┌─────────────────┐         ┌─────────────────┐
│    contests     │         │    questions    │
├─────────────────┤         ├─────────────────┤
│ id (PK)         │◄───────┤│ id (PK)         │
│ title           │ 1     *│ contest_id (FK) │
│ description     │         │ order           │
│ whatsapp_number │         │ question_text   │
│ max_winners     │         │ options (JSON)  │
│ min_score_to_win│         │ correct_answer  │
│ status          │         │ points          │
│ start_date      │         │ type            │
│ end_date        │         │ is_active       │
└─────────────────┘         └─────────────────┘
        │                           │
        │ 1                         │ 1
        │                           │
        │ *                         │ *
        │                           │
┌───────┴───────┐         ┌─────────┴───────┐
│   responses   │         │   responses     │
├───────────────┤         ├─────────────────┤
│ id (PK)       │         │ id (PK)         │
│ contest_id(FK)│◄────────│ question_id(FK) │
│ participant_id│         │ participant_id  │
│ question_id   │         │ answer (1-3)    │
│ answer        │         │ is_correct      │
│ is_correct    │         │ points_earned   │
│ points_earned │         │ answered_at     │
│ answered_at   │         └─────────────────┘
└───────────────┘
        │
        │ *
        │
        │ 1
        ▼
┌─────────────────┐
│  participants   │
├─────────────────┤
│ id (PK)         │
│ whatsapp_number │◄─── UNIQUE
│ name            │
│ conversation_sid│
│ metadata (JSON) │
└─────────────────┘
        │
        │ 1
        │
        │ *
        ▼
┌─────────────────┐
│     winners     │
├─────────────────┤
│ id (PK)         │
│ contest_id (FK) │
│ participant_id  │
│ rank            │
│ total_score     │
│ week_number     │
│ week_start_date │
│ week_end_date   │
│ notified        │
│ notified_at     │
│ prize           │
└─────────────────┘

┌─────────────────┐
│      users      │ (Admin)
├─────────────────┤
│ id (PK)         │
│ name            │
│ email (UNIQUE)  │
│ password        │
└─────────────────┘
```

**Contraintes** :
- `responses` : UNIQUE(participant_id, question_id)
- `winners` : UNIQUE(contest_id, participant_id)
- `participants` : UNIQUE(whatsapp_number)

---

## Configuration des environnements

### Développement (Local)

```
┌──────────────────┐
│  Votre machine   │
│                  │
│  • PHP 8.2+      │
│  • Composer      │
│  • Node.js/npm   │
│  • MySQL/SQLite  │
│                  │
│  APP_URL=        │
│    localhost     │
│                  │
│  DB_CONNECTION=  │
│    sqlite        │
└──────────────────┘
```

**Commandes** :
```bash
composer dev    # Lance tout (server, queue, logs, vite)
npm run dev     # Watch mode pour les assets
```

---

### Production

```
┌──────────────────────────────────────┐
│  Serveur Web (VPS/Cloud)             │
│                                      │
│  • Nginx/Apache                      │
│  • PHP 8.2-FPM                       │
│  • MySQL 8.0                         │
│  • SSL Certificate (Let's Encrypt)   │
│  • Firewall (UFW)                    │
│                                      │
│  APP_URL=                            │
│    https://quiz-suzuki-can.         │
│           ywcdigital.com             │
│                                      │
│  DB_CONNECTION=mysql                 │
│  APP_ENV=production                  │
│  APP_DEBUG=false                     │
└──────────────────────────────────────┘
```

**Process** :
- Laravel Queue Worker (systemd service)
- Cron jobs (pour les tâches planifiées)

---

## Sécurité et performance

### Couches de sécurité

```
1. Firewall (Port 80, 443, 22 seulement)
   │
   ▼
2. Nginx/Apache
   │  • Rate limiting
   │  • Security headers
   │  • SSL/TLS
   │
   ▼
3. Laravel Middleware
   │  • CSRF Protection
   │  • Authentication
   │  • Throttling
   │
   ▼
4. Validation des données
   │  • Request validation
   │  • Sanitization
   │
   ▼
5. Eloquent ORM
   │  • SQL Injection protection
   │  • Query binding
   │
   ▼
6. Base de données
   │  • User permissions
   │  • Contraintes FK
```

### Performance

```
┌─────────────────┐
│ Laravel Cache   │
│  • Config       │
│  • Routes       │
│  • Views        │
└─────────────────┘
        │
        ▼
┌─────────────────┐
│ Database Cache  │
│  • Query cache  │
└─────────────────┘
        │
        ▼
┌─────────────────┐
│ Asset Pipeline  │
│  • Vite build   │
│  • Minification │
│  • Versioning   │
└─────────────────┘
```

---

## Monitoring et logs

### Logs disponibles

```
1. Laravel Application Logs
   📁 storage/logs/laravel.log
   • Erreurs PHP
   • Exceptions
   • Requêtes SQL (en debug)

2. Nginx Access Logs
   📁 /var/log/nginx/quiz-suzuki-can-access.log
   • Toutes les requêtes HTTP
   • Codes de réponse
   • IP des clients

3. Nginx Error Logs
   📁 /var/log/nginx/quiz-suzuki-can-error.log
   • Erreurs 500, 502, 503, 504
   • Problèmes PHP-FPM

4. Twilio Studio Logs
   🌐 https://console.twilio.com/monitor/logs/studio
   • Exécution des flows
   • Requêtes HTTP (success/fail)
   • Erreurs de flow

5. MySQL Slow Query Log (optionnel)
   • Requêtes lentes (> 2 secondes)
```

### Métriques à surveiller

```
┌──────────────────────────────────────┐
│  Dashboard Metrics                   │
├──────────────────────────────────────┤
│  • Nombre de participants            │
│  • Réponses par jour/heure           │
│  • Taux de complétion (%)            │
│  • Score moyen                       │
│  • Distribution des réponses         │
│  • Top 10 leaderboard                │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│  Serveur Metrics                     │
├──────────────────────────────────────┤
│  • CPU usage                         │
│  • Memory usage                      │
│  • Disk space                        │
│  • Network I/O                       │
│  • Database connections              │
│  • Response time (API)               │
└──────────────────────────────────────┘
```

---

## Intégrations futures possibles

```
┌─────────────────────────────────────────┐
│  Améliorations possibles                │
├─────────────────────────────────────────┤
│  1. SMS Notifications                   │
│     • Notifier les gagnants par SMS     │
│     • Utiliser Twilio SMS API           │
│                                         │
│  2. Email Marketing                     │
│     • Mailchimp/SendGrid integration    │
│     • Newsletter aux participants       │
│                                         │
│  3. Analytics                           │
│     • Google Analytics                  │
│     • Facebook Pixel                    │
│     • Mixpanel                          │
│                                         │
│  4. CRM Integration                     │
│     • Exporter les leads                │
│     • Salesforce/HubSpot                │
│                                         │
│  5. Social Media                        │
│     • Auto-post gagnants sur Facebook   │
│     • Instagram Stories                 │
│                                         │
│  6. Payment Gateway (si achat requis)   │
│     • Orange Money                      │
│     • MTN Mobile Money                  │
│     • Stripe                            │
└─────────────────────────────────────────┘
```

---

## Technologies utilisées

### Backend
- **Laravel 12.0** - Framework PHP
- **PHP 8.2+** - Langage
- **MySQL 8.0** - Base de données
- **Eloquent ORM** - Mapping objet-relationnel

### Frontend
- **Blade Templates** - Moteur de templating
- **Vite** - Build tool moderne
- **Tailwind CSS 3.1** - Framework CSS
- **Alpine.js 3.4** - JavaScript réactif léger

### Intégrations
- **Twilio WhatsApp API** - Messagerie
- **Twilio Studio** - Flow builder

### Infrastructure
- **Nginx** - Serveur web
- **PHP-FPM** - Process manager
- **Let's Encrypt** - Certificats SSL
- **Systemd** - Gestion des services

---

**Dernière mise à jour** : 2025-12-17
**Version** : 1.0
