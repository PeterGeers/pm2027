# PM 2027 - Platform & Tooling

## Overzicht

Modernisering van het FH-DCE Presidents' Meeting registratiesysteem van PHP/jQuery naar een moderne stack met Next.js, AWS Cognito en multi-language support.

## Technologie Stack

| Laag                | Technologie                                  | Doel                                               |
| ------------------- | -------------------------------------------- | -------------------------------------------------- |
| Frontend + API      | Next.js 14+ (App Router) + TypeScript        | Full-stack framework, SSR, API routes              |
| Styling             | Tailwind CSS 3                               | Utility-first CSS, responsive design               |
| Authenticatie       | AWS Cognito + NextAuth.js (Cognito provider) | Registratie, login, wachtwoord reset, MFA, sessies |
| Database            | PostgreSQL (Neon)                            | Serverless Postgres, relationele data, boekingen   |
| ORM                 | Prisma                                       | Type-safe database queries, migraties              |
| Internationalisatie | next-intl                                    | Multi-language UI (EN, NL, DE, FR)                 |
| Email               | Amazon SES                                   | Transactionele emails (bevestigingen, reminders)   |
| Excel Export        | SheetJS (xlsx)                               | Spreadsheet downloads voor admin                   |
| Hosting             | Vercel                                       | Zero-config deployment, SSL, preview deploys       |
| Infra as Code       | AWS CDK (optioneel)                          | Cognito User Pool, SES provisioning                |

## Authenticatie — AWS Cognito

### Wat het vervangt

- `register.php` → Cognito Sign Up + email verificatie
- `activate.php` → Cognito email confirmation flow
- `forgotpass.php` → Cognito Forgot Password flow
- `index.php` login → Cognito Hosted UI of custom formulier via NextAuth
- PHP session management → JWT tokens via Cognito

### Configuratie

- User Pool met email als verificatie-methode
- App Client voor de Next.js applicatie
- Cognito Group "admin" voor administrator-rol
- Custom attributes: `club_id` (koppeling naar club)
- Password policy: minimaal 8 tekens (aansluitend bij huidige app)

### NextAuth.js integratie

```typescript
// auth.ts
import CognitoProvider from "next-auth/providers/cognito";

export const authOptions = {
  providers: [
    CognitoProvider({
      clientId: process.env.COGNITO_CLIENT_ID,
      clientSecret: process.env.COGNITO_CLIENT_SECRET,
      issuer: process.env.COGNITO_ISSUER,
    }),
  ],
  callbacks: {
    async session({ session, token }) {
      // Voeg Cognito groups toe aan session
      session.user.groups = token.groups;
      session.user.clubId = token.clubId;
      return session;
    },
  },
};
```

### Admin-rol

- Cognito Group "admin" vervangt het `admin` veld in de `users` tabel
- JWT token bevat automatisch groep-claims
- Next.js middleware checkt `session.user.groups.includes("admin")`
- Eerste admin wordt handmatig aan de groep toegevoegd via AWS Console

### Kosten

- Free tier: 50.000 MAU — ruim voldoende voor ~150 gebruikers
- SES: $0.10 per 1000 emails — verwaarloosbaar

## Internationalisatie (i18n)

### Aanpak

- `next-intl` library met JSON vertaalbestanden per taal
- URL-based routing: `/en/booking`, `/nl/booking`, `/de/booking`
- Default taal: Engels
- Ondersteunde talen: EN, NL, DE, FR
- Taalswitch dropdown in navbar, keuze opgeslagen in cookie

### Bestandsstructuur

```
messages/
├── en.json    # Engels (default)
├── nl.json    # Nederlands
├── de.json    # Duits
└── fr.json    # Frans
```

### Wat vertaald wordt

- Alle UI labels en knoppen
- Formulier validatieberichten
- Instructieteksten (booking + admin)
- Email templates
- Foutmeldingen
- Datum- en valutaformaten (via `Intl` API)

## Database — Neon (Serverless PostgreSQL) + Prisma

### Waarom Neon

- Serverless: schaalt naar 0 bij inactiviteit, betaal alleen voor gebruik
- Free tier ruim voldoende: 100 CU-hrs/maand, 0.5 GB storage, tot 2 CU (8 GB RAM)
- Database branching: per Vercel preview deploy een eigen database branch
- Autoscaling en read replicas beschikbaar
- Geen VPC/security groups configuratie nodig

### Vereenvoudiging t.o.v. huidige schema

- Stage/submitted patroon → `status` enum op booking (`DRAFT`, `SUBMITTED`, `LOCKED`)
- Geen dubbele tabellen meer (booking + booking_submitted)
- `users` tabel → alleen applicatie-data (club_id, preferences), auth via Cognito
- Audit trail via `created_at`, `updated_at`, `submitted_at` timestamps

### Prisma schema (kern)

```prisma
model Booking {
  id            Int       @id @default(autoincrement())
  cognitoUserId String
  clubId        Int
  status        BookingStatus @default(DRAFT)
  submittedCount Int      @default(0)
  comments      String?
  createdAt     DateTime  @default(now())
  updatedAt     DateTime  @updatedAt
  submittedAt   DateTime?

  club          Club      @relation(fields: [clubId], references: [id])
  contact       Contact?
  delegates     Delegate[]
  guests        Guest[]
  rooms         Room[]
  travels       Travel[]
  payments      Payment[]
  extras        Extra[]
}

enum BookingStatus {
  DRAFT
  SUBMITTED
  LOCKED
}
```

## Email — Amazon SES

### Wat het vervangt

- PHPMailer → SES SDK of `@aws-sdk/client-ses`
- Handmatige SMTP configuratie → SES verified domain

### Email types

- Booking bevestiging (bij submit)
- Betaling ontvangen
- Herinnering: registratie afmaken
- Herinnering: boeking submitten
- Herinnering: betaling
- Booking locked notificatie

### Templates

- React Email of MJML voor responsive HTML emails
- Per taal een template variant

## Hosting & Deployment — Vercel

- Zero-config deployment vanuit Git
- Automatische preview deploys per branch/PR
- Edge functions voor snelle response
- Gratis tier ruim voldoende
- Custom domain: pm2027.hdc-centrum.nl

## Development Tooling

| Tool              | Doel                                 |
| ----------------- | ------------------------------------ |
| pnpm              | Package manager (sneller dan npm)    |
| ESLint + Prettier | Code kwaliteit en formatting         |
| Vitest            | Unit tests                           |
| Playwright        | E2E tests                            |
| GitHub Actions    | CI/CD pipeline                       |
| Prisma Studio     | Database browser tijdens development |
| AWS CLI           | Cognito en SES beheer                |

## Projectstructuur

```
pm2027/
├── src/
│   ├── app/
│   │   ├── [locale]/
│   │   │   ├── page.tsx              # Login
│   │   │   ├── register/page.tsx
│   │   │   ├── booking/page.tsx
│   │   │   └── admin/
│   │   │       ├── page.tsx
│   │   │       ├── users/page.tsx
│   │   │       ├── bookings/page.tsx
│   │   │       ├── payments/page.tsx
│   │   │       └── ...
│   │   └── api/
│   │       ├── auth/[...nextauth]/route.ts
│   │       ├── booking/route.ts
│   │       ├── admin/route.ts
│   │       └── export/route.ts
│   ├── components/
│   │   ├── booking/
│   │   ├── admin/
│   │   └── ui/
│   ├── lib/
│   │   ├── prisma.ts
│   │   ├── email.ts
│   │   └── charges.ts
│   └── i18n/
│       └── request.ts
├── messages/
│   ├── en.json
│   ├── nl.json
│   ├── de.json
│   └── fr.json
├── prisma/
│   ├── schema.prisma
│   └── seed.ts
├── public/
│   ├── logos/
│   └── club_logos/
├── .env.local
├── next.config.ts
├── tailwind.config.ts
├── package.json
└── tsconfig.json
```

## Versiebeheer — GitHub + GitGuardian

### GitHub Repository

- Private repository (boekingsdata en club-informatie is niet publiek)
- Branch strategie: `main` (productie) + feature branches
- Pull Requests verplicht voor main
- Repository: `github.com/[org]/pm2027` (aan te maken)

### GitGuardian — Secret Detection

GitGuardian scant elke push en PR op gelekte secrets (API keys, wachtwoorden, tokens).

**Setup stappen:**

1. Maak een account op [gitguardian.com](https://www.gitguardian.com) (gratis voor open source, betaald voor private repos — free trial beschikbaar)
2. Koppel de GitHub repository via het GitGuardian dashboard
3. Genereer een API key in GitGuardian → Settings → API
4. Voeg `GITGUARDIAN_API_KEY` toe als GitHub repository secret:
   - GitHub repo → Settings → Secrets and variables → Actions → New repository secret
5. De GitHub Actions workflow (`.github/workflows/gitguardian.yml`) is al aangemaakt

**Wat het scant:**

- AWS access keys en secret keys
- Database credentials
- Cognito client secrets
- API tokens
- Private keys
- Wachtwoorden in code

**Lokaal scannen (optioneel):**

```bash
pip install ggshield
ggshield auth login
ggshield secret scan repo .
```

### Pre-commit hook (optioneel, extra beveiliging)

Voorkomt dat secrets überhaupt gecommit worden:

```bash
pip install ggshield
ggshield install -m local
```

Dit installeert een git pre-commit hook die lokaal scant vóór elke commit.

### .gitignore

De volgende bestanden worden uitgesloten van versiebeheer:

- `.env`, `.env.local`, `.env.*.local` — environment variabelen
- `**/pmdb_secret.php` — huidige database credentials
- `node_modules/` — dependencies
- `.next/` — build output

### Environment Variabelen (niet in git)

```
# .env.local (voorbeeld, NIET committen)
COGNITO_CLIENT_ID=xxx
COGNITO_CLIENT_SECRET=xxx
COGNITO_ISSUER=https://cognito-idp.eu-west-1.amazonaws.com/eu-west-1_xxx
NEXTAUTH_SECRET=xxx
DATABASE_URL=postgresql://user:pass@ep-xxx.eu-central-1.aws.neon.tech/pm2027?sslmode=require
SES_REGION=eu-west-1
```

## Migratie-aandachtspunten

1. **Één account per club** — business rule blijft in applicatie, niet in Cognito
2. **Club lijst** — `fhdce_clubs` tabel migreren, nieuwe clubs toevoegen
3. **Prijzen en configuratie** — van `config.php` constanten naar database of environment variabelen
4. **Bestaande data** — eenmalige migratie van MariaDB naar PostgreSQL indien nodig
5. **Emails** — SES domain verificatie voor pm2027.hdc-centrum.nl
6. **Club logo's** — migreren naar `/public/club_logos/`

git remote add origin https://github.com/PeterGeers/pm2027.git
git branch -M main
git push -u origin main
