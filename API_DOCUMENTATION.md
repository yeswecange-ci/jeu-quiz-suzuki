# Documentation complète des API - Quiz Game

## Base URL

```
Production: https://quiz-suzuki-can.ywcdigital.com/api
Local: http://localhost:8000/api
```

## Table des matières

1. [Test de l'API](#1-test-de-lapi)
2. [Soumettre une réponse](#2-soumettre-une-réponse)
3. [Obtenir les questions](#3-obtenir-les-questions)
4. [Statut du participant](#4-statut-du-participant)
5. [Informations du participant](#5-informations-du-participant)
6. [Classement](#6-classement)
7. [Intégration Twilio](#intégration-twilio)

---

## 1. Test de l'API

### GET /api/ping

Vérifier que l'API fonctionne.

**Réponse** :
```json
{
  "success": true,
  "message": "Quiz Game API is running",
  "timestamp": "2025-12-18T14:47:49.112930Z"
}
```

**Test curl** :
```bash
curl https://quiz-suzuki-can.ywcdigital.com/api/ping
```

---

## 2. Soumettre une réponse

### POST /api/game/submit-answer

Enregistre la réponse d'un participant à une question.

**Paramètres** :

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `contest_id` | integer | Oui | ID du concours |
| `whatsapp_number` | string | Oui | Numéro WhatsApp du participant |
| `question_id` | integer | Oui | ID de la question |
| `answer` | integer | Oui | Réponse (1, 2 ou 3) |
| `conversation_sid` | string | Non | ID de la conversation Twilio |
| `profile_name` | string | Non | Nom du profil WhatsApp |

**Exemple de requête** :
```json
{
  "contest_id": 1,
  "whatsapp_number": "+2250701234567",
  "question_id": 1,
  "answer": 1,
  "conversation_sid": "CH1234567890",
  "profile_name": "Jean Dupont"
}
```

**Réponse succès** (200 OK) :
```json
{
  "success": true,
  "message": "Bonne réponse !",
  "data": {
    "is_correct": true,
    "points_earned": 2,
    "total_score": 2,
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

**Réponse mauvaise réponse** (200 OK) :
```json
{
  "success": true,
  "message": "Mauvaise réponse",
  "data": {
    "is_correct": false,
    "points_earned": 0,
    "total_score": 0,
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

**Erreur** (400 Bad Request) :
```json
{
  "success": false,
  "message": "Ce concours n'est pas actif",
  "status": "draft"
}
```

**Erreur validation** (422 Unprocessable Entity) :
```json
{
  "success": false,
  "message": "Données invalides",
  "errors": {
    "answer": ["The answer field must be between 1 and 3."]
  }
}
```

**Test curl** :
```bash
curl -X POST https://quiz-suzuki-can.ywcdigital.com/api/game/submit-answer \
  -H "Content-Type: application/json" \
  -d '{
    "contest_id": 1,
    "whatsapp_number": "+2250701234567",
    "question_id": 1,
    "answer": 1,
    "conversation_sid": "CHtest123",
    "profile_name": "Test User"
  }'
```

---

## 3. Obtenir les questions

### GET /api/game/questions/{contest_id}

Récupère toutes les questions d'un concours.

**Paramètres URL** :
- `contest_id` : ID du concours

**Réponse** (200 OK) :
```json
{
  "success": true,
  "data": {
    "contest": {
      "id": 1,
      "title": "Quizz CAN Suzuki 2025",
      "status": "active",
      "is_active": true
    },
    "questions": [
      {
        "id": 1,
        "order": 1,
        "question_text": "En quelle année la CAN a-t-elle été créée ?",
        "options": {
          "1": "1957",
          "2": "1960",
          "3": "1965"
        },
        "points": 2,
        "type": "multiple_choice"
      },
      {
        "id": 2,
        "order": 2,
        "question_text": "Quel pays a remporté le plus de titres CAN ?",
        "options": {
          "1": "Égypte",
          "2": "Cameroun",
          "3": "Ghana"
        },
        "points": 2,
        "type": "multiple_choice"
      }
    ]
  }
}
```

**Test curl** :
```bash
curl https://quiz-suzuki-can.ywcdigital.com/api/game/questions/1
```

---

## 4. Statut du participant

### GET /api/game/participant-status

Obtenir le statut d'un participant dans un concours spécifique.

**Paramètres query** :

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `contest_id` | integer | Oui | ID du concours |
| `whatsapp_number` | string | Oui | Numéro WhatsApp |

**Réponse participant existant** (200 OK) :
```json
{
  "success": true,
  "data": {
    "has_started": true,
    "has_completed": false,
    "progress": {
      "total": 4,
      "answered": 2,
      "percentage": 50
    },
    "score": 4
  }
}
```

**Réponse nouveau participant** (200 OK) :
```json
{
  "success": true,
  "data": {
    "has_started": false,
    "progress": {
      "total": 4,
      "answered": 0,
      "percentage": 0
    },
    "score": 0
  }
}
```

**Test curl** :
```bash
curl "https://quiz-suzuki-can.ywcdigital.com/api/game/participant-status?contest_id=1&whatsapp_number=+2250701234567"
```

---

## 5. Informations du participant

### GET /api/game/participant/{whatsapp_number}

Récupère les informations globales d'un participant (tous concours).

**Paramètres URL** :
- `whatsapp_number` : Numéro WhatsApp (URL encodé)

**Réponse** (200 OK) :
```json
{
  "success": true,
  "data": {
    "id": 1,
    "whatsapp_number": "+2250701234567",
    "name": null,
    "total_score": 6,
    "wins_count": 0
  }
}
```

**Erreur participant non trouvé** (404 Not Found) :
```json
{
  "success": false,
  "message": "Participant non trouvé"
}
```

**Test curl** :
```bash
curl https://quiz-suzuki-can.ywcdigital.com/api/game/participant/+2250701234567
```

---

## 6. Classement

### GET /api/game/leaderboard/{contest_id}

Obtenir le classement d'un concours.

**Paramètres URL** :
- `contest_id` : ID du concours

**Paramètres query** :
- `limit` (optionnel) : Nombre de participants à retourner (défaut: 10)

**Réponse** (200 OK) :
```json
{
  "success": true,
  "data": [
    {
      "rank": 1,
      "whatsapp_number": "+2250701234567",
      "name": "Jean Dupont",
      "total_score": 8,
      "questions_answered": 4
    },
    {
      "rank": 2,
      "whatsapp_number": "+2250709876543",
      "name": "Participant 2",
      "total_score": 6,
      "questions_answered": 4
    }
  ]
}
```

**Test curl** :
```bash
curl "https://quiz-suzuki-can.ywcdigital.com/api/game/leaderboard/1?limit=10"
```

---

## Intégration Twilio

### Configuration des widgets HTTP dans Twilio Studio

Pour chaque question du quiz, vous devez créer un widget HTTP avec les paramètres suivants :

**Type de widget** : `Make HTTP Request`

**Configuration** :
- **Method** : POST
- **Content Type** : `application/json; charset=utf-8`
- **URL** : `{{flow.variables.api_base_url}}/submit-answer`

**Body** pour Question 1 :
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

**Body** pour Question 2 :
```json
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 2,
  "answer": {{widgets.question2.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}",
  "profile_name": "{{contact.channel.user_info.user_name}}"
}
```

*Et ainsi de suite pour les questions 3 et 4...*

**Transitions** :
```json
{
  "transitions": [
    {
      "next": "question2",
      "event": "success"
    },
    {
      "next": "question2",
      "event": "failed"
    }
  ]
}
```

⚠️ **Important** : Les transitions "failed" doivent pointer vers la question suivante pour éviter que le flow ne se bloque.

### Variables de flow Twilio

Définir ces variables au début du flow :

| Variable | Valeur |
|----------|--------|
| `api_base_url` | `https://quiz-suzuki-can.ywcdigital.com/api/game` |
| `contest_id` | `1` (ou l'ID de votre concours) |

### Variables disponibles dans Twilio

| Variable Twilio | Description | Utilisation API |
|-----------------|-------------|-----------------|
| `{{contact.channel.address}}` | Numéro WhatsApp | `whatsapp_number` |
| `{{contact.channel.user_info.user_name}}` | Nom du profil WhatsApp | `profile_name` |
| `{{trigger.message.ConversationSid}}` | ID de la conversation | `conversation_sid` |
| `{{widgets.question1.inbound.Body}}` | Réponse du participant | `answer` |

---

## Tests complets

### Script de test automatique

Un script de test est disponible dans le projet : `test_all_api.php`

**Exécution** :
```bash
cd /path/to/project
php test_all_api.php
```

**Ce qu'il teste** :
1. ✅ API Ping
2. ✅ Récupération des questions
3. ✅ Soumission d'une réponse
4. ✅ Récupération des infos participant
5. ✅ Statut du participant
6. ✅ Classement

### Test manuel avec cURL

**1. Tester l'API** :
```bash
curl https://quiz-suzuki-can.ywcdigital.com/api/ping
```

**2. Récupérer les questions** :
```bash
curl https://quiz-suzuki-can.ywcdigital.com/api/game/questions/1
```

**3. Soumettre une réponse** :
```bash
curl -X POST https://quiz-suzuki-can.ywcdigital.com/api/game/submit-answer \
  -H "Content-Type: application/json" \
  -d '{
    "contest_id": 1,
    "whatsapp_number": "+2250701234567",
    "question_id": 1,
    "answer": 1,
    "conversation_sid": "CHtest123",
    "profile_name": "Test User"
  }'
```

**4. Vérifier le classement** :
```bash
curl https://quiz-suzuki-can.ywcdigital.com/api/game/leaderboard/1
```

---

## Codes d'erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 400 | Requête invalide (concours inactif, etc.) |
| 404 | Ressource non trouvée |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

---

## Sécurité

### CORS

L'API accepte les requêtes cross-origin depuis tous les domaines pour les tests.
En production, vous pouvez restreindre les origines dans `config/cors.php`.

### Rate Limiting

Pas de rate limiting actuellement. À implémenter si nécessaire pour éviter les abus.

### Validation

Toutes les entrées sont validées :
- `answer` : doit être entre 1 et 3
- `contest_id` : doit exister en base
- `question_id` : doit exister et appartenir au concours
- `whatsapp_number` : format string

---

## Résumé des endpoints

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/ping` | Test de l'API |
| GET | `/api/game/questions/{contest_id}` | Lister les questions |
| POST | `/api/game/submit-answer` | Soumettre une réponse |
| GET | `/api/game/participant/{whatsapp_number}` | Infos participant (global) |
| GET | `/api/game/participant-status` | Statut dans un concours |
| GET | `/api/game/leaderboard/{contest_id}` | Classement |

---

## Support

**Fichiers de référence** :
- Code des routes : `routes/api.php`
- Controller : `app/Http/Controllers/Api/GameApiController.php`
- Documentation Twilio : `INSTRUCTIONS_MANUELLES_FLOW.md`
- Tests : `test_all_api.php`

**Date** : 2025-12-18
**Version** : 1.0
**Statut** : ✅ Tous les endpoints testés et fonctionnels
