FROM node:18-alpine

# Installer les dépendances MySQL
RUN apk add --no-cache mysql-client

# Créer le répertoire de l'application
WORKDIR /app

# Copier les fichiers de configuration
COPY package*.json ./
COPY nuxt.config.ts ./

# Installer les dépendances
RUN npm install
RUN npm install mysql2 --save

# Copier le reste des fichiers
COPY . .

# Définir les variables d'environnement
ENV NUXT_HOST=0.0.0.0
ENV NUXT_PORT=3000

# Exposer le port
EXPOSE 3000

# Commande de démarrage
CMD ["npm", "run", "dev"]
