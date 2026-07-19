FROM php:8.3-cli-bullseye

# Instalar dependencias del sistema necesarias para Oracle
RUN apt-get update && apt-get install -y \
    unzip \
    libaio1 \
    wget

# Descargar e instalar Oracle Instant Client
WORKDIR /opt/oracle
RUN wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip \
    && wget https://download.oracle.com/otn_software/linux/instantclient/2112000/instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip \
    && unzip instantclient-basiclite-linux.x64-21.12.0.0.0dbru.zip \
    && unzip instantclient-sdk-linux.x64-21.12.0.0.0dbru.zip \
    && rm -f *.zip

# Configurar variables de entorno para Oracle
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient_21_12

# Instalar extensión OCI8 de PHP
RUN echo 'instantclient,/opt/oracle/instantclient_21_12' | pecl install oci8 \
    && docker-php-ext-enable oci8 \
    && docker-php-ext-install pdo

# Copiar Composer desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo de la aplicación
WORKDIR /app
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Activar configuración PHP
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]