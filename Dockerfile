# Imagen base Ubuntu
FROM ubuntu:24.04


# Evitar prompts interactivos durante la instalación
ENV DEBIAN_FRONTEND=noninteractive


# Actualizar sistema y herramientas necesarias
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    unzip \
    lsb-release \
    ca-certificates \
    gnupg2

# Agregar repositorio de PHP 8.2 de Ondřej Surý
RUN add-apt-repository ppa:ondrej/php -y && apt-get update

# Instalar Apache y PHP 8.2 con extensiones comunes
RUN apt-get install -y \
    git \
    sudo \
    passwd \
    apache2 \
    libapache2-mod-php8.2 \
    php8.2 \
    php8.2-cli \
    php8.2-common \
    php8.2-pdo \
    php8.2-sqlite3 \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-opcache \
    php8.2-intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


RUN echo "ubuntu:ubuntu" | chpasswd

RUN echo "ubuntu ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers
# Activar módulos de Apache
RUN a2enmod php8.2 rewrite

# Install Node.js (latest version) and npm
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y curl \
    && curl -fsSL https://deb.nodesource.com/setup_current.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN apt-get update && apt-get install -y curl unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*
# Instalación de Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Copiar configuración personalizada de Apache
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Definir directorio de trabajo
WORKDIR /workspaces/sitewebinmobiliaria

# Asignar permisos adecuados (ajustar según necesidades de seguridad)
RUN chown -R www-data:www-data /workspaces/sitewebinmobiliaria



