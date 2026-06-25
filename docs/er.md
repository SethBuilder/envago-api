# Envago v2 — ER Diagram

```mermaid
erDiagram
    users {
        int id PK
        string name
        string email
        string phone
        string password_hash
        timestamp created_at
    }

    providers {
        int id PK
        int user_id FK
        string business_name
        string pix_key
        timestamp verified_at
    }

    categories {
        int id PK
        string name
        string slug
        string icon_path
    }

    activities {
        int id PK
        int provider_id FK
        int category_id FK
        string name
        string slug
        text description
        decimal price
        int duration_minutes
        int capacity
        string city
        string state
        decimal lat
        decimal lng
        boolean is_active
    }

    schedules {
        int id PK
        int activity_id FK
        date date
        time time
        int seats_total
        int seats_booked
    }

    bookings {
        int id PK
        int user_id FK
        int schedule_id FK
        int seats
        decimal total_price
        enum status
        string payment_ref
        timestamp confirmed_at
        timestamp created_at
    }

    payouts {
        int id PK
        int provider_id FK
        int booking_id FK
        decimal amount
        decimal commission
        enum status
        timestamp paid_at
    }

    media {
        int id PK
        int activity_id FK
        string url
        enum type
        int order
    }

    reviews {
        int id PK
        int activity_id FK
        int user_id FK "nullable"
        string author_name
        string author_avatar_url
        int rating
        text body
        enum source
        string source_url "nullable"
        boolean is_approved
        timestamp scraped_at "nullable"
        timestamp created_at
    }

    users ||--o{ bookings : "faz"
    users ||--o| providers : "é"
    providers ||--o{ activities : "oferece"
    categories ||--o{ activities : "classifica"
    activities ||--o{ schedules : "tem"
    activities ||--o{ media : "tem"
    schedules ||--o{ bookings : "reservado via"
    bookings ||--o| payouts : "gera"
    providers ||--o{ payouts : "recebe"
    activities ||--o{ reviews : "tem"
    users ||--o{ reviews : "escreve"
```

## Notas de design

- `users` serve tanto turistas quanto operadores — se for operador, tem uma linha em `providers`
- `schedules` controla o contador de vagas — `seats_booked` incrementa a cada reserva
- `bookings` aponta para `schedule` (não direto para `activity`) para registrar data/horário exato
- `payouts` criado somente após operador confirmar (`bookings.status = confirmed`) — garante o fluxo PIX D+1
- `commission` salva na linha do payout para preservar histórico mesmo se a taxa mudar
- `reviews.user_id` é nullable — reviews raspadas do Apify não têm conta na plataforma; reviews de usuários sim
- `reviews.source` enum: `scraped` | `user_submitted`
- `reviews.is_approved` permite moderar antes de publicar (especialmente as raspadas)
- `reviews.source_url` guarda o link original (Google Maps, TripAdvisor etc.) para rastreabilidade
