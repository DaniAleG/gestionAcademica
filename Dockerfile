FROM php:8.2-apache

# Librerías de desarrollo de PostgreSQL, necesarias para compilar pdo_pgsql
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensión para conectarnos a PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Copiamos el proyecto al directorio que sirve Apache
COPY . /var/www/html/

# Render inyecta la variable PORT y espera que la app escuche ahí.
# Ajustamos Apache para usar ese puerto (por defecto 10000 en local).
ENV PORT=10000
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

# Sustituye ${PORT} por el valor real de la variable de entorno al arrancar
CMD sh -c "sed -i \"s/Listen .*/Listen ${PORT}/\" /etc/apache2/ports.conf && \
           sed -i \"s/<VirtualHost \\*:.*>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf && \
           apache2-foreground"
