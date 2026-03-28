# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Fintra Backend is a PHP-based financial content and tools platform serving users in multiple languages (English, Hindi, Gujarati). The application provides financial calculators, investment recommendations, educational content, and user portfolio management.

## Architecture

### Entry Points

- **`index.php`** - Main page router that loads Twig templates and renders HTML responses with language support and caching via Memcache
- **`api.php`** - RESTful API endpoint router that handles service requests and returns JSON responses
- Various standalone `.php` files (e.g., `admin.php`, `redirect.php`) - Page-specific or feature-specific handlers

### Core Technology Stack

- **PHP 7.x** with Twig 2.0 templating engine
- **Database**: MySQL via Directus SDK and custom DAOs
- **Search**: Apache Solr via Solarium library
- **Framework**: Legacy Zend Framework components (zend-db, zend-stdlib)
- **Caching**: Memcache for template and data caching
- **ORM**: Custom DAO (Data Access Object) pattern implementations

### Directory Structure

```
/apis/                      # API endpoint classes and services
  /dao/                     # Database Access Objects (DAOs)
    Database.php            # Connection management
    DAOBase.php             # Base DAO class for common DB operations
    [Entity]DAO.php         # Entity-specific DAOs (BlogDAO, CalculatorDAO, etc.)
  /services/                # Business logic services
  /calculators/             # Calculator-specific implementations
  /portfolio_classes/       # Portfolio management logic
  /content_classes/         # Content-related operations
  *.php                     # API endpoint class handlers (HomepageClass, UserClass, etc.)

/templates/                 # Twig HTML templates
  *.html                    # Template files with Twig syntax

/utils/                     # Shared utilities
  utils.php                 # General utilities (JWT, language handling, etc.)
  cmsutils.php              # Directus CMS SDK integration
  memcache.php              # Memcache wrapper
  dbutils.php               # Database utilities
  solrutils.php             # Solr search utilities
  /dao/                     # Data utilities
  /tests/                   # Utility test files

/conf/                      # Configuration files
  db.conf                   # Database credentials

/lib/                       # Third-party libraries and integrations
/vendor/                    # Composer dependencies
/assets/                    # Static assets (CSS, JS, images)
/css/                       # Stylesheets
/js/                        # JavaScript files
/uploads/                   # User-uploaded files
/documents/                 # Document storage

/mocks/                     # Mock data for testing
/mocks/stocks/              # Stock mock data
/mocks/mutual-funds/        # Mutual fund mock data
```

## Key API Endpoints

The API router (`api.php`) handles these primary services via the `service` query parameter:

- **`home`** - Homepage data
- **`topics`** - Educational topics listing
- **`chapters`** - Chapter content
- **`chapterdetail`** - Detailed chapter information
- **`topfunds`** - Top mutual funds
- **`fetchrisk`** - Risk assessment questionnaire
- **`user`** - User profile operations
- **`getPortfolio`** - User portfolio data
- **`searchCity`** - City search (for location-based features)
- **`getBlogs`** - Blog listing
- **`getContentDetails`** - Content detail retrieval
- **`search`** - Search autocomplete suggestions
- **`gptcomplete`** - GPT-powered completions
- **`getlisting`** - Credit card listings
- **`log`** - Analytics and event logging

### API Request Format

- Accepts JSON body: `json_decode(file_get_contents('php://input'), true)`
- Falls back to `$_REQUEST` if body is empty
- CORS headers enabled for all origins
- All responses are JSON-encoded

## Data Access Layer

### DAO Pattern

All database queries use the DAO (Data Access Object) pattern:

- **`DAOBase.php`** - Abstract base class providing common database operations
- **Entity-specific DAOs** - Inherit from DAOBase and implement entity-specific queries
  - Common DAOs: BlogDAO, CalculatorDAO, CreditCardDAO, LoanApplicationDAO, etc.
- **`Database.php`** - Manages database connection via Zend DB

### Directus CMS Integration

- Uses Directus SDK for headless CMS operations
- Configured in `cmsutils.php`
- Handles multilingual content (English, Hindi, Gujarati)
- Database: `fintracms` on configured MySQL host

## Configuration

### Database Configuration

Located in `/conf/db.conf`:
```php
DBHOST = '...'  // MySQL hostname
DBUSER = '...'  // MySQL username
DBPASS = '...'  // MySQL password
```

### Language Support

Three languages supported with ISO 639-1 codes:
- `english` → `en`
- `hindi` → `hi`
- `gujarati` → `gu`

Language selected via `ln` query parameter in requests. Defaults to `english` if not specified.

## Caching Strategy

- **Memcache** used for template rendering results and frequently accessed data
- Cache keys follow pattern: `[page-type]-[language-code]` (e.g., `home-en`, `home-hi`)
- Set expiration time and check cache before computing expensive operations

## Common Development Tasks

### Adding a New API Endpoint

1. Create a new class in `/apis/` (e.g., `MyNewClass.php`)
2. Implement `fetchData()` or similar method accepting `$params`
3. Add a `case` in `api.php` router under the switch statement
4. Return JSON-serializable data

### Adding Database Queries

1. Create or extend a DAO in `/apis/dao/`
2. Extend `DAOBase` to inherit common CRUD operations
3. Add entity-specific query methods
4. Use in API classes via instantiation

### Adding Templates

1. Create `.html` file in `/templates/`
2. Use Twig syntax for variables and logic: `{{ variable }}`, `{% for %}`
3. Load in PHP via `$twig->load('template-name.html')`
4. Render with `$template->render($data_array)`

### Handling Multiple Languages

Use the language utility to get language-specific content:

```php
$lang = $_REQUEST['ln'] ?? 'english';
$lang_key = Utils::$language_keys[$lang];  // 'en', 'hi', 'gu'
$data = CMSUtils::getConfigValue($key, $lang_key);  // Fetch language-specific config
```

## Important Notes

- **Security**: JWT authentication uses a static key in `utils.php`. Review before production.
- **Legacy Code**: Zend Framework 1.x components are outdated; consider modernization for new code.
- **Database Credentials**: Stored in `/conf/db.conf` - never commit with real credentials.
- **Memcache Dependency**: Caching depends on Memcache being available; graceful fallback needed.
- **File Permissions**: `/uploads` and `/documents` directories need write permissions.
- **CORS**: Headers allow all origins; restrict as needed for security.

## Testing

Test files located in `/utils/tests/` and various test/mock files in the root and `/mocks/` directory.

## Deployment

- **Remote Server**: `/var/www/html` on `ec2-user@13.126.2.19`
- **Database**: Configured via `/conf/db.conf`
- Ensure Memcache, MySQL, and Apache/PHP are running on the server
