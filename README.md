# Candidacy Management System

A clean, minimalist candidate and political party management system built with Slim Framework, Medoo, and Tailwind CSS.

## Features

- **Candidate Management**: Create, read, update, delete candidates with profile pictures
- **Party Management**: Create, read, update, delete political parties
- **File Upload**: Upload and manage candidate pictures locally
- **Clean UI**: Minimalist monochrome design using Tailwind CSS
- **Responsive Design**: Works on desktop and mobile devices

## Tech Stack

- **Framework**: Slim Framework 4
- **Database**: MySQL with Medoo query builder
- **Frontend**: Tailwind CSS, Alpine.js
- **PHP**: 7.4+

## Project Structure

```
candidate/
├── public/
│   ├── index.php          # Entry point
│   ├── .htaccess          # URL rewriting
│   └── uploads/           # Uploaded pictures
├── src/
│   ├── Controllers/       # Request handlers
│   │   ├── HomeController.php
│   │   ├── CandidateController.php
│   │   └── PartyController.php
│   ├── Models/            # Database models
│   │   ├── Candidate.php
│   │   └── Party.php
│   └── Views/             # HTML templates
│       ├── layout.php
│       ├── home.php
│       ├── candidates/
│       └── parties/
├── config/
│   ├── database.php       # MySQL configuration
│   └── config.php         # App settings
├── vendor/                # Composer packages
├── composer.json
└── candidacy.sql          # Database schema
```

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0+
- Composer
- Laragon or similar local server

### Steps

1. **Navigate to project directory**
   ```bash
   cd c:\laragon\www\candidate
   ```

2. **Install dependencies** (already done)
   ```bash
   composer install
   ```

3. **Create MySQL database**
   - Use HeidiSQL or MySQL CLI to import `candidacy.sql`
   ```bash
   mysql -u root < candidacy.sql
   ```

4. **Configure database** (if needed)
   - Edit `config/database.php` with your MySQL credentials
   - Default: host=localhost, user=root, password=''

5. **Start local server**
   ```bash
   php -S localhost:8000 -t public/
   ```

6. **Access application**
   - Open http://localhost:8000 in your browser

## Usage

### Home Page
- Dashboard with quick stats and navigation
- Links to manage candidates and parties

### Candidates
- **List**: View all registered candidates with pictures and parties
- **Add**: Create new candidate with code, name, gender, party, and picture
- **Edit**: Update candidate information or picture
- **Delete**: Remove candidate and associated picture

### Parties
- **List**: View all political parties
- **Add**: Create new party
- **Edit**: Update party name
- **Delete**: Remove party (referenced candidates remain)

## Key Features

### File Upload
- Supported formats: JPG, PNG, GIF
- Max file size: 2MB
- Files stored in `/public/uploads/`
- Old pictures automatically deleted on update

### Database
- Two main tables: `candidate`, `party`
- Foreign key relationship: `candidate.party_id` → `party.id`
- Auto-delete candidates when party is deleted (CASCADE)

### Design
- **Colors**: Monochrome (blacks, grays, whites)
- **Framework**: Tailwind CSS utilities
- **Icons**: Inline SVGs
- **Responsive**: Adapts to all screen sizes

## Routes

```
GET  /                      # Home page
GET  /candidates            # List candidates
GET  /candidates/create     # Add candidate form
POST /candidates            # Store new candidate
GET  /candidates/{id}/edit  # Edit candidate form
POST /candidates/{id}       # Update candidate
POST /candidates/{id}/delete# Delete candidate

GET  /parties               # List parties
GET  /parties/create        # Add party form
POST /parties               # Store new party
GET  /parties/{id}/edit     # Edit party form
POST /parties/{id}          # Update party
POST /parties/{id}/delete   # Delete party
```

## Configuration

Edit `config/config.php` to customize:
- App name and URL
- Upload file size limits (default 2MB)
- Allowed file extensions
- Items per page

## Notes

- Middle name is optional for candidates
- Pictures are stored in local filesystem, not database
- Use `public/index.php` to route all requests
- Slim Framework handles routing and dependency injection
- Medoo provides a clean database interface

## Future Enhancements

- Pagination for candidate lists
- Advanced search and filtering
- Candidate approval workflow
- Batch image processing
- Email notifications
- User authentication

## License

Open source
