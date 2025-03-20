FROM alpine:3.17

# Installer PostgreSQL client et bash
RUN apk add --no-cache postgresql-client bash

# Copier le script d'initialisation
COPY init-db.sh /init-db.sh
RUN chmod +x /init-db.sh

# Définir le point d'entrée
ENTRYPOINT ["/init-db.sh"]
