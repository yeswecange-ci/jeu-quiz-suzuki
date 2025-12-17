#!/bin/bash

###############################################################################
# Script de déploiement - Quiz Suzuki CAN
# URL de production : https://quiz-suzuki-can.ywcdigital.com
###############################################################################

set -e  # Arrêter en cas d'erreur

echo "🚀 Déploiement de Quiz Game API - Suzuki CAN"
echo "================================================"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Vérifier que nous sommes sur le serveur de production
if [ ! -f ".env" ]; then
    echo -e "${RED}❌ Fichier .env non trouvé !${NC}"
    echo "Voulez-vous créer un fichier .env à partir de .env.production.example ? (y/n)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        cp .env.production.example .env
        echo -e "${YELLOW}⚠️  Veuillez configurer le fichier .env avec vos identifiants${NC}"
        exit 1
    else
        exit 1
    fi
fi

# 2. Mettre l'application en mode maintenance
echo -e "\n${YELLOW}🔧 Activation du mode maintenance...${NC}"
php artisan down --render="errors::503" --retry=60

# 3. Pull du code depuis Git
echo -e "\n${GREEN}📥 Récupération du code...${NC}"
git pull origin main

# 4. Installer les dépendances PHP
echo -e "\n${GREEN}📦 Installation des dépendances PHP...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Installer les dépendances Node.js
echo -e "\n${GREEN}📦 Installation des dépendances Node.js...${NC}"
npm ci --production

# 6. Build des assets
echo -e "\n${GREEN}🔨 Build des assets frontend...${NC}"
npm run build

# 7. Optimisations Laravel
echo -e "\n${GREEN}⚡ Optimisation de l'application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Migrations de la base de données
echo -e "\n${YELLOW}🗄️  Migration de la base de données...${NC}"
php artisan migrate --force

# 9. Nettoyer les caches
echo -e "\n${GREEN}🧹 Nettoyage des caches...${NC}"
php artisan cache:clear
php artisan view:clear

# 10. Créer les liens symboliques si nécessaire
if [ ! -L "public/storage" ]; then
    echo -e "\n${GREEN}🔗 Création du lien symbolique pour le storage...${NC}"
    php artisan storage:link
fi

# 11. Définir les permissions
echo -e "\n${GREEN}🔐 Configuration des permissions...${NC}"
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 12. Redémarrer le service queue (si utilisé)
if systemctl is-active --quiet laravel-queue; then
    echo -e "\n${GREEN}🔄 Redémarrage du service queue...${NC}"
    sudo systemctl restart laravel-queue
fi

# 13. Désactiver le mode maintenance
echo -e "\n${GREEN}✅ Désactivation du mode maintenance...${NC}"
php artisan up

echo -e "\n${GREEN}✨ Déploiement terminé avec succès !${NC}"
echo "================================================"
echo -e "${GREEN}🌐 Application disponible sur :${NC}"
echo -e "   ${YELLOW}https://quiz-suzuki-can.ywcdigital.com${NC}"
echo ""
echo -e "${YELLOW}📋 Prochaines étapes :${NC}"
echo "   1. Vérifier que l'API répond : curl https://quiz-suzuki-can.ywcdigital.com/api/ping"
echo "   2. Se connecter au dashboard : https://quiz-suzuki-can.ywcdigital.com/login"
echo "   3. Configurer le flow Twilio avec les widgets HTTP"
echo "   4. Tester le bot WhatsApp"
echo ""
