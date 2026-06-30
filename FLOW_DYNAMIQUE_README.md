# 🎮 Flow dynamique Quiz Suzuki CAN

Le bot WhatsApp ne contient **plus aucune question en dur**. Il consomme les
questions du **concours actif** directement depuis l'application Laravel.
Ajouter, modifier, réordonner ou supprimer une question dans le dashboard se
reflète **immédiatement** dans le bot, sans toucher au flow Twilio.

---

## 🧠 Principe

```
Trigger → welcome → ready (Oui/Non)
   └─ oui → fetch_next_question ──┐
                                  │  (boucle)
   ┌──────────────────────────────┘
   ▼
fetch_next_question  POST /api/game/next-question
   ├─ completed = true  → final_message   (fin / "déjà terminé" / "aucun concours")
   └─ completed = false → ask_question  (texte + options fournis par l'API)
                              ▼
                          submit_answer  POST /api/game/submit-answer
                              ├─ accepted = true  → fetch_next_question (question suivante)
                              ├─ accepted = false → invalid_answer → ask_question (on re-pose)
                              └─ échec HTTP       → fetch_next_question (la même question revient)
```

### Pourquoi c'est robuste
- **Réponses figées** : une réponse enregistrée n'est jamais écrasée.
- **Auto-réparation** : si une soumission échoue, la question n'est pas
  enregistrée → au tour suivant `next-question` la **re-sert** → aucune perte,
  aucun doublon. Plus besoin des widgets `http_retry` (et de leurs `question_id`
  en dur qui étaient buggés).
- **Concours auto-détecté** : l'API choisit seule le concours `active`. Pour un
  futur concours, **rien à changer dans le flow**.
- **Reprise dynamique** : un joueur qui réécrit reçoit les nouvelles questions
  actives qu'il n'a pas encore traitées.

---

## 🔌 Endpoints API

### `POST /api/game/next-question`
Body : `{ "whatsapp_number", "profile_name?", "conversation_sid?", "contest_id?" }`

Réponse (question à poser) :
```json
{
  "success": true,
  "completed": false,
  "data": {
    "contest_id": 2,
    "question_id": 5,
    "current": 1,
    "total": 4,
    "options_count": 3,
    "message": "📝 *Question 1/4*\n\n<énoncé>\n\n 1️⃣ ...\n 2️⃣ ...\n 3️⃣ ...\n\n👉 Réponds avec le numéro (1 à 3)."
  }
}
```
Réponse (terminé / aucun concours) : `completed: true` + `data.message`.

### `POST /api/game/submit-answer`
Body : `{ "contest_id?", "whatsapp_number", "question_id", "answer", ... }`
- `answer` est **assaini** (`(int) trim`) et **validé dynamiquement** selon le
  nombre réel d'options de la question.
- Retourne `accepted: true|false`. `false` = hors plage → le flow re-pose.

---

## 🚀 Déploiement (jour J)

```bash
# 1. Dépendances + build
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 2. Migrations (ajoute les index de perf)
php artisan migrate --force

# 3. Caches
php artisan optimize
```

### Dans Twilio Studio
1. **Importer** `twilio-flow-dynamic.json` (Create new Flow → Import from JSON).
2. Vérifier la variable `api_base_url` dans le widget `Global_variables`
   (`https://quiz-suzuki-can.ywcdigital.com/api/game`).
3. **Publier** le flow et le rattacher au numéro WhatsApp.

> Les anciens fichiers `twilio-flow-*.json` (fixed/updated/corrected) sont
> **obsolètes** — ne pas les importer.

---

## ✅ Checklist avant d'ouvrir au public
- [ ] Le concours du jour a le statut **`active`** et des `start_date`/`end_date` cohérentes.
- [ ] Ses questions ont le bon **`order`**, et `is_active = true`.
- [ ] Le **`correct_answer`** de chaque question est correct (ex. Suzuki SUV → *Grand Vitara*).
- [ ] `curl https://quiz-suzuki-can.ywcdigital.com/api/ping` répond.
- [ ] Test bout-en-bout : envoyer "Oui" au bot → enchaîner les questions → "Félicitations".
- [ ] Réenvoyer un message après avoir fini → message "déjà terminé" (réponses figées).

---

## 🧪 Test rapide de l'API
```bash
# Prochaine question
curl -X POST https://quiz-suzuki-can.ywcdigital.com/api/game/next-question \
  -H "Content-Type: application/json" \
  -d '{"whatsapp_number":"whatsapp:+2250700000000","profile_name":"Test"}'

# Soumettre une réponse
curl -X POST https://quiz-suzuki-can.ywcdigital.com/api/game/submit-answer \
  -H "Content-Type: application/json" \
  -d '{"whatsapp_number":"whatsapp:+2250700000000","question_id":5,"answer":"1"}'
```

---

## 🔐 Recommandation sécurité (post-lancement)
L'API `/api/game/*` est publique et non authentifiée (`add_twilio_auth: false`).
Après le lancement, envisager un en-tête secret partagé (`X-Game-Token`) validé
côté Laravel et ajouté aux widgets HTTP, pour empêcher des soumissions externes.
