# CRM System for Finishing & Fit-Out Company

نظام إدارة علاقات العملاء لشركات التشطيبات

## Features | المميزات

### Core Modules | الوحدات الأساسية
- **Lead Management** - إدارة العملاء المحتملين
- **Site Visit Management** - إدارة المعاينات
- **Quotation Management** - إدارة عروض الأسعار
- **Project Management** - إدارة المشاريع
- **Materials & Inventory** - المخزون والخامات
- **Contracts & Documents** - العقود والمستندات
- **Payments & Finance** - المدفوعات والمالية
- **Supplier & Contractor Management** - إدارة الموردين والمقاولين
- **Customer Portal** - بوابة العملاء
- **Task Management** - إدارة المهام
- **Reporting & Analytics** - التقارير والتحليلات
- **Communication Center** - مركز الاتصالات

### Bonus Modules | الوحدات الإضافية
- **HR & Attendance** - الموارد البشرية والحضور
- **Quality Control** - مراقبة الجودة
- **Maintenance Requests** - طلبات الصيانة
- **Marketing Dashboard** - لوحة التسويق

## Tech Stack | التقنيات المستخدمة

### Backend
- Laravel 10
- PHP 8.1+
- MySQL
- Laravel Sanctum (API Authentication)
- barryvdh/laravel-dompdf (PDF Generation)

### Frontend
- Vue.js 3
- Vue Router
- Pinia (State Management)
- Tailwind CSS
- Chart.js
- Day.js

## Installation | التثبيت

### Requirements | المتطلبات
- PHP >= 8.1
- Composer
- Node.js >= 18
- MySQL >= 8.0

### Steps | الخطوات

1. Clone the repository
```bash
git clone <repository-url>
cd crm
```

2. Install PHP dependencies
```bash
composer install
```

3. Install Node.js dependencies
```bash
npm install
```

4. Copy environment file
```bash
cp .env.example .env
```

5. Generate application key
```bash
php artisan key:generate
```

6. Configure database in `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_fitout
DB_USERNAME=root
DB_PASSWORD=
```

7. Run migrations and seeders
```bash
php artisan migrate --seed
```

8. Create storage link
```bash
php artisan storage:link
```

9. Build frontend assets
```bash
npm run build
```

10. Start the development server
```bash
php artisan serve
```

## Default Login Credentials | بيانات الدخول الافتراضية

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@crm.com | password |
| Manager | manager@crm.com | password |
| Sales | sales@crm.com | password |
| Engineer | engineer@crm.com | password |

## API Documentation | توثيق API

### Authentication
- `POST /api/login` - Login
- `POST /api/register` - Register
- `POST /api/logout` - Logout (requires auth)
- `GET /api/user` - Get current user

### Leads
- `GET /api/leads` - List leads
- `POST /api/leads` - Create lead
- `GET /api/leads/{id}` - Get lead
- `PUT /api/leads/{id}` - Update lead
- `DELETE /api/leads/{id}` - Delete lead
- `POST /api/leads/{id}/activities` - Add activity

### Site Visits
- `GET /api/site-visits` - List visits
- `POST /api/site-visits` - Create visit
- `GET /api/site-visits/{id}` - Get visit
- `POST /api/site-visits/{id}/photos` - Upload photos
- `POST /api/site-visits/{id}/measurements` - Add measurement

### Quotations
- `GET /api/quotations` - List quotations
- `POST /api/quotations` - Create quotation
- `GET /api/quotations/{id}` - Get quotation
- `POST /api/quotations/{id}/send` - Send quotation
- `POST /api/quotations/{id}/pdf` - Generate PDF

### Projects
- `GET /api/projects` - List projects
- `POST /api/projects` - Create project
- `GET /api/projects/{id}` - Get project
- `PUT /api/projects/{id}/phases/{phase}` - Update phase
- `POST /api/projects/{id}/photos` - Upload photos

### Finance
- `GET /api/invoices` - List invoices
- `POST /api/invoices` - Create invoice
- `GET /api/payments` - List payments
- `POST /api/payments` - Record payment
- `GET /api/expenses` - List expenses

### Reports
- `GET /api/dashboard` - Dashboard stats
- `GET /api/reports/leads` - Leads report
- `GET /api/reports/projects` - Projects report
- `GET /api/reports/financial` - Financial report

## Project Structure | هيكل المشروع

```
crm/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/    # API Controllers
│   │   └── Middleware/         # Custom Middleware
│   ├── Models/                 # Eloquent Models
│   └── Providers/              # Service Providers
├── database/
│   ├── migrations/             # Database Migrations
│   └── seeders/                # Database Seeders
├── resources/
│   ├── js/
│   │   ├── components/         # Vue Components
│   │   ├── layouts/            # Layout Components
│   │   ├── router/             # Vue Router
│   │   ├── stores/             # Pinia Stores
│   │   └── views/              # Page Views
│   ├── css/                    # Stylesheets
│   └── views/                  # Blade Templates
├── routes/
│   ├── api.php                 # API Routes
│   └── web.php                 # Web Routes
└── public/                     # Public Assets
```

## License | الترخيص

MIT License

## Support | الدعم

For support, please contact: support@company.com
