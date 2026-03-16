# Project: Golden Connect

## Overview

Golden Connect is a financial platform with a cyclic queue system (Monar), referral program, automated multi-messenger advertising, and digital business card networking. Participants purchase lots ($50–$1,000), receive working places in a shared cyclic queue, and earn +100% return as the queue cycles. The platform supports 46 languages with auto-translation and publishes across 9 messaging platforms.

## Core Features

### 1. Cyclic Monar (Queue System)
- Participants buy lots ($50–$1,000) creating 2–32 parallel working places ($10 each)
- Remainder converts to technical lots (virtual queue fillers)
- Each working place needs 2 incoming participants to complete a "circle"
- First incoming: income distribution (30%–90% depending on lot size)
- Second incoming: reinvestment (new place at end of queue)
- Target: +100% return, lot closes when achieved
- Single sequential Monar queue worker (critical shared state)

### 2. Three User Balances
- **Deposit Balance** — for lot purchases and subscription fees (no withdrawal)
- **Income Balance** — earnings from Monar circles (only balance for withdrawals)
- **Referral Balance** — referral earnings (transfer to Income Balance only)

### 3. Subscription System
- 0.5% of lot value every 7 days per active lot
- Non-payment freezes working places (preserves position)
- Subscription payments convert to technical lots

### 4. World Pool
- Monthly distribution among participants with lots $300+
- 8 pools with configurable percentages ($300: 15%, $400: 20%, $500: 40%, others: 5% each)
- Higher lot = access to more pools
- Auto-activates lot if payout is sufficient

### 5. Referral Program
- 5-level linear classic referral tree
- Separate from Monar queue (tree-based, not queue-based)
- Referral payouts from each circle's distribution
- Unallocated referral funds convert to technical lots

### 6. Credit Lot (Registration Bonus)
- $10 credit lot per new user (one-time)
- Separate credit queue
- Unlocks upon first real lot activation ($50+)
- Compensated (-$10) at first real lot closure

### 7. Withdrawal Conditions
- Must activate new lot ≥ 50% of completed lot's income within 48 hours
- Auto-reinvest: if no action in 48h, system activates lot for full income amount (max $1,000)

### 8. Automated Advertising
- Participants create ad texts in dashboard (max posts/week depends on lot tier)
- Auto-published across 9 messengers (Telegram, WhatsApp, Facebook Messenger, WeChat, Viber, Discord, Line, QQ, Signal)
- Auto-translation to 46 languages
- Moderation before publication
- Daily and weekly digest generation
- Images: 1–5 per post depending on lot tier

### 9. Golden Asset (Business Card Database)
- Digital business card with avatar, bio (5,000 chars), languages, social links (20 max), business projects (20 max)
- Auto-translated to all system languages
- Published on website (permanent page + QR code), Telegram (language channels), WhatsApp (English only)
- Two tiers: General (all participants) and VIP ($500+ lots, closed VIP chat)

### 10. Networking (Presentations)
- Participants earn from networking fund by giving presentations
- Score = lot coefficient (1.0–2.0) × number of presentations per month
- Monthly distribution proportional to scores
- Presentations recorded by authorized personnel

## Tech Stack

- **Language:** PHP 8.2+
- **Framework:** Laravel 12
- **Database:** MySQL
- **Frontend:** Vue.js (via Inertia.js)
- **Back-Front Bridge:** Inertia.js
- **Caching / Queues:** Redis
- **Queue Manager:** Laravel Horizon
- **WebSocket Server:** Laravel Reverb
- **Admin Panel:** Laravel Nova + `outl1ne/nova-settings`
- **Tree Structure:** `lazychaser/laravel-nestedset`
- **BEP20 / Web3:** `drlecks/simple-web3-php`
- **Money Handling:** `moneyphp/money`
- **Testing:** Pest
- **Frontend Routes:** `tightenco/ziggy`
- **Containerization:** Docker
- **Web Server / Reverse Proxy:** Nginx
- **App Server:** Laravel Octane (Swoole)
- **Authentication:** Laravel Fortify
- **Task Scheduler:** Laravel Scheduler
- **Notifications:** Laravel Notifications + `laravel-notification-channels/telegram`
- **Frontend Build:** Vite
- **DTO:** `spatie/laravel-data`
- **Media Files:** `spatie/laravel-medialibrary`
- **Roles & Permissions:** `spatie/laravel-permission`
- **Rate Limiting (Jobs):** `spatie/laravel-rate-limited-job-middleware`
- **Architecture:** DDD (Domain-Driven Design)
- **Network:** BEP20 (BSC) for deposits and withdrawals
- **Localization:** Russian + English (two languages)

## Architecture Notes

- DDD architecture with bounded contexts (domains)
- Lot purchase is lightweight HTTP (~50-100ms): validate, atomic balance deduction, dispatch async jobs
- Monar queue processed by **strictly 1 worker** (sequential, shared state)
- Atomic balance operations with DB transactions to prevent race conditions
- Separate Horizon queues: lots, monar (1 worker), referrals, notifications, billing, default
- Octane (Swoole) with connection pooling for high concurrency
- Configurable admin settings via `nova_get_setting()` helper
- All user-facing text must support Russian and English localization

## Architecture

See `.ai-factory/ARCHITECTURE.md` for detailed architecture guidelines.
Pattern: Domain-Driven Design (DDD)

## Non-Functional Requirements

- **Logging:** Configurable via LOG_LEVEL
- **Error handling:** Structured error responses via Inertia (client-side error feedback)
- **Security:** Atomic balance operations, rate limiting (Nginx + application), BEP20 wallet validation
- **Performance:** Target 2000+ RPS, async job processing, Redis caching
- **Scalability:** Min 20-50 new participants/day sustainable throughput
- **Localization:** Russian and English UI, 46 languages for advertising content
