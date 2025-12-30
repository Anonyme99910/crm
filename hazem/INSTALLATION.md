# Quick Installation Guide

## Prerequisites
1. **XAMPP** installed with Apache and MySQL running
2. **Node.js** (v16+) installed
3. **Composer** installed

## Quick Setup (Automated)

1. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create database named `hazem`

2. **Run Setup Script**
   ```bash
   cd c:\xampp\htdocs\hazem
   setup.bat
   ```

3. **Access Website**
   - Frontend: http://localhost/hazem/
   - Admin: http://localhost/hazem/admin/login
   - Login: admin@hazem.com / admin123

## Manual Setup

If the automated script fails, follow these steps:

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Configure Environment
```bash
php artisan key:generate
```

### 3. Setup Database
```bash
php artisan migrate:fresh --seed
```

### 4. Create Storage Link
```bash
php artisan storage:link
```

### 5. Build Assets
```bash
npm run build
```

## Development Mode

To run in development mode with hot reload:

```bash
npm run dev
```

Then access: http://localhost/hazem/

## Troubleshooting

### Error: "Database connection failed"
- Ensure MySQL is running in XAMPP
- Verify database `hazem` exists
- Check `.env` file has correct credentials

### Error: "404 Not Found"
- Check `.htaccess` files exist
- Enable mod_rewrite in Apache
- Restart Apache

### Error: "Permission denied"
- Run as Administrator
- Check folder permissions on `storage/` and `bootstrap/cache/`

### Error: "npm install fails"
- Clear npm cache: `npm cache clean --force`
- Delete `node_modules` and `package-lock.json`
- Run `npm install` again

## Post-Installation

1. **Change Admin Password**
   - Login to admin panel
   - Go to Settings
   - Update admin credentials

2. **Customize Content**
   - Update site settings
   - Add your portfolio items
   - Upload your images/videos
   - Customize sections

3. **Update Logo**
   - Replace `public/Logo Transparent 2.png` with your logo

## Support

For issues, check the main README.md file or contact support.
