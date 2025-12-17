# ⚡ Quick Start - Quiz Suzuki CAN

## 🚨 PROBLÈME CRITIQUE IDENTIFIÉ

**Le flow Twilio actuel N'APPELLE PAS l'API Laravel !**

Les réponses des participants ne sont pas enregistrées dans la base de données.

---

## ✅ SOLUTION RAPIDE (3 étapes)

### 1️⃣ Configurer l'URL de production

**Fichier** : `.env`
```bash
# Ligne 5 - Changer :
APP_URL=https://quiz-suzuki-can.ywcdigital.com
```

### 2️⃣ Modifier le flow Twilio

**Dans Twilio Studio** :

1. **Ajouter les variables du flow** :
   ```json
   {
     "contest_id": 1,
     "api_base_url": "https://quiz-suzuki-can.ywcdigital.com/api/game"
   }
   ```

2. **Ajouter 4 widgets HTTP** après chaque question :
   - Après Q1 : `http_submit_q1`
   - Après Q2 : `http_submit_q2`
   - Après Q3 : `http_submit_q3`
   - Après Q4 : `http_submit_q4`

   **Exemple pour Q1** :
   ```
   Type: Make HTTP Request
   Method: POST
   URL: {{flow.variables.api_base_url}}/submit-answer
   Body (JSON):
   {
     "contest_id": {{flow.variables.contest_id}},
     "whatsapp_number": "{{contact.channel.address}}",
     "question_id": 1,
     "answer": {{widgets.question1.inbound.Body}},
     "conversation_sid": "{{trigger.message.ConversationSid}}"
   }
   ```

3. **Publier le flow**

### 3️⃣ Déployer l'application

```bash
# Sur le serveur de production
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build
php artisan migrate --force
php artisan db:seed --class=DemoDataSeeder
php artisan config:cache
```

---

## 📚 Documentation complète

| Fichier | Description |
|---------|-------------|
| `DEPLOYMENT_README.md` | Guide de déploiement complet (étape par étape) |
| `FLOW_INTEGRATION_GUIDE.md` | Intégration détaillée du flow Twilio |
| `MODIFICATIONS_FLOW.md` | Modifications exactes à faire au flow |
| `ARCHITECTURE.md` | Schéma de l'architecture système |
| `.env.production.example` | Configuration pour la production |
| `deploy.sh` | Script de déploiement automatisé |

---

## 🧪 Test rapide

### Tester l'API
```bash
curl https://quiz-suzuki-can.ywcdigital.com/api/ping
```

### Tester une soumission
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

**Réponse attendue** :
```json
{
  "success": true,
  "message": "Bonne réponse !",
  "data": {
    "is_correct": true,
    "points_earned": 1,
    "total_score": 1
  }
}
```

---

## ✅ Checklist avant production

- [ ] `.env` configuré avec `APP_URL` correcte
- [ ] Base de données migrée (`php artisan migrate`)
- [ ] Seeder exécuté (`php artisan db:seed`)
- [ ] Variables du flow Twilio ajoutées
- [ ] 4 widgets HTTP ajoutés au flow
- [ ] Flow publié
- [ ] Test complet du bot WhatsApp
- [ ] Vérification dans le dashboard Laravel

---

## 🆘 Aide rapide

**Les réponses ne s'enregistrent pas ?**
1. Vérifier les logs Laravel : `tail -f storage/logs/laravel.log`
2. Vérifier les logs Twilio : Console → Monitor → Logs → Studio
3. Tester l'API manuellement avec curl

**Erreur "Contest not found" ?**
```bash
php artisan db:seed --class=DemoDataSeeder
```

**Accès refusé à la base de données ?**
Vérifier les identifiants dans `.env` (lignes 23-28)

---

## 📞 Endpoints API

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/ping` | Test de l'API |
| POST | `/api/game/submit-answer` | Soumettre une réponse |
| GET | `/api/game/questions/{id}` | Liste des questions |
| GET | `/api/game/leaderboard/{id}` | Classement |
| GET | `/api/game/participant-status` | Statut du participant |

---

## 🎯 Structure du flow modifié

```
Participant répond "3"
    ↓
split_question1 (valide 1/2/3)
    ↓
http_submit_q1 → POST /api/game/submit-answer
    ↓
question2
    ↓
split_question2
    ↓
http_submit_q2 → POST /api/game/submit-answer
    ↓
question3
    ↓
split_question3
    ↓
http_submit_q3 → POST /api/game/submit-answer
    ↓
question4
    ↓
split_question4
    ↓
http_submit_q4 → POST /api/game/submit-answer
    ↓
final_message
```

---

## 📊 Questions dans la base de données

| ID | Question | Réponse correcte |
|----|----------|------------------|
| 1 | La CAN existe depuis combien de temps ? | 3 (Plus de 60 ans) |
| 2 | Combien de fois la CI a gagné la CAN ? | 2 (2 fois) |
| 3 | Prévois-tu d'acheter une voiture ? | Marketing (pas de bonne réponse) |
| 4 | Tu utilises une voiture surtout pour… | Marketing (pas de bonne réponse) |

**Score maximum** : 2 points (Q1 + Q2)
**Gagnants** : Top 10 par semaine avec min 2 points

---

## 🔥 Commandes essentielles

```bash
# Déploiement complet
./deploy.sh

# Lister les concours
php artisan contest:manage list

# Voir un concours
php artisan contest:manage show 1

# Sélectionner les gagnants
php artisan contest:manage winners 1

# Logs en temps réel
tail -f storage/logs/laravel.log

# Optimiser l'application
php artisan optimize

# Mode maintenance ON/OFF
php artisan down
php artisan up
```

---

**🚀 Prêt à déployer ? Suivez `DEPLOYMENT_README.md` pour les détails complets !**

---

**Dernière mise à jour** : 2025-12-17
