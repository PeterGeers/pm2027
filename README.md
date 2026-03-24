# PM2027 — FH-DCE Presidents Meeting Registration System

Registration and booking platform for the FH-DCE Presidents Meeting 2027, hosted by H-DC 't Centrum in Vianen, Netherlands.

## What it does

- Club account registration and authentication
- Booking management (delegates, guests, rooms, travel, t-shirts, payments)
- Admin panel for managing bookings, payments, exports, and reminders
- Multi-language support (EN, NL, DE, FR)

## Tech Stack

| Layer          | Technology                            |
| -------------- | ------------------------------------- |
| Frontend + API | Next.js 14+ (App Router) + TypeScript |
| Styling        | Tailwind CSS                          |
| Auth           | AWS Cognito + NextAuth.js             |
| Database       | PostgreSQL (Neon)                     |
| ORM            | Prisma                                |
| i18n           | next-intl                             |
| Email          | Amazon SES                            |
| Hosting        | Vercel                                |

## Getting Started

```bash
pnpm install
cp .env.example .env.local  # fill in your credentials
pnpm prisma generate
pnpm dev
```

## Environment Variables

See `.env.example` for required variables (Cognito, Neon database URL, SES, NextAuth secret).

## License

Private — FH-DCE / H-DC 't Centrum
