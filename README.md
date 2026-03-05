# Survey Maker System

A comprehensive survey creation and management platform built with Slim Framework, Medoo, and modern web technologies.

## Features

### 📊 **Survey Management**
- Create unlimited surveys with sections and questions
- Support for multiple question types:
  - Text input
  - Yes/No questions  
  - Scale (1-5 rating)
  - Multiple choice (with custom options)
  - File upload (PDF only)
- Private surveys with passkey protection
- Public surveys accessible to everyone

### 🔗 **Link Sharing**
- **Environment-aware URLs**: Automatically uses `app_url` from config
- **Public survey links**: Direct access for public surveys
- **Private survey links**: Includes passkey in URL for seamless access
- **Secure sharing**: Copy shareable links from admin dashboard
- **Cross-environment support**: Works with localhost, survey.local, or any domain

### 👥 **Respondent Experience**
- Clean, Google Forms-inspired interface
- Progressive form with sections
- File upload with PDF validation (5MB limit)
- Duplicate submissions allowed per email
- Thank you page after submission

### 🎛️ **Admin Dashboard**
- Session-based authentication with middleware protection
- Real-time survey creation with Alpine.js
- AJAX-powered section and question management
- Response analytics and reporting
- Respondent management and individual response viewing
- Survey sharing interface with copy-to-clipboard

### 🔒 **Security**
- Password hashing with bcrypt
- Session-based authentication
- Middleware protection for admin routes
- File upload validation (PDF only)
- CSRF protection ready

## Tech Stack

- **Framework**: Slim Framework 4 with PSR-7
- **Database**: MySQL with Medoo query builder
- **Frontend**: Tailwind CSS, Alpine.js 3.x
- **Authentication**: Session-based with middleware
- **File Storage**: Local storage with organized structure
- **PHP**: 7.4+ with type hints and PHPDoc

## Project Structure

```
survey/
├── public/
│   ├── index.php          # Application entry point
│   └── uploads/           # Survey file uploads
│       └── survey_{id}/
│           └── respondent_{id}/
├── src/
│   ├── Controllers/       # Request handlers
│   │   ├── AdminController.php
│   │   ├── SurveyController.php
│   │   ├── SectionController.php
│   │   ├── QuestionController.php
│   │   ├── RespondentController.php
│   │   └── ResponseController.php
│   ├── Middleware/        # Authentication & security
│   │   └── AdminAuthMiddleware.php
│   ├── Models/            # Database models
│   │   ├── Survey.php
│   │   ├── Section.php
│   │   ├── Question.php
│   │   ├── QuestionOption.php
│   │   ├── Respondent.php
│   │   ├── Response.php
│   │   ├── File.php
│   │   └── Admin.php
│   ├── Services/          # Business services
│   │   └── FileUploader.php
│   └── Views/             # HTML templates
│       ├── layout/        # Shared templates
│       ├── admin/         # Admin interface
│       ├── public/        # Public survey listing
│       └── respondent/    # Survey form interface
├── config/
│   ├── database.php       # Database connection
│   ├── config.php         # App configuration
│   ├── routes.php         # Route definitions
│   ├── controllers.php    # DI container setup
│   ├── models.php         # Model bindings
│   └── services.php       # Service bindings
├── vendor/                # Composer dependencies
├── composer.json
└── survey.sql            # Database schema with sample data
```

## Installation

### Prerequisites
- PHP 7.4+ with PDO MySQL extension
- MySQL 8.0+ or equivalent
- Composer
- Web server (Apache/Nginx/built-in PHP server)

### Quick Setup

1. **Clone and install dependencies**
   ```bash
   composer install
   ```

2. **Import database schema**
   ```bash
   mysql -u root < survey.sql
   ```

3. **Configure application**
   - Edit `config/database.php` for your MySQL settings
   - Update `config/config.php` with your environment settings:
   ```php
   'app_url' => 'http://localhost:8000',        // For localhost
   // OR
   'app_url' => 'http://survey.local',          // For virtual hosts
   ```

4. **Start server**
   ```bash
   php -S localhost:8000 -t public/
   ```

5. **Access the system**
   - **Public surveys**: http://localhost:8000  
   - **Admin login**: http://localhost:8000/admin/login
   - **Credentials**: admin / admin123

## Configuration

### App URL Setup
The `app_url` setting in `config/config.php` is crucial for proper link sharing:

```php
// For local development
'app_url' => 'http://localhost:8000',

// For virtual host (hosts file setup)
'app_url' => 'http://survey.local',

// For production
'app_url' => 'https://surveys.yourdomain.com',
```

### File Upload Settings
```php
'upload_path' => __DIR__ . '/../public/uploads',
'upload_max_size' => 5242880, // 5MB
'allowed_extensions' => ['pdf'],
```

## Usage Guide

### 🔗 **Link Sharing**

#### For Public Surveys
1. Go to Admin Dashboard → Surveys
2. Click "Share" next to any public survey
3. Copy the generated link: `http://your-domain/surveys/1/take`
4. Share with anyone - no passkey required

#### For Private Surveys  
1. Create a survey and set it as **Private** with a passkey
2. Click "Share" to get a pre-authenticated link
3. Link includes passkey: `http://your-domain/surveys/1/take?key=your-passkey`
4. Recipients can access directly without entering passkey

#### Environment Changes
When moving between environments (localhost → survey.local → production):
1. Update `app_url` in `config/config.php`
2. Re-generate shareable links from admin dashboard
3. All new links will use the updated URL automatically

### 👥 **Survey Creation Workflow**

1. **Login to admin**: `/admin/login` (admin / admin123)
2. **Create survey**: Add title, description, set public/private
3. **Add sections**: Organize questions into logical groups
4. **Add questions**: Choose from 5 question types
5. **Configure options**: For multiple choice questions
6. **Test survey**: Use the "Test Link" button
7. **Share survey**: Copy shareable link for distribution

### 📊 **Response Management**

- **View results**: Click "Results" for analytics dashboard
- **Individual responses**: Click "Respondents" to see detailed submissions
- **File downloads**: Access uploaded PDFs from response details
- **Export options**: Ready for CSV/Excel export integration

## Database Schema

8 interconnected tables:
- `admins` - Admin user accounts
- `surveys` - Survey metadata and settings
- `sections` - Survey sections for organization  
- `questions` - Individual questions with types
- `question_options` - Multiple choice options
- `respondents` - Response submissions
- `responses` - Individual question answers
- `files` - Uploaded PDF files

## Security Features

- **Session-based authentication** with middleware protection
- **Password hashing** using bcrypt
- **File validation** (PDF only, 5MB limit)
- **CSRF protection** ready for implementation
- **Private survey passkeys** for access control
- **Admin route protection** via middleware

## Routes

### Public Routes
```
GET  /                           # Public survey listing
GET  /surveys/{id}/take          # Survey form (supports ?key= for private)
POST /surveys/{id}/submit        # Submit survey response
GET  /surveys/{id}/thank-you     # Thank you page
```

### Admin Routes (Protected by Middleware)
```
GET  /admin/login               # Admin login form
POST /admin/login               # Process login
GET  /admin/logout              # Logout
GET  /admin                     # Dashboard redirect
GET  /admin/surveys             # Survey management
GET  /admin/surveys/create      # Create survey form
GET  /admin/surveys/{id}/edit   # Edit survey
GET  /admin/surveys/{id}/share  # Generate shareable link
GET  /admin/surveys/{id}/results     # Analytics dashboard
GET  /admin/surveys/{id}/respondents # Response management
```

## Customization

### Adding Question Types
1. Update `Question` model with new type
2. Add form input in `admin/survey-form.php`
3. Handle rendering in `respondent/survey.php`
4. Process submission in `RespondentController`

### Styling
- Built with Tailwind CSS
- Modify templates in `src/Views/`
- Update Alpine.js components in `src/Views/admin/js/`

### File Types
- Update `FileUploader` service
- Modify `allowed_extensions` in config
- Update MIME type validation

---

**🎯 Ready to create surveys!** The system handles everything from simple polls to complex multi-section surveys with file uploads and private access control.
