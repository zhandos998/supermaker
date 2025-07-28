#!/bin/bash
# Скрипт деплоя: пушим staging → обновляем продакшн

STAGING="/var/www/supermaker-staging"
PRODUCTION="/var/www/supermaker"

echo "🚀 Пушим изменения в GitHub..."
cd $STAGING
git add .
git commit -m "Deploy from staging $(date '+%Y-%m-%d %H:%M:%S')"
git push origin main

echo "📥 Обновляем продакшн..."
cd $PRODUCTION
git pull origin main

echo "⚙️ Обновляем зависимости и кэш..."
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache

echo "✅ Деплой завершён!"
