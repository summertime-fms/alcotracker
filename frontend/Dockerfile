# ----- builder -----
FROM node:22.19.0 AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .

# Принимаем API_BASE_URL как build argument
# Это важно, так как Nuxt встраивает runtimeConfig в билд
ARG API_BASE_URL
ENV API_BASE_URL=${API_BASE_URL}

RUN npm run build

# ----- runner -----
FROM node:22.19.0 AS runner
WORKDIR /app
ENV NODE_ENV=production

# Принимаем API_BASE_URL как runtime переменную (для SSR)
ARG API_BASE_URL
ENV API_BASE_URL=${API_BASE_URL}

COPY --from=builder /app/.output ./.output
EXPOSE 3000
CMD ["node", ".output/server/index.mjs"]
