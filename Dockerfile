# استخدم صورة PHP الرسمية مع Apache
FROM php:8.2-apache

# نسخ ملفات المشروع
COPY . /var/www/html

# تثبيت المتطلبات الأساسية
RUN docker-php-ext-install pdo pdo_mysql

# تثبيت Composer
RUN apt-get update && apt-get install -y unzip git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# تثبيت تبعيات Laravel
RUN composer install --no-dev --optimize-autoloader

# إعطاء الصلاحيات للمجلدات المطلوبة
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# فتح المنفذ
EXPOSE 80

# تشغيل Apache
CMD ["apache2-foreground"]
