FROM node:18-alpine

WORKDIR /app

# Copy package files for dependency installation
COPY package*.json ./

# Install all dependencies (including dev dependencies)
RUN npm install

# Expose port
EXPOSE 3000

# Start the application in development mode
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
