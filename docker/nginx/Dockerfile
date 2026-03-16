FROM node:22-alpine AS node-builder
WORKDIR /app

ARG VITE_APP_NAME=GoldenConnect
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=8080
ARG VITE_REVERB_SCHEME=https

ENV VITE_APP_NAME=${VITE_APP_NAME} \
    VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}

COPY package.json package-lock.json* ./
RUN npm ci
COPY resources/ resources/
COPY vite.config.js tailwind.config.js* postcss.config.js* ./
RUN npm run build

FROM nginx:1.27-alpine
COPY --from=node-builder /app/public/build /app/public/build
