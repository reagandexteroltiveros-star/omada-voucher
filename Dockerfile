# ------------------------
# Dockerfile
# ------------------------
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www/html

# Copy webhook script
COPY index.php /var/www/html/

# Expose port for HTTP (Omada webhook should point here)
EXPOSE 8080

# Start built-in PHP server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
