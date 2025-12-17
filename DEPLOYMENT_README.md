# 🚀 Guide de Déploiement - Quiz Suzuki CAN

## 📊 Résumé de l'analyse

### ✅ Ce qui est prêt
- ✅ Application Laravel 12 complètement fonctionnelle
- ✅ API RESTful avec tous les endpoints nécessaires
- ✅ Base de données avec migrations et seeders
- ✅ Dashboard d'administration
- ✅ Système de scoring et classement
- ✅ Questions alignées avec le flow Twilio

### ❌ Ce qui DOIT être fait avant la mise en production

1. **CRITIQUE** : Intégrer les appels HTTP dans le flow Twilio
2. **CRITIQUE** : Configurer l'URL de production dans `.env`
3. **IMPORTANT** : Exécuter les migrations et seeders sur le serveur
4. **IMPORTANT** : Tester l'intégration complète

---

## 📁 Documentation créée

J'ai créé les documents suivants pour vous aider :

1. **`FLOW_INTEGRATION_GUIDE.md`** - Guide complet d'intégration du flow Twilio
2. **`MODIFICATIONS_FLOW.md`** - Modifications détaillées à apporter au flow
3. **`.env.production.example`** - Configuration pour la production
4. **`deploy.sh`** - Script de déploiement automatisé

---

## 🎯 Plan de déploiement (Étape par étape)

### Phase 1 : Préparation du serveur (30 min)

#### 1.1 Transférer le code sur le serveur
```bash
# Sur votre machine locale
git push origin main

# Sur le serveur
cd /var/www/
git clone https://votre-repo.git quiz-suzuki-can
cd quiz-suzuki-can
```

#### 1.2 Configurer l'environnement
```bash
# Copier le fichier de configuration
cp .env.production.example .env

# Éditer avec vos identifiants
nano .env
```

**Paramètres à modifier dans `.env`** :
```env
APP_URL=https://quiz-suzuki-can.ywcdigital.com
DB_DATABASE=quiz_suzuki_can
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
MAIL_HOST=votre_serveur_mail
```

#### 1.3 Installer les dépendances
```bash
# Dépendances PHP
composer install --no-dev --optimize-autoloader

# Dépendances Node.js
npm ci --production

# Build des assets
npm run build
```

#### 1.4 Configurer Laravel
```bash
# Générer la clé d'application
php artisan key:generate

# Créer le lien symbolique pour le storage
php artisan storage:link

# Optimiser l'application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 1.5 Base de données
```bash
# Créer la base de données
mysql -u root -p
CREATE DATABASE quiz_suzuki_can CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Exécuter les migrations
php artisan migrate --force

# Charger les données de test
php artisan db:seed --class=DemoDataSeeder
```

#### 1.6 Créer un utilisateur admin
```bash
php artisan tinker
```

Puis dans tinker :
```php
$user = new App\Models\User();
$user->name = 'Admin Suzuki';
$user->email = 'admin@suzuki.ci';
$user->password = bcrypt('VotreMotDePasseSecurisé');
$user->email_verified_at = now();
$user->save();
```

#### 1.7 Permissions
```bash
chown -R www-data:www-data /var/www/quiz-suzuki-can
chmod -R 775 storage bootstrap/cache
```

---

### Phase 2 : Configuration Nginx/Apache (15 min)

#### Pour Nginx

Créer : `/etc/nginx/sites-available/quiz-suzuki-can`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name quiz-suzuki-can.ywcdigital.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name quiz-suzuki-can.ywcdigital.com;

    root /var/www/quiz-suzuki-can/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;

    # Logs
    access_log /var/log/nginx/quiz-suzuki-can-access.log;
    error_log /var/log/nginx/quiz-suzuki-can-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activer le site :
```bash
ln -s /etc/nginx/sites-available/quiz-suzuki-can /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

### Phase 3 : Configuration SSL (10 min)

```bash
# Installer Certbot
apt install certbot python3-certbot-nginx

# Obtenir un certificat SSL
certbot --nginx -d quiz-suzuki-can.ywcdigital.com
```

---

### Phase 4 : Tester l'application (15 min)

#### 4.1 Test de l'API
```bash
# Test ping
curl https://quiz-suzuki-can.ywcdigital.com/api/ping

# Devrait retourner :
# {"success":true,"message":"Quiz Game API is running","timestamp":"2025-12-17T..."}
```

#### 4.2 Test submit-answer
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

# Devrait retourner :
# {"success":true,"message":"Bonne réponse !","data":{...}}
```

#### 4.3 Test du dashboard
1. Ouvrir : `https://quiz-suzuki-can.ywcdigital.com/login`
2. Se connecter avec les identifiants créés
3. Vérifier que le concours apparaît
4. Vérifier les questions

---

### Phase 5 : Configuration du Flow Twilio (45 min)

**📖 Suivre le guide détaillé dans `FLOW_INTEGRATION_GUIDE.md`**

#### Résumé rapide :

1. **Ouvrir Twilio Studio**
   - Aller sur https://console.twilio.com/
   - Studio → Flows → Votre flow

2. **Configurer les variables**
   - Cliquer sur **Flow Configuration**
   - Ajouter :
     ```json
     {
       "contest_id": 1,
       "api_base_url": "https://quiz-suzuki-can.ywcdigital.com/api/game"
     }
     ```

3. **Ajouter 4 widgets HTTP** (voir `MODIFICATIONS_FLOW.md`)
   - `http_submit_q1` après la question 1
   - `http_submit_q2` après la question 2
   - `http_submit_q3` après la question 3
   - `http_submit_q4` après la question 4

4. **Publier le flow**
   - Cliquer sur **Publish**
   - Confirmer

---

### Phase 6 : Test complet (30 min)

#### 6.1 Test du bot WhatsApp
1. Envoyer un message au numéro WhatsApp configuré
2. Répondre "Oui" pour commencer
3. Répondre aux 4 questions (1, 2, 3, 1 par exemple)
4. Vérifier le message final

#### 6.2 Vérifier dans le dashboard
1. Aller sur `https://quiz-suzuki-can.ywcdigital.com/dashboard`
2. Cliquer sur le concours "Scan & Gagne"
3. Vérifier que :
   - ✅ Le participant apparaît
   - ✅ Les 4 réponses sont enregistrées
   - ✅ Le score est calculé (devrait être 2 avec les réponses ci-dessus)

#### 6.3 Tester le classement
```bash
curl "https://quiz-suzuki-can.ywcdigital.com/api/game/leaderboard/1?limit=10"
```

Devrait retourner le participant avec son score.

---

## 🔧 Configuration du Queue Worker (Optionnel mais recommandé)

Si vous utilisez des jobs en arrière-plan :

### Créer un service systemd

Créer : `/etc/systemd/system/laravel-queue.service`

```ini
[Unit]
Description=Laravel Queue Worker - Quiz Suzuki CAN
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/quiz-suzuki-can/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

Activer :
```bash
systemctl enable laravel-queue
systemctl start laravel-queue
systemctl status laravel-queue
```

---

## 📊 Monitoring et Logs

### Logs Laravel
```bash
tail -f /var/www/quiz-suzuki-can/storage/logs/laravel.log
```

### Logs Nginx
```bash
tail -f /var/log/nginx/quiz-suzuki-can-error.log
tail -f /var/log/nginx/quiz-suzuki-can-access.log
```

### Logs Twilio
- Aller sur https://console.twilio.com/
- Monitor → Logs → Studio Flows

---

## 🔒 Sécurité

### Checklist de sécurité

- [ ] SSL/HTTPS activé
- [ ] `.env` non accessible publiquement (vérifier `.htaccess`/nginx config)
- [ ] Pare-feu configuré (UFW ou iptables)
- [ ] Mots de passe forts pour la base de données
- [ ] Limiter les tentatives de connexion (Laravel a ça par défaut)
- [ ] Configurer fail2ban (optionnel)
- [ ] Sauvegardes automatiques de la base de données

### Limiter l'accès à l'API (Optionnel)

Si vous voulez restreindre l'accès à l'API uniquement depuis Twilio :

**Dans `.env`** :
```env
TWILIO_API_TOKEN=votre_token_secret_genere
```

**Créer un middleware** :
```bash
php artisan make:middleware ValidateTwilioRequest
```

Voir `FLOW_INTEGRATION_GUIDE.md` pour les détails.

---

## 📅 Tâches de maintenance

### Quotidiennes
- Vérifier les logs d'erreurs
- Surveiller le nombre de participants

### Hebdomadaires
- Sélectionner et notifier les gagnants :
  ```bash
  php artisan contest:manage winners 1
  ```
- Sauvegarder la base de données :
  ```bash
  mysqldump -u user -p quiz_suzuki_can > backup_$(date +%Y%m%d).sql
  ```

### Mensuelles
- Mettre à jour les dépendances :
  ```bash
  composer update
  npm update
  ```
- Analyser les performances
- Nettoyer les vieux logs :
  ```bash
  php artisan log:clear
  ```

---

## 🆘 Dépannage

### Problème : "500 Internal Server Error"
**Solution** :
```bash
php artisan config:clear
php artisan cache:clear
chmod -R 775 storage bootstrap/cache
```

### Problème : "SQLSTATE[HY000] [1045] Access denied"
**Solution** : Vérifier les identifiants de base de données dans `.env`

### Problème : Le bot ne enregistre pas les réponses
**Solutions** :
1. Vérifier que les widgets HTTP sont bien ajoutés dans le flow
2. Tester l'endpoint manuellement avec curl
3. Vérifier les logs Twilio (Monitor → Logs)
4. Vérifier les logs Laravel

### Problème : "Contest not found"
**Solution** :
```bash
# Vérifier que le seeder a été exécuté
php artisan db:seed --class=DemoDataSeeder
```

---

## 📞 Ressources et Support

### Documentation
- Laravel : https://laravel.com/docs/12.x
- Twilio Studio : https://www.twilio.com/docs/studio
- API Documentation : Voir le fichier `routes/api.php`

### Commandes utiles

```bash
# Voir l'état de l'application
php artisan about

# Lister les routes
php artisan route:list

# Lister les concours
php artisan contest:manage list

# Entrer en mode maintenance
php artisan down

# Sortir du mode maintenance
php artisan up

# Optimiser l'application
php artisan optimize

# Nettoyer les caches
php artisan optimize:clear
```

---

## ✅ Checklist finale avant la mise en production

- [ ] Code déployé sur le serveur
- [ ] `.env` configuré avec les bonnes valeurs
- [ ] Base de données créée et migrée
- [ ] Seeder exécuté (contest + questions créés)
- [ ] Utilisateur admin créé
- [ ] SSL/HTTPS configuré
- [ ] Nginx/Apache configuré et testé
- [ ] Permissions correctes (storage, bootstrap/cache)
- [ ] Test API `/api/ping` réussit
- [ ] Test API `/api/game/submit-answer` réussit
- [ ] Dashboard accessible et fonctionnel
- [ ] Flow Twilio modifié avec les widgets HTTP
- [ ] Variables du flow configurées (contest_id, api_base_url)
- [ ] Flow Twilio publié
- [ ] Test complet du bot WhatsApp effectué
- [ ] Vérification dans le dashboard que les réponses sont enregistrées
- [ ] Logs configurés et accessibles
- [ ] Sauvegardes automatiques configurées
- [ ] Documentation lue et comprise

---

## 🎉 Félicitations !

Une fois toutes ces étapes complétées, votre application Quiz Suzuki CAN sera opérationnelle !

**URL de production** : https://quiz-suzuki-can.ywcdigital.com

**Prochaines étapes** :
1. Communiquer le numéro WhatsApp aux participants
2. Surveiller les premiers participants
3. Sélectionner les gagnants chaque semaine
4. Profiter du succès de votre campagne ! 🎉🚗✨

---

**Date de création** : 2025-12-17
**Version** : 1.0
**Auteur** : Claude Code Assistant
