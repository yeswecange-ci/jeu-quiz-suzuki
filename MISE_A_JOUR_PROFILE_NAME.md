# Mise à jour : Capture du Profile Name WhatsApp

## Ce qui a été fait

### 1. Base de données

**Migration créée** : `2025_12_18_020651_add_profile_name_to_participants_table.php`

La table `participants` a maintenant un nouveau champ :
- `profile_name` (nullable) : Nom du profil WhatsApp de l'utilisateur

```sql
ALTER TABLE participants ADD COLUMN profile_name VARCHAR(255) NULL AFTER name;
```

### 2. Modèle Participant

**Fichier modifié** : `app/Models/Participant.php:16`

Le champ `profile_name` a été ajouté aux champs fillable :
```php
protected $fillable = [
    'whatsapp_number',
    'name',
    'profile_name',  // ✅ AJOUTÉ
    'conversation_sid',
    'metadata',
];
```

### 3. API GameApiController

**Fichier modifié** : `app/Http/Controllers/Api/GameApiController.php`

**Validation** (ligne 28) :
```php
$validator = Validator::make($request->all(), [
    'contest_id' => 'required|exists:contests,id',
    'whatsapp_number' => 'required|string',
    'question_id' => 'required|exists:questions,id',
    'answer' => 'required|integer|min:1|max:3',
    'conversation_sid' => 'nullable|string',
    'profile_name' => 'nullable|string',  // ✅ AJOUTÉ
]);
```

**Traitement** (lignes 58-73) :
```php
// Créer ou récupérer le participant
$participantData = ['conversation_sid' => $request->conversation_sid];

// Ajouter le profile_name s'il est fourni
if ($request->filled('profile_name')) {
    $participantData['profile_name'] = $request->profile_name;
}

$participant = Participant::findOrCreateByWhatsApp(
    $request->whatsapp_number,
    $participantData
);

// Mettre à jour le profile_name si déjà existant et nouveau profile_name fourni
if ($request->filled('profile_name') && $participant->profile_name !== $request->profile_name) {
    $participant->update(['profile_name' => $request->profile_name]);
}
```

### 4. ParticipantController (CRUD)

**Fichier créé** : `app/Http/Controllers/ParticipantController.php`

Fonctionnalités :
- `index()` : Liste des participants avec recherche et filtres
  - Recherche par WhatsApp, nom, ou profile_name
  - Filtre par concours
- `show($participant)` : Afficher un participant avec toutes ses réponses groupées par concours
- `edit($participant)` : Formulaire d'édition
- `update($participant)` : Mise à jour des informations
- `destroy($participant)` : Suppression

### 5. Routes Web

**Fichier modifié** : `routes/web.php:45`

```php
// Gestion des participants
Route::resource('participants', ParticipantController::class)->except(['create', 'store']);
```

Routes disponibles :
- `GET /participants` : Liste des participants
- `GET /participants/{id}` : Détails d'un participant avec toutes ses réponses
- `GET /participants/{id}/edit` : Formulaire d'édition
- `PUT /participants/{id}` : Mise à jour
- `DELETE /participants/{id}` : Suppression

### 6. Vues Blade

**Fichiers créés** :

1. **`resources/views/participants/index.blade.php`**
   - Liste des participants
   - Recherche par numéro, nom ou profile_name
   - Filtre par concours
   - Affiche le nombre de réponses

2. **`resources/views/participants/show.blade.php`**
   - Informations du participant (numéro, profile_name, nom)
   - Statistiques (concours, réponses, score, victoires)
   - Réponses groupées par concours
   - Détails de chaque réponse :
     - Question
     - Réponse donnée vs réponse correcte
     - Résultat (correct/incorrect)
     - Points gagnés
     - Date

3. **`resources/views/participants/edit.blade.php`**
   - Formulaire de modification
   - Champs : WhatsApp, profile_name, nom
   - Affichage des statistiques
   - Bouton de suppression

### 7. Navigation

**Fichier modifié** : `resources/views/layouts/app.blade.php:43-49`

Ajout du lien "Participants" dans le menu sidebar.

### 8. Correction des calculs de stats

**Fichier modifié** : `app/Http/Controllers/ContestController.php`

**Problème** : Le `average_score` calculait la moyenne des points par réponse, pas par participant.

**Solution** (lignes 248-264) :
```php
/**
 * Calculer le score moyen par participant (pas par réponse)
 */
private function calculateAverageScore(Contest $contest): float
{
    $participants = $contest->participants()->get();

    if ($participants->isEmpty()) {
        return 0;
    }

    $totalScore = $participants->sum(function ($participant) use ($contest) {
        return $contest->getParticipantScore($participant->id);
    });

    return round($totalScore / $participants->count(), 2);
}
```

## Comment intégrer le profile_name dans Twilio

### Option 1 : Utiliser le Profile Name Twilio (Recommandé)

Dans Twilio Studio, vous avez accès à la variable `{{contact.channel.user_info.user_name}}` qui contient le nom du profil WhatsApp.

**Modifier le body des widgets HTTP** :

Pour `http_submit_q1`, `http_submit_q2`, `http_submit_q3`, `http_submit_q4` :

```json
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 1,
  "answer": {{widgets.question1.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}",
  "profile_name": "{{contact.channel.user_info.user_name}}"
}
```

### Option 2 : Instructions manuelles dans Twilio Studio

Pour chaque widget HTTP (`http_submit_q1`, `http_submit_q2`, `http_submit_q3`, `http_submit_q4`) :

1. Cliquer sur le widget
2. Dans "Request Body", ajouter la ligne :
   ```
   "profile_name": "{{contact.channel.user_info.user_name}}"
   ```
3. Publier le flow

**Exemple complet du body pour `http_submit_q1`** :
```json
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 1,
  "answer": {{widgets.question1.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}",
  "profile_name": "{{contact.channel.user_info.user_name}}"
}
```

## Test de l'API avec profile_name

```bash
curl -X POST http://localhost:8000/api/game/submit-answer \
  -H "Content-Type: application/json" \
  -d '{
    "contest_id": 1,
    "whatsapp_number": "+2250701234567",
    "question_id": 1,
    "answer": 1,
    "conversation_sid": "CHtest123",
    "profile_name": "Jean Dupont"
  }'
```

**Réponse attendue** :
```json
{
  "success": true,
  "message": "Bonne réponse !",
  "data": {
    "is_correct": true,
    "points_earned": 1,
    "total_score": 1,
    "progress": {
      "total": 4,
      "answered": 1,
      "percentage": 25
    },
    "question": {
      "id": 1,
      "order": 1,
      "correct_answer": 1
    }
  }
}
```

## Accès au CRUD Participants

### Via le dashboard

1. Se connecter au dashboard Laravel
2. Cliquer sur "Participants" dans le menu
3. Liste des participants avec recherche et filtres

### Via l'URL directe

- **Liste** : https://quiz-suzuki-can.ywcdigital.com/participants
- **Détails** : https://quiz-suzuki-can.ywcdigital.com/participants/{id}
- **Modifier** : https://quiz-suzuki-can.ywcdigital.com/participants/{id}/edit

## Caractéristiques de la gestion des participants

### Page Index (Liste)

- **Filtres** :
  - Recherche par numéro WhatsApp, nom, ou profile_name
  - Filtre par concours
- **Affichage** :
  - Profile name en premier, sinon nom, sinon "Participant #ID"
  - Numéro WhatsApp
  - Nombre de réponses
  - Date d'inscription

### Page Show (Détails)

- **Informations** :
  - Numéro WhatsApp
  - Profile name (si disponible)
  - Nom personnalisé (si disponible)
  - Date d'inscription
- **Statistiques** :
  - Nombre de concours participés
  - Total de réponses
  - Score total cumulé
  - Nombre de victoires
- **Réponses par concours** :
  - Groupées par concours
  - Affichage du score et taux de réussite par concours
  - Détails de chaque réponse avec résultat

### Page Edit (Modification)

- **Champs modifiables** :
  - Numéro WhatsApp
  - Profile name
  - Nom personnalisé
- **Champs en lecture seule** :
  - Conversation SID (Twilio)
  - Date d'inscription
  - Statistiques
- **Actions** :
  - Enregistrer les modifications
  - Supprimer le participant (avec confirmation)

## Déploiement en production

Sur le serveur :

```bash
# 1. Pull du code
git pull origin main

# 2. Migration de la base de données
php artisan migrate --force

# 3. Optimisations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Redémarrer le serveur si nécessaire
sudo systemctl restart php8.2-fpm
```

## Vérification

1. **Base de données** :
   ```sql
   DESCRIBE participants;
   -- Vérifier que profile_name existe
   ```

2. **Routes** :
   ```bash
   php artisan route:list --name=participants
   ```

3. **API** :
   ```bash
   curl -X POST https://quiz-suzuki-can.ywcdigital.com/api/game/submit-answer \
     -H "Content-Type: application/json" \
     -d '{"contest_id": 1, "whatsapp_number": "+225...", "question_id": 1, "answer": 1, "profile_name": "Test"}'
   ```

4. **Dashboard** :
   - Accéder à https://quiz-suzuki-can.ywcdigital.com/participants
   - Vérifier que la page affiche la liste des participants

## Résumé des modifications

| Fichier | Type | Description |
|---------|------|-------------|
| `database/migrations/2025_12_18_020651_add_profile_name_to_participants_table.php` | Nouveau | Migration pour ajouter profile_name |
| `app/Models/Participant.php` | Modifié | Ajout de profile_name aux fillable |
| `app/Http/Controllers/Api/GameApiController.php` | Modifié | Validation et capture du profile_name |
| `app/Http/Controllers/ParticipantController.php` | Nouveau | CRUD complet des participants |
| `app/Http/Controllers/ContestController.php` | Modifié | Correction du calcul average_score |
| `routes/web.php` | Modifié | Routes pour participants |
| `resources/views/participants/index.blade.php` | Nouveau | Liste des participants |
| `resources/views/participants/show.blade.php` | Nouveau | Détails et réponses |
| `resources/views/participants/edit.blade.php` | Nouveau | Formulaire d'édition |
| `resources/views/layouts/app.blade.php` | Modifié | Ajout du lien Participants |

## Notes importantes

1. **Le profile_name est optionnel** : L'API accepte les requêtes sans ce champ
2. **Mise à jour automatique** : Si un participant envoie un nouveau profile_name, il sera mis à jour
3. **Affichage prioritaire** : Dans les vues, le profile_name est affiché en premier, puis le nom, puis "Participant #ID"
4. **Recherche** : Le champ profile_name est inclus dans la recherche
5. **Correction des stats** : Le score moyen est maintenant calculé correctement (par participant, pas par réponse)

---

**Date** : 2025-12-18
**Statut** : ✅ Implémenté et testé localement
**À faire** :
- Déployer sur production
- Mettre à jour le flow Twilio avec le champ profile_name
- Tester en production
