# Modifications à apporter au Flow Twilio

## 🎯 Objectif

Intégrer les appels API dans le flow pour enregistrer les réponses des participants dans la base de données Laravel.

---

## 📋 Résumé des modifications

Le flow actuel a **28 states** (widgets). Nous devons ajouter **4 widgets HTTP** pour appeler l'API après chaque question.

### States à ajouter :

1. **http_submit_q1** - Après la réponse à la question 1
2. **http_submit_q2** - Après la réponse à la question 2
3. **http_submit_q3** - Après la réponse à la question 3
4. **http_submit_q4** - Après la réponse à la question 4

---

## 🔧 Modifications détaillées

### ÉTAPE 1 : Configurer les variables du flow

Dans Twilio Studio, aller dans **Flow Configuration → Variables** et ajouter :

```json
{
  "contest_id": 1,
  "api_base_url": "https://quiz-suzuki-can.ywcdigital.com/api/game"
}
```

---

### ÉTAPE 2 : Modifier les transitions

#### A. Modifier `split_question1`

**AVANT** :
```
split_question1 → match (answer = 1, 2 ou 3) → question2
```

**APRÈS** :
```
split_question1 → match (answer = 1, 2 ou 3) → http_submit_q1 → question2
```

**Modifications dans le JSON du flow** :

Localiser la section `split_question1` et modifier les transitions :

```json
{
  "name": "split_question1",
  "type": "split-based-on",
  "transitions": [
    {
      "next": "no_match_q1",
      "event": "noMatch"
    },
    {
      "next": "http_submit_q1",  // ← CHANGÉ de "question2" à "http_submit_q1"
      "event": "match",
      "conditions": [
        {
          "friendly_name": "If value equal_to 1",
          "arguments": ["{{widgets.question1.inbound.Body}}"],
          "type": "equal_to",
          "value": "1"
        }
      ]
    },
    {
      "next": "http_submit_q1",  // ← CHANGÉ
      "event": "match",
      "conditions": [
        {
          "friendly_name": "If value equal_to 2",
          "arguments": ["{{widgets.question1.inbound.Body}}"],
          "type": "equal_to",
          "value": "2"
        }
      ]
    },
    {
      "next": "http_submit_q1",  // ← CHANGÉ
      "event": "match",
      "conditions": [
        {
          "friendly_name": "If value equal_to 3",
          "arguments": ["{{widgets.question1.inbound.Body}}"],
          "type": "equal_to",
          "value": "3"
        }
      ]
    }
  ]
}
```

#### B. Ajouter le widget `http_submit_q1`

Insérer ce nouveau state dans le tableau `states` :

```json
{
  "name": "http_submit_q1",
  "type": "make-http-request",
  "transitions": [
    {
      "next": "question2",
      "event": "success"
    },
    {
      "next": "question2",
      "event": "fail"
    }
  ],
  "properties": {
    "offset": {
      "x": -350,
      "y": 1400
    },
    "method": "POST",
    "content_type": "application/json;charset=utf-8",
    "url": "{{flow.variables.api_base_url}}/submit-answer",
    "body": "{\n  \"contest_id\": {{flow.variables.contest_id}},\n  \"whatsapp_number\": \"{{contact.channel.address}}\",\n  \"question_id\": 1,\n  \"answer\": {{widgets.question1.inbound.Body}},\n  \"conversation_sid\": \"{{trigger.message.ConversationSid}}\"\n}",
    "timeout": 10000
  }
}
```

---

### ÉTAPE 3 : Répéter pour les questions 2, 3 et 4

#### C. Modifier `split_question2` et ajouter `http_submit_q2`

**Modifier les transitions de `split_question2`** :
```json
{
  "next": "http_submit_q2",  // ← au lieu de "question3"
  "event": "match"
}
```

**Ajouter le widget** :
```json
{
  "name": "http_submit_q2",
  "type": "make-http-request",
  "transitions": [
    {
      "next": "question3",
      "event": "success"
    },
    {
      "next": "question3",
      "event": "fail"
    }
  ],
  "properties": {
    "offset": {
      "x": -280,
      "y": 2000
    },
    "method": "POST",
    "content_type": "application/json;charset=utf-8",
    "url": "{{flow.variables.api_base_url}}/submit-answer",
    "body": "{\n  \"contest_id\": {{flow.variables.contest_id}},\n  \"whatsapp_number\": \"{{contact.channel.address}}\",\n  \"question_id\": 2,\n  \"answer\": {{widgets.question2.inbound.Body}},\n  \"conversation_sid\": \"{{trigger.message.ConversationSid}}\"\n}",
    "timeout": 10000
  }
}
```

#### D. Modifier `split_question3` et ajouter `http_submit_q3`

**Modifier les transitions de `split_question3`** :
```json
{
  "next": "http_submit_q3",  // ← au lieu de "question4"
  "event": "match"
}
```

**Ajouter le widget** :
```json
{
  "name": "http_submit_q3",
  "type": "make-http-request",
  "transitions": [
    {
      "next": "question4",
      "event": "success"
    },
    {
      "next": "question4",
      "event": "fail"
    }
  ],
  "properties": {
    "offset": {
      "x": -320,
      "y": 2600
    },
    "method": "POST",
    "content_type": "application/json;charset=utf-8",
    "url": "{{flow.variables.api_base_url}}/submit-answer",
    "body": "{\n  \"contest_id\": {{flow.variables.contest_id}},\n  \"whatsapp_number\": \"{{contact.channel.address}}\",\n  \"question_id\": 3,\n  \"answer\": {{widgets.question3.inbound.Body}},\n  \"conversation_sid\": \"{{trigger.message.ConversationSid}}\"\n}",
    "timeout": 10000
  }
}
```

#### E. Modifier `split_question4` et ajouter `http_submit_q4`

**Modifier les transitions de `split_question4`** :
```json
{
  "next": "http_submit_q4",  // ← au lieu de "final_message"
  "event": "match"
}
```

**Ajouter le widget** :
```json
{
  "name": "http_submit_q4",
  "type": "make-http-request",
  "transitions": [
    {
      "next": "final_message",
      "event": "success"
    },
    {
      "next": "final_message",
      "event": "fail"
    }
  ],
  "properties": {
    "offset": {
      "x": -380,
      "y": 3300
    },
    "method": "POST",
    "content_type": "application/json;charset=utf-8",
    "url": "{{flow.variables.api_base_url}}/submit-answer",
    "body": "{\n  \"contest_id\": {{flow.variables.contest_id}},\n  \"whatsapp_number\": \"{{contact.channel.address}}\",\n  \"question_id\": 4,\n  \"answer\": {{widgets.question4.inbound.Body}},\n  \"conversation_sid\": \"{{trigger.message.ConversationSid}}\"\n}",
    "timeout": 10000
  }
}
```

---

### ÉTAPE 4 : Modifier aussi les chemins "no_match"

N'oubliez pas de modifier aussi les transitions pour `split_no_match_q1`, `split_no_match_q2`, `split_no_match_q3`, et `split_no_match_q4`.

**Pour `split_no_match_q1`** :
```json
{
  "next": "http_submit_q1",  // ← au lieu de "question2"
  "event": "match"
}
```

Répéter pour les autres.

---

## 🧪 Comment tester

### 1. Dans Twilio Studio

1. Ouvrir le flow dans l'éditeur
2. Cliquer sur **"Validate"** pour vérifier qu'il n'y a pas d'erreurs
3. **Publier** le flow (bouton "Publish")
4. Tester avec le widget test de Twilio

### 2. Test complet

1. Envoyer un message WhatsApp au numéro configuré
2. Répondre à toutes les questions
3. Vérifier dans le dashboard Laravel que :
   - Le participant est créé
   - Les 4 réponses sont enregistrées
   - Le score est calculé
4. Consulter le classement

### 3. Vérifier les logs

**Dans Laravel** :
```bash
tail -f storage/logs/laravel.log
```

**Dans Twilio** :
- Aller dans **Monitor → Logs → Studio**
- Vérifier les appels HTTP (200 = succès)

---

## 📊 Schéma du flow modifié

```
welcome_message
    ↓
ready_question
    ↓
split_ready_question (oui/non)
    ↓
question1 (réponse 1/2/3)
    ↓
split_question1
    ↓
**http_submit_q1** ← NOUVEAU
    ↓
question2
    ↓
split_question2
    ↓
**http_submit_q2** ← NOUVEAU
    ↓
question3
    ↓
split_question3
    ↓
**http_submit_q3** ← NOUVEAU
    ↓
question4
    ↓
split_question4
    ↓
**http_submit_q4** ← NOUVEAU
    ↓
final_message
```

---

## ⚠️ Points d'attention

1. **Variables du flow** : S'assurer que `contest_id` et `api_base_url` sont bien définies
2. **URL de l'API** : Utiliser HTTPS (pas HTTP)
3. **Timeout** : 10 secondes pour chaque appel HTTP
4. **Continuer même en cas d'erreur** : Les transitions "fail" pointent aussi vers la question suivante
5. **Format JSON** : Bien respecter les guillemets et accolades

---

## 🔄 Alternative : Webhook unique

Au lieu d'appeler l'API après chaque question, vous pouvez aussi envoyer TOUTES les réponses à la fin :

**Ajouter un widget avant `final_message`** :

```json
{
  "name": "http_submit_all",
  "type": "make-http-request",
  "properties": {
    "method": "POST",
    "url": "{{flow.variables.api_base_url}}/submit-all-answers",
    "body": "{\n  \"contest_id\": {{flow.variables.contest_id}},\n  \"whatsapp_number\": \"{{contact.channel.address}}\",\n  \"answers\": [\n    {\"question_id\": 1, \"answer\": {{widgets.question1.inbound.Body}}},\n    {\"question_id\": 2, \"answer\": {{widgets.question2.inbound.Body}}},\n    {\"question_id\": 3, \"answer\": {{widgets.question3.inbound.Body}}},\n    {\"question_id\": 4, \"answer\": {{widgets.question4.inbound.Body}}}\n  ],\n  \"conversation_sid\": \"{{trigger.message.ConversationSid}}\"\n}"
  }
}
```

**Note** : Cette approche nécessite de créer un nouvel endpoint dans l'API Laravel.

---

**Dernière mise à jour** : 2025-12-17
