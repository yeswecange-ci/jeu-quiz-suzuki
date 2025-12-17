# 📥 Guide d'importation du Flow Twilio mis à jour

## ✅ Ce qui a été modifié

Le fichier `twilio-flow-updated.json` contient le flow original **avec les modifications suivantes** :

### 1. **Variables du flow ajoutées**
```json
{
  "contest_id": 1,
  "api_base_url": "https://quiz-suzuki-can.ywcdigital.com/api/game"
}
```

### 2. **4 nouveaux widgets HTTP ajoutés**
- `http_submit_q1` - Soumet la réponse à la question 1
- `http_submit_q2` - Soumet la réponse à la question 2
- `http_submit_q3` - Soumet la réponse à la question 3
- `http_submit_q4` - Soumet la réponse à la question 4

### 3. **Transitions modifiées**
Tous les widgets de validation (`split_question1-4` et `split_no_match_q1-4`) redirigent maintenant vers les widgets HTTP au lieu d'aller directement à la question suivante.

**Avant** :
```
split_question1 → question2
```

**Après** :
```
split_question1 → http_submit_q1 → question2
```

---

## 📋 Instructions d'importation

### Option 1 : Importer comme nouveau flow (RECOMMANDÉ)

1. **Se connecter à Twilio Console**
   - Aller sur https://console.twilio.com/

2. **Accéder à Studio**
   - Cliquer sur **Explore Products** (menu de gauche)
   - Cliquer sur **Studio**
   - Cliquer sur **Flows**

3. **Créer un nouveau flow**
   - Cliquer sur le bouton **+ (Create new Flow)**
   - Nom : `Quiz Suzuki CAN - With API`
   - Cliquer sur **Next**
   - Sélectionner **Import from JSON**
   - Cliquer sur **Next**

4. **Importer le fichier**
   - Cliquer sur **Upload JSON** ou **Paste JSON**
   - Sélectionner le fichier `twilio-flow-updated.json`
   - OU copier-coller le contenu du fichier
   - Cliquer sur **Next**

5. **Vérifier le flow**
   - Le flow doit s'afficher avec 32 widgets (28 originaux + 4 nouveaux)
   - Vérifier que les widgets HTTP sont bien présents :
     - `http_submit_q1`
     - `http_submit_q2`
     - `http_submit_q3`
     - `http_submit_q4`

6. **Configurer les variables** (IMPORTANT)
   - Cliquer sur **Flow Configuration** (icône d'engrenage en haut à droite)
   - Onglet **Variables**
   - Vérifier que les variables sont bien là :
     ```
     contest_id = 1
     api_base_url = https://quiz-suzuki-can.ywcdigital.com/api/game
     ```
   - Si elles ne sont pas là, les ajouter manuellement

7. **Publier le flow**
   - Cliquer sur **Publish** (en haut à droite)
   - Confirmer

8. **Attacher le flow au numéro WhatsApp**
   - Aller dans **Messaging** → **Try it out** → **Send a WhatsApp message**
   - Ou **Messaging** → **Settings** → **WhatsApp Sandbox Settings**
   - Dans **When a message comes in**, sélectionner votre nouveau flow
   - Sauvegarder

---

### Option 2 : Remplacer le flow existant

⚠️ **ATTENTION** : Cette méthode écrase le flow actuel. Faites une sauvegarde avant !

1. **Sauvegarder le flow actuel**
   - Ouvrir votre flow existant
   - Cliquer sur **...** (menu) → **Export to JSON**
   - Sauvegarder le fichier (backup)

2. **Ouvrir le flow existant**
   - Studio → Flows → Votre flow actuel

3. **Importer le JSON**
   - Cliquer sur **...** (menu) → **Import from JSON**
   - Sélectionner `twilio-flow-updated.json`
   - Confirmer l'écrasement

4. **Vérifier et publier**
   - Vérifier que tout est correct
   - Publier le flow

---

## 🔍 Vérifications après importation

### 1. Vérifier les widgets HTTP

Pour chaque widget `http_submit_q1`, `http_submit_q2`, `http_submit_q3`, `http_submit_q4` :

**Cliquer sur le widget et vérifier** :

- **Type** : `Make HTTP Request`
- **Method** : `POST`
- **URL** : `{{flow.variables.api_base_url}}/submit-answer`
- **Content Type** : `application/json;charset=utf-8`
- **Body** (exemple pour Q1) :
  ```json
  {
    "contest_id": {{flow.variables.contest_id}},
    "whatsapp_number": "{{contact.channel.address}}",
    "question_id": 1,
    "answer": {{widgets.question1.inbound.Body}},
    "conversation_sid": "{{trigger.message.ConversationSid}}"
  }
  ```
- **Timeout** : `10000` ms (10 secondes)
- **Transitions** :
  - `Success (2XX)` → `question2` (ou q3, q4, final_message)
  - `Failed` → `question2` (ou q3, q4, final_message)

### 2. Vérifier les variables du flow

- Cliquer sur **Flow Configuration**
- Onglet **Variables**
- Vérifier :
  - `contest_id` = `1`
  - `api_base_url` = `https://quiz-suzuki-can.ywcdigital.com/api/game`

⚠️ **IMPORTANT** : Si vous déployez sur une URL différente, modifiez `api_base_url` !

### 3. Vérifier les transitions

**split_question1** doit avoir 4 transitions :
- `noMatch` → `no_match_q1`
- `match` (answer = 1) → `http_submit_q1` ✅
- `match` (answer = 2) → `http_submit_q1` ✅
- `match` (answer = 3) → `http_submit_q1` ✅

**http_submit_q1** doit avoir 2 transitions :
- `success` → `question2` ✅
- `fail` → `question2` ✅

Répéter la vérification pour Q2, Q3, Q4.

---

## 🧪 Tester le flow

### Test 1 : Validation du flow

1. Dans Studio, cliquer sur **Validate** (en haut à droite)
2. S'assurer qu'il n'y a **aucune erreur**
3. Les warnings sont acceptables

### Test 2 : Test avec le widget test de Twilio

1. Cliquer sur **Test** (en haut à droite)
2. Dans le simulateur, envoyer des messages :
   - Envoyer n'importe quel texte pour déclencher le flow
   - Répondre "Oui"
   - Répondre "3" (Q1)
   - Répondre "2" (Q2)
   - Répondre "1" (Q3)
   - Répondre "1" (Q4)

3. **Vérifier dans les logs** :
   - Cliquer sur chaque widget HTTP
   - Vérifier que la requête est envoyée
   - Vérifier le code de réponse (devrait être 200 si l'API fonctionne)

### Test 3 : Test avec un vrai numéro WhatsApp

1. Envoyer un message au numéro WhatsApp configuré
2. Suivre le flow complet
3. **Vérifier dans le dashboard Laravel** :
   - Aller sur https://quiz-suzuki-can.ywcdigital.com/login
   - Aller dans le concours
   - Vérifier que les réponses apparaissent

---

## 🔧 Dépannage

### Erreur : "Variable not found: flow.variables.contest_id"

**Solution** : Les variables ne sont pas configurées.
1. Flow Configuration → Variables
2. Ajouter manuellement :
   - `contest_id` = `1`
   - `api_base_url` = `https://quiz-suzuki-can.ywcdigital.com/api/game`

### Erreur : "Invalid JSON"

**Solution** : Le fichier JSON est corrompu ou mal copié.
1. Télécharger à nouveau `twilio-flow-updated.json`
2. Vérifier qu'il n'y a pas de caractères spéciaux ajoutés
3. Utiliser un validateur JSON : https://jsonlint.com/

### Les requêtes HTTP échouent (status fail)

**Solutions** :
1. Vérifier que l'API Laravel est accessible : `curl https://quiz-suzuki-can.ywcdigital.com/api/ping`
2. Vérifier les logs Twilio : Console → Monitor → Logs → Studio
3. Vérifier que l'URL est en HTTPS (pas HTTP)
4. Vérifier que le serveur répond en moins de 10 secondes

### Les réponses ne s'enregistrent pas

**Solutions** :
1. Vérifier les logs Laravel : `tail -f storage/logs/laravel.log`
2. Tester l'endpoint manuellement :
   ```bash
   curl -X POST https://quiz-suzuki-can.ywcdigital.com/api/game/submit-answer \
     -H "Content-Type: application/json" \
     -d '{
       "contest_id": 1,
       "whatsapp_number": "+2250701234567",
       "question_id": 1,
       "answer": 3,
       "conversation_sid": "CHtest123"
     }'
   ```
3. Vérifier que le contest_id = 1 existe dans la base de données
4. Vérifier que les questions sont actives (`is_active = true`)

---

## 📊 Structure du nouveau flow

```
États (states) :
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

TOTAL : 32 widgets (28 originaux + 4 nouveaux)

Nouveaux widgets ajoutés :
├── http_submit_q1 (widget #8)
├── http_submit_q2 (widget #11)
├── http_submit_q3 (widget #14)
└── http_submit_q4 (widget #17)

Flux modifié :
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

welcome_message
  ↓
ready_question → split_ready_question
  ↓
question1 → split_question1
  ↓
http_submit_q1 ← NOUVEAU (appel API)
  ↓
question2 → split_question2
  ↓
http_submit_q2 ← NOUVEAU (appel API)
  ↓
question3 → split_question3
  ↓
http_submit_q3 ← NOUVEAU (appel API)
  ↓
question4 → split_question4
  ↓
http_submit_q4 ← NOUVEAU (appel API)
  ↓
final_message
```

---

## ⚙️ Modifications manuelles possibles

Si vous voulez modifier après l'importation :

### Changer l'URL de l'API

1. Flow Configuration → Variables
2. Modifier `api_base_url`
3. Publier

### Changer le contest_id

1. Flow Configuration → Variables
2. Modifier `contest_id`
3. Publier

### Ajouter un timeout plus long

1. Cliquer sur un widget HTTP (ex: `http_submit_q1`)
2. Modifier `Timeout` (en millisecondes)
3. Recommandé : 10000-15000 ms (10-15 secondes)
4. Publier

### Afficher le score dans le message final

Voir le document `FLOW_INTEGRATION_GUIDE.md` section "Afficher le score".

---

## 📋 Checklist finale

Après l'importation, vérifier :

- [ ] Flow importé sans erreurs
- [ ] 32 widgets visibles (28 + 4 nouveaux)
- [ ] Variables configurées (`contest_id`, `api_base_url`)
- [ ] Widgets HTTP bien configurés (URL, body, timeout)
- [ ] Transitions correctes (split → HTTP → question)
- [ ] Flow validé (bouton Validate)
- [ ] Flow publié
- [ ] Test avec le simulateur réussi
- [ ] Test avec un vrai numéro WhatsApp réussi
- [ ] Réponses apparaissent dans le dashboard Laravel
- [ ] Logs Twilio ne montrent pas d'erreurs

---

## 🎯 Résumé des changements

| Élément | Avant | Après |
|---------|-------|-------|
| Nombre de widgets | 28 | 32 |
| Appels API | 0 | 4 (après chaque question) |
| Variables du flow | 0 | 2 (contest_id, api_base_url) |
| Flux Q1 | split_question1 → question2 | split_question1 → http_submit_q1 → question2 |
| Flux Q2 | split_question2 → question3 | split_question2 → http_submit_q2 → question3 |
| Flux Q3 | split_question3 → question4 | split_question3 → http_submit_q3 → question4 |
| Flux Q4 | split_question4 → final_message | split_question4 → http_submit_q4 → final_message |

---

**✅ Une fois importé et testé, votre flow sera complètement intégré avec l'API Laravel !**

Les réponses seront enregistrées automatiquement dans la base de données et le classement sera mis à jour en temps réel.

---

**Date de création** : 2025-12-17
**Fichier** : `twilio-flow-updated.json`
**Compatible avec** : Twilio Studio v2
