FROM node:20-alpine
WORKDIR /app

# Install production deps first for better layering
COPY package*.json ./
RUN npm ci --omit=dev

# Copy application code and dataset
COPY server.js ./
COPY data ./data

EXPOSE 3000
CMD ["node", "server.js"]
