# Hazem Abdullah - Home Design Portfolio Website

A modern, dynamic portfolio website built with Laravel and Vue.js featuring a full-featured admin panel for content management.

## Features

- **Dynamic Homepage** with artistic animations and effects
- **Portfolio Gallery** with image and video support
- **Services Section** showcasing offerings
- **Client Testimonials** with ratings
- **Contact Section** with business information
- **Full Admin Panel** to manage all content without coding
- **Responsive Design** optimized for all devices
- **Beautiful UI** using Tailwind CSS with bronze/copper color scheme

## Tech Stack

### Backend
- Laravel 10
- MySQL Database
- RESTful API
- Laravel Sanctum for authentication

### Frontend
- Vue.js 3
- Vue Router
- Pinia (State Management)
- Tailwind CSS
- GSAP for animations
- Vite for build tooling

## Installation Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8.1+)
- Node.js (v16 or higher)
- Composer

### Step 1: Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `hazem`
3. Set collation to `utf8mb4_unicode_ci`

### Step 2: Install Dependencies

Open terminal in `c:\xampp\htdocs\hazem` and run:

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Configure Environment

The `.env` file is already configured for localhost/hazem. If needed, update:

```env
DB_DATABASE=hazem
DB_USERNAME=root
DB_PASSWORD=
```

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

### Step 5: Run Migrations and Seed Database

```bash
php artisan migrate --seed
```

This will create all necessary tables and populate them with sample data.

### Step 6: Create Storage Link

```bash
php artisan storage:link
```

### Step 7: Build Frontend Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### Step 8: Start the Application

1. Make sure XAMPP Apache and MySQL are running
2. Access the website at: **http://localhost/hazem/**
3. Access admin panel at: **http://localhost/hazem/admin/login**

## Default Admin Credentials

- **Email:** admin@hazem.com
- **Password:** admin123

**Important:** Change these credentials after first login!

## Admin Panel Features

### Dashboard
- Overview statistics
- Quick access to all sections

### Sections Management
- Edit homepage sections
- Update titles, subtitles, and content
- Toggle section visibility
- Customize background colors

### Portfolio Management
- Add/Edit/Delete portfolio items
- Upload multiple images and videos
- Categorize projects
- Set featured items
- Add project details (client, date, location)

### Services Management
- Create and manage services
- Choose from predefined icons
- Control visibility

### Testimonials Management
- Add client testimonials
- Set ratings (1-5 stars)
- Include client information
- Toggle active status

### Settings
- Update site information
- Manage contact details
- Configure social media links
- Edit business hours

## File Upload Support

The admin panel supports uploading:
- **Images:** JPG, JPEG, PNG, GIF (max 50MB)
- **Videos:** MP4, MOV, AVI, WEBM (max 50MB)

Uploaded files are stored in `public/storage/media/`

## Project Structure

```
hazem/
├── app/
│   ├── Http/Controllers/Api/    # API Controllers
│   └── Models/                   # Eloquent Models
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── public/                       # Public assets
├── resources/
│   ├── css/                      # Tailwind CSS
│   ├── js/
│   │   ├── components/           # Vue components
│   │   ├── views/                # Vue pages
│   │   ├── stores/               # Pinia stores
│   │   └── router/               # Vue Router
│   └── views/                    # Blade templates
├── routes/
│   ├── api.php                   # API routes
│   └── web.php                   # Web routes
└── storage/                      # File storage
```

## Customization

### Colors
The bronze/copper color scheme is defined in `tailwind.config.js`:
- Primary: #B8824A
- Light: #C89A5F
- Dark: #A67340

### Logo
Replace `public/Logo Transparent 2.png` with your own logo.

### Fonts
- Sans-serif: Inter
- Serif: Playfair Display

Fonts are loaded from Google Fonts in `resources/views/app.blade.php`

## Troubleshooting

### Issue: 404 Not Found
- Ensure `.htaccess` files are present in root and `public/` directories
- Check Apache `mod_rewrite` is enabled in XAMPP

### Issue: Database Connection Error
- Verify MySQL is running in XAMPP
- Check database name is `hazem`
- Ensure credentials in `.env` are correct

### Issue: Assets Not Loading
- Run `npm run build` to compile assets
- Clear browser cache
- Check `public/build/` directory exists

### Issue: File Upload Fails
- Run `php artisan storage:link`
- Check `storage/app/public/` has write permissions
- Verify `upload_max_filesize` in php.ini

### Issue: Admin Login Not Working
- Run `php artisan migrate:fresh --seed` to reset database
- Clear browser cookies
- Check browser console for errors

## Development

### Running in Development Mode

```bash
# Terminal 1 - Vite dev server
npm run dev

# Access at http://localhost/hazem/
```

### Building for Production

```bash
npm run build
```

## API Endpoints

### Public Endpoints
- `GET /api/settings` - Get all settings
- `GET /api/sections` - Get all sections
- `GET /api/portfolio` - Get all portfolio items
- `GET /api/portfolio/{id}` - Get single portfolio item
- `GET /api/services` - Get all services
- `GET /api/testimonials` - Get all testimonials

### Protected Endpoints (Require Authentication)
- `POST /api/login` - Admin login
- `POST /api/logout` - Admin logout
- `GET /api/me` - Get authenticated user
- All POST, PUT, DELETE operations for content management

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is proprietary software created for Hazem Abdullah Home Design.

## Support

For issues or questions, please contact the development team.

---

**Built with ❤️ using Laravel, Vue.js, and Tailwind CSS**
