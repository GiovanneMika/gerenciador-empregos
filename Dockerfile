FROM php:8.2-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Limpar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Obter Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário do sistema para rodar comandos Composer e Artisan
RUN useradd -G www-data,root -u 1000 -d /home/laraveluser laraveluser
RUN mkdir -p /home/laraveluser/.composer && \
    chown -R laraveluser:laraveluser /home/laraveluser

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar arquivos do projeto
COPY . /var/www

# Copiar arquivo .env.example para .env se não existir
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Ajustar permissões
RUN chown -R laraveluser:laraveluser /var/www
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/bootstrap/cache

# Criar arquivo database.sqlite se não existir
RUN touch /var/www/database/database.sqlite
RUN chown laraveluser:laraveluser /var/www/database/database.sqlite
RUN chmod 664 /var/www/database/database.sqlite

# Mudar para o usuário laraveluser
USER laraveluser

# Instalar dependências do Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Gerar chave da aplicação
RUN php artisan key:generate

# Expor porta 8000
EXPOSE 8000

# Comando para iniciar o servidor
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
