# استخدم صورة PHP الرسمية مع Apache
FROM php:8.2-apache

# إعدادات النظام الأساسية + تثبيت الامتدادات اللازمة
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql mbstring exif pcntl bcmath opcache

# تمكين Apache Rewrite Module (مطلوب لـ Laravel routes)
RUN a2enmod rewrite

# نسخ ملفات المشروع إلى المجلد الافتراضي لـ Apache
COPY . /var/www/html

# تعيين مجلد العمل
WORKDIR /var/www/html

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# تثبيت التبعيات بدون حزم التطوير
RUN composer install --no-dev --optimize-autoloader

# إعطاء صلاحيات التخزين والتخزين المؤقت
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# تفعيل .htaccess (ضروري لتوجيه Laravel)
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# المنفذ الذي يستمع عليه التطبيق (Railway يقرأ من متغير PORT)
EXPOSE 80

# أمر التشغيل الافتراضي
CMD ["apache2-foreground"]
