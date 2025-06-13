FROM node:18-alpine

WORKDIR /app

# Package dosyalarını kopyala
COPY package*.json ./

# Dependencies yükle
RUN npm install

# Uygulamayı kopyala
COPY . .

# Port aç
EXPOSE 3000

# Development server başlat
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "3000"]