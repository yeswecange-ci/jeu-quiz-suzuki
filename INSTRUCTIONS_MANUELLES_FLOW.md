# 🔧 Instructions manuelles pour ajouter les appels API au flow Twilio

## 🚨 Problème d'importation JSON

Si vous ne pouvez pas importer le fichier JSON directement dans Twilio Studio, suivez ces instructions pour ajouter les widgets HTTP manuellement.

---

## ✅ Solution : Ajouter les widgets HTTP manuellement

### Étape 1 : Ouvrir votre flow actuel dans Twilio Studio

1. Aller sur https://console.twilio.com/
2. Studio → Flows
3. Ouvrir votre flow existant "Quiz Suzuki CAN"

---

### Étape 2 : Configurer les variables du flow

1. Cliquer sur **Flow Configuration** (icône d'engrenage en haut à droite)
2. Onglet **Variables**
3. Cliquer sur **Add new variable** deux fois :

**Variable 1** :
- **Key** : `contest_id`
- **Value** : `1`

**Variable 2** :
- **Key** : `api_base_url`
- **Value** : `https://quiz-suzuki-can.ywcdigital.com/api/game`

4. Cliquer sur **Save**

---

### Étape 3 : Ajouter le widget HTTP pour la question 1

#### A. Trouver le widget `split_question1`

1. Dans le canvas, trouver le widget nommé `split_question1`
2. Cliquer dessus pour voir ses transitions

#### B. Créer un nouveau widget HTTP

1. Faites glisser un widget **Make HTTP Request** depuis la palette de gauche
2. Placez-le entre `split_question1` et `question2`
3. Nommez-le : `http_submit_q1`

#### C. Configurer le widget HTTP

Cliquer sur `http_submit_q1` et remplir :

**Configuration** :
- **Request Method** : `POST`
- **Request URL** :
  ```
  {{flow.variables.api_base_url}}/submit-answer
  ```
- **Content Type** : `application/json`

**Request Body** :
```json
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 1,
  "answer": {{widgets.question1.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}
```

**Advanced Settings** :
- **Timeout** : `10000` (10 secondes)

**Transitions** :
- **Success (2XX)** : Connecter à `question2`
- **Failed** : Connecter aussi à `question2` (pour ne pas bloquer le participant)

#### D. Modifier les transitions de `split_question1`

1. Cliquer sur `split_question1`
2. Pour chacune des 3 conditions (answer = 1, 2, 3) :
   - Changer la transition de `question2` vers `http_submit_q1`

**Avant** :
```
split_question1 → question2
```

**Après** :
```
split_question1 → http_submit_q1 → question2
```

#### E. Modifier aussi `split_no_match_q1`

1. Cliquer sur `split_no_match_q1`
2. Changer les transitions de `question2` vers `http_submit_q1`

---

### Étape 4 : Répéter pour les questions 2, 3 et 4

#### Question 2

**Créer le widget** : `http_submit_q2`

**Configuration** :
- **Request Method** : `POST`
- **Request URL** : `{{flow.variables.api_base_url}}/submit-answer`
- **Content Type** : `application/json`

**Request Body** :
```json
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 2,
  "answer": {{widgets.question2.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}
```

**Transitions** :
- Success → `question3`
- Failed → `question3`

**Modifier** :
- `split_question2` → de `question3` vers `http_submit_q2`
- `split_no_match_q2` → de `question3` vers `http_submit_q2`

---

#### Question 3

**Créer le widget** : `http_submit_q3`

**Configuration** :
- **Request Method** : `POST`
- **Request URL** : `{{flow.variables.api_base_url}}/submit-answer`
- **Content Type** : `application/json`

**Request Body** :
```json
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 3,
  "answer": {{widgets.question3.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}
```

**Transitions** :
- Success → `question4`
- Failed → `question4`

**Modifier** :
- `split_question3` → de `question4` vers `http_submit_q3`
- `split_no_match_q3` → de `question4` vers `http_submit_q3`

---

#### Question 4

**Créer le widget** : `http_submit_q4`

**Configuration** :
- **Request Method** : `POST`
- **Request URL** : `{{flow.variables.api_base_url}}/submit-answer`
- **Content Type** : `application/json`

**Request Body** :
```json
{
  "contest_id": {{flow.variables.contest_id}},
  "whatsapp_number": "{{contact.channel.address}}",
  "question_id": 4,
  "answer": {{widgets.question4.inbound.Body}},
  "conversation_sid": "{{trigger.message.ConversationSid}}"
}
```

**Transitions** :
- Success → `final_message`
- Failed → `final_message`

**Modifier** :
- `split_question4` → de `final_message` vers `http_submit_q4`
- `split_no_match_q4` → de `final_message` vers `http_submit_q4`

---

### Étape 5 : Publier le flow

1. Cliquer sur **Validate** en haut à droite pour vérifier qu'il n'y a pas d'erreurs
2. Cliquer sur **Publish** pour publier le flow

---

## 🧪 Tester le flow

### Test 1 : Dans le simulateur Twilio

1. Cliquer sur **Test** en haut à droite
2. Envoyer un message pour démarrer le flow
3. Répondre "Oui" puis aux 4 questions
4. Dans les logs, vérifier que les widgets HTTP sont appelés

### Test 2 : Vérifier les appels HTTP

1. Après avoir testé, cliquer sur chaque widget `http_submit_q1-4`
2. Voir les détails de l'exécution
3. Vérifier que la réponse est `200 OK`

### Test 3 : Avec un vrai numéro WhatsApp

1. Envoyer un message au numéro WhatsApp
2. Compléter le quiz
3. **Vérifier dans le dashboard Laravel** :
   - https://quiz-suzuki-can.ywcdigital.com/login
   - Aller dans Contests → "Scan & Gagne"
   - Vérifier que les réponses sont enregistrées

---

## 📊 Schéma visuel

Voici à quoi devrait ressembler le flow après modification :

```
question1
    ↓
split_question1 (validation 1/2/3)
    ↓
http_submit_q1 ← NOUVEAU WIDGET
    ↓ (appel API)
    ↓
question2
    ↓
split_question2
    ↓
http_submit_q2 ← NOUVEAU WIDGET
    ↓
question3
    ↓
split_question3
    ↓
http_submit_q3 ← NOUVEAU WIDGET
    ↓
question4
    ↓
split_question4
    ↓
http_submit_q4 ← NOUVEAU WIDGET
    ↓
final_message
```

---

## ✅ Checklist de vérification

Après avoir ajouté tous les widgets :

- [ ] Variables du flow configurées (`contest_id`, `api_base_url`)
- [ ] 4 widgets HTTP créés (`http_submit_q1-4`)
- [ ] Chaque widget HTTP configuré avec :
  - [ ] Method : POST
  - [ ] URL : `{{flow.variables.api_base_url}}/submit-answer`
  - [ ] Content-Type : application/json
  - [ ] Body : JSON avec les bonnes variables
  - [ ] Timeout : 10000
- [ ] Transitions modifiées :
  - [ ] `split_question1` → `http_submit_q1` → `question2`
  - [ ] `split_question2` → `http_submit_q2` → `question3`
  - [ ] `split_question3` → `http_submit_q3` → `question4`
  - [ ] `split_question4` → `http_submit_q4` → `final_message`
  - [ ] Idem pour `split_no_match_q1-4`
- [ ] Flow validé (bouton Validate)
- [ ] Flow publié
- [ ] Test dans le simulateur réussi
- [ ] Test avec WhatsApp réussi
- [ ] Réponses visibles dans le dashboard

---

## ⚠️ Astuces

### Si l'API ne répond pas

**Vérifier** :
1. L'API est accessible : `curl https://quiz-suzuki-can.ywcdigital.com/api/ping`
2. Le serveur est en HTTPS (pas HTTP)
3. Les logs Laravel : `tail -f storage/logs/laravel.log`
4. Les logs Twilio : Console → Monitor → Logs → Studio

### Si les transitions ne se connectent pas

1. Supprimer la transition existante
2. Re-créer la transition vers le bon widget

### Si les variables ne sont pas reconnues

1. Vérifier que les variables sont bien créées dans Flow Configuration
2. Vérifier l'orthographe exacte : `contest_id` et `api_base_url`
3. Publier le flow après avoir ajouté les variables

---

## 🎯 Résultat attendu

Après avoir suivi ces instructions :

✅ Chaque réponse du participant sera automatiquement envoyée à votre API Laravel
✅ Les réponses seront enregistrées dans la base de données
✅ Le score sera calculé en temps réel
✅ Le classement sera mis à jour
✅ Vous pourrez sélectionner les gagnants chaque semaine

---

**Temps estimé** : 30-45 minutes pour ajouter les 4 widgets manuellement

Si vous avez des questions ou des difficultés, consultez les logs Twilio ou Laravel pour identifier le problème.

---

**Date** : 2025-12-17
**Version** : 1.0 - Instructions manuelles
