# Guide d'intégration : Flow Twilio ↔️ API Laravel

## 📊 État actuel de l'intégration

### ✅ Ce qui fonctionne
- Les questions du flow correspondent à celles dans la base de données
- L'API a tous les endpoints nécessaires
- La structure des données est compatible

### ❌ Ce qui NE fonctionne PAS
- **Le flow bot n'appelle JAMAIS l'API**
- Les réponses des participants ne sont pas enregistrées
- Le classement reste vide
- Pas de suivi des scores

---

## 🔧 Modifications OBLIGATOIRES

### 1. Configurer l'URL de production

**Fichier** : `.env`

```env
# Changer cette ligne :
APP_URL=http://localhost

# Par :
APP_URL=https://quiz-suzuki-can.ywcdigital.com
```

**Commande après modification** :
```bash
php artisan config:clear
php artisan config:cache
```

---

### 2. Ajouter des variables au flow Twilio

Dans Twilio Studio, ajouter ces **Flow Variables** au début du flow :

```json
{
  "contest_id": 1,
  "api_base_url": "https://quiz-suzuki-can.ywcdigital.com/api/game"
}
```

---

### 3. Ajouter des appels HTTP après CHAQUE question

Le flow doit appeler l'API après chaque réponse. Voici comment modifier le flow :

#### A. Après la Question 1 (state: split_question1)

**Insérer un widget "Make HTTP Request"** entre `split_question1` et `question2` :

```
Nom du widget : http_submit_q1
Type : Make HTTP Request
Méthode : POST
URL : {{flow.variables.api_base_url}}/submit-answer
Content-Type : application/json

Body (JSON) :
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 1,
  "answer": {{widgets.question1.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}

Transitions :
  - Success (2XX) → question2
  - Failed → question2 (continuer même si erreur)
```

#### B. Après la Question 2 (state: split_question2)

**Insérer un widget "Make HTTP Request"** entre `split_question2` et `question3` :

```
Nom du widget : http_submit_q2
Type : Make HTTP Request
Méthode : POST
URL : {{flow.variables.api_base_url}}/submit-answer
Content-Type : application/json

Body (JSON) :
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 2,
  "answer": {{widgets.question2.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}

Transitions :
  - Success (2XX) → question3
  - Failed → question3
```

#### C. Après la Question 3 (state: split_question3)

**Insérer un widget "Make HTTP Request"** entre `split_question3` et `question4` :

```
Nom du widget : http_submit_q3
Type : Make HTTP Request
Méthode : POST
URL : {{flow.variables.api_base_url}}/submit-answer
Content-Type : application/json

Body (JSON) :
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 3,
  "answer": {{widgets.question3.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}

Transitions :
  - Success (2XX) → question4
  - Failed → question4
```

#### D. Après la Question 4 (state: split_question4)

**Insérer un widget "Make HTTP Request"** entre `split_question4` et `final_message` :

```
Nom du widget : http_submit_q4
Type : Make HTTP Request
Méthode : POST
URL : {{flow.variables.api_base_url}}/submit-answer
Content-Type : application/json

Body (JSON) :
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 4,
  "answer": {{widgets.question4.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}

Transitions :
  - Success (2XX) → final_message
  - Failed → final_message
```

---

### 4. (Optionnel) Afficher le score dans le message final

Modifier le widget `final_message` pour inclure le score :

**Ajouter un widget "Make HTTP Request"** AVANT `final_message` :

```
Nom du widget : http_get_score
Type : Make HTTP Request
Méthode : GET
URL : {{flow.variables.api_base_url}}/participant-status?contest_id={{flow.variables.contest_id}}&whatsapp_number={{contact.channel.address}}
Content-Type : application/json

Transitions :
  - Success (2XX) → final_message_with_score
  - Failed → final_message
```

**Puis modifier le message final** :

```
🎉 Félicitations ! Tu as terminé le jeu !
✅ Tes réponses ont été enregistrées.

📊 Ton score : {{widgets.http_get_score.parsed.data.score}} points

🙏 Merci pour ta participation

🏆 Les 10 gagnants seront annoncés chaque semaine.

📲 Restez connectés à notre page pour ne rien manquer 🎉🚗✨
```

---

### 5. (Optionnel) Demander le nom du participant

Ajouter un widget après `welcome_message` et avant `ready_question` :

```
Nom du widget : ask_name
Type : send-and-wait-for-reply
Message : "Pour commencer, comment t'appelles-tu ? 😊"
Timeout : 3600

Transitions :
  - incomingMessage → save_name
  - timeout → ready_question
```

Puis ajouter un widget HTTP pour sauvegarder le nom (optionnel, ou le faire lors du premier submit-answer).

---

## 📝 Checklist de déploiement

Avant de mettre en production, vérifier :

- [ ] `.env` configuré avec `APP_URL=https://quiz-suzuki-can.ywcdigital.com`
- [ ] Database migrée : `php artisan migrate`
- [ ] Seeder exécuté : `php artisan db:seed --class=DemoDataSeeder`
- [ ] Variables du flow Twilio configurées (contest_id, api_base_url)
- [ ] Widgets HTTP ajoutés après chaque question
- [ ] Test complet du flow avec un vrai numéro WhatsApp
- [ ] Vérifier que les réponses apparaissent dans le dashboard Laravel
- [ ] Tester l'endpoint : `https://quiz-suzuki-can.ywcdigital.com/api/ping`

---

## 🧪 Test de l'intégration

### 1. Tester l'API manuellement

```bash
# Test ping
curl https://quiz-suzuki-can.ywcdigital.com/api/ping

# Test submit-answer
curl -X POST https://quiz-suzuki-can.ywcdigital.com/api/game/submit-answer \
  -H "Content-Type: application/json" \
  -d '{
    "contest_id": 1,
    "whatsapp_number": "+2250701234567",
    "question_id": 1,
    "answer": 3,
    "conversation_sid": "CHxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }'

# Test get participant status
curl "https://quiz-suzuki-can.ywcdigital.com/api/game/participant-status?contest_id=1&whatsapp_number=+2250701234567"

# Test leaderboard
curl "https://quiz-suzuki-can.ywcdigital.com/api/game/leaderboard/1?limit=10"
```

### 2. Vérifier dans le dashboard

Après avoir testé le bot WhatsApp :

1. Se connecter au dashboard : `https://quiz-suzuki-can.ywcdigital.com/login`
2. Aller sur le concours
3. Vérifier que les participants et réponses apparaissent
4. Consulter le classement

---

## 🔒 Sécurité (Recommandations)

### Ajouter une authentification API (Optionnel mais recommandé)

Pour protéger l'API contre les abus, ajouter un token d'authentification :

**1. Modifier `.env`** :
```env
TWILIO_API_TOKEN=votre_token_secret_ici
```

**2. Créer un middleware** :
```bash
php artisan make:middleware ValidateTwilioToken
```

**3. Dans le middleware** :
```php
if ($request->header('X-API-Token') !== config('services.twilio.api_token')) {
    return response()->json(['error' => 'Unauthorized'], 401);
}
```

**4. Dans le flow Twilio, ajouter le header** :
```
Headers :
  X-API-Token: {{flow.variables.api_token}}
```

---

## 🆘 Dépannage

### Erreur : "Contest not found"
- Vérifier que le seeder a été exécuté
- Vérifier le `contest_id` dans les variables du flow

### Erreur : "Question not found"
- Vérifier que les question_id correspondent (1, 2, 3, 4)
- Vérifier que les questions sont `is_active = true`

### Les réponses ne s'enregistrent pas
- Vérifier les logs Laravel : `storage/logs/laravel.log`
- Tester l'endpoint manuellement avec curl
- Vérifier que le serveur est accessible depuis Twilio

### Erreur CORS (si vous utilisez des requêtes depuis le navigateur)
- Installer le package : `composer require fruitcake/laravel-cors`
- Configurer dans `config/cors.php`

---

## 📞 Support

Pour toute question :
1. Consulter les logs : `tail -f storage/logs/laravel.log`
2. Tester les endpoints avec Postman ou curl
3. Vérifier le flow Twilio dans le debugger

---

**Date de création** : 2025-12-17
**Version de l'API** : Laravel 12.0
**Version du flow** : Twilio Studio v2
