# 💰 Fintra Backend

A PHP-based financial content and tools platform serving users in multiple languages (English, Hindi, Gujarati).

**[Quick Start](./QUICK_START.md)** • **[Documentation](./CLAUDE.md)** • **[Setup Guides](./SETUP_INSTRUCTIONS.md)**

---

## ✨ Features

- 📱 Multi-language support (English, Hindi, Gujarati)
- 🧮 Financial calculators (EMI, SIP, Investment, etc.)
- 📊 Investment recommendations & portfolio management
- 📚 Educational content (topics, chapters, facts)
- 🔍 Advanced search with autocomplete
- 💳 Credit card & loan comparisons
- ⚡ High-performance caching (Memcache)
- 🔐 JWT authentication
- 🎯 RESTful API endpoints
- 🗄️ Directus CMS integration

---

## 🚀 Quick Start (5 Minutes)

### Prerequisites
- **Docker Desktop** (easiest) - [Install here](https://www.docker.com/products/docker-desktop)
- OR PHP 7.4+ with MySQL

### Setup with Docker

```bash
# 1. Clone/Navigate to project
cd /Users/ruchi/fintra_backend

# 2. Create environment file
cp .env.example .env

# 3. Edit credentials (add your API keys)
nano .env

# 4. Start server
docker-compose up -d

# 5. Test it works
bash test_server.sh
```

**Visit:** http://localhost:8080

### Setup without Docker

```bash
# 1. Create .env file
cp .env.example .env
nano .env

# 2. Start PHP server
bash setup-php-server.sh

# 3. Or use built-in server
php -S localhost:8000 server.php
```

**Visit:** http://localhost:8000

---

## 📖 Documentation

| Document | Purpose |
|----------|---------|
| **[QUICK_START.md](./QUICK_START.md)** | 5-minute setup guide |
| **[SETUP_INSTRUCTIONS.md](./SETUP_INSTRUCTIONS.md)** | Detailed installation |
| **[INSTALL_DOCKER.md](./INSTALL_DOCKER.md)** | Docker setup steps |
| **[LOCAL_SETUP.md](./LOCAL_SETUP.md)** | All setup options |
| **[CLAUDE.md](./CLAUDE.md)** | Code architecture & API docs |

---

## 🧪 Testing

### Run All Tests
```bash
bash test_server.sh
```

### Test API Endpoints
```bash
bash test_api.sh
```

### System Diagnostics
```bash
bash diagnose.sh
```

### Test Individual Endpoints
```bash
# Home endpoint
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"home"}'

# Topics
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"topics"}'

# Search
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"search","q":"loan"}'
```

---

## 🏗️ Architecture

### Entry Points
- **`index.php`** - Web pages (Twig templates)
- **`api.php`** - REST API endpoints

### Core Directories
```
apis/              API classes & business logic
├── dao/          Database access objects
├── services/     Business services
├── calculators/  Financial calculators
└── portfolio_classes/  Portfolio management

utils/            Utility functions
├── cmsutils.php  Directus CMS integration
├── utils.php     General utilities
└── memcache.php  Caching layer

templates/        Twig HTML templates
conf/            Configuration files
```

### Technology Stack
- **PHP 7.4+** with Twig 2.0 templates
- **MySQL 8.0** database
- **Directus SDK** for CMS
- **Apache Solr** for search
- **Memcache** for performance
- **JWT** for authentication

---

## 🔌 API Endpoints

### Available Services

**Data Endpoints:**
- `home` - Homepage content
- `topics` - Educational topics
- `chapters` - Chapter content
- `chapterdetail` - Detailed chapter info
- `topfunds` - Top mutual funds
- `getBlogs` - Blog posts
- `getContentDetails` - Content details
- `getlisting` - Credit card comparisons

**User & Search:**
- `user` - User profile operations
- `search` - Auto-complete search
- `searchCity` - Location search
- `fetchrisk` - Risk assessment quiz

**Advanced:**
- `getPortfolio` - Portfolio data
- `log` - Analytics logging
- `gptcomplete` - GPT-powered suggestions

---

## 📦 Docker Commands

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f web

# Stop services
docker-compose down

# Restart services
docker-compose restart

# Execute command in container
docker-compose exec web php -v

# Access MySQL
docker-compose exec db mysql -u fintra_user -pfintra_pass fintracms
```

---

## 🛠️ Development Workflow

### Local Development
```bash
# 1. Edit files in your IDE
# 2. Changes reflect immediately in browser
# 3. Run tests to verify
bash test_server.sh

# 4. Commit changes
git add .
git commit -m "Your message"

# 5. Push to remote
git push
```

### Common Development Tasks
- **Add API endpoint** - Create class in `apis/`, add case in `api.php`
- **Add database query** - Create/extend DAO in `apis/dao/`
- **Add template** - Create `.html` in `templates/`, use Twig syntax
- **Add calculator** - Create class in `apis/calculators/`

See [CLAUDE.md](./CLAUDE.md) for detailed architecture guide.

---

## 🔐 Configuration

### Environment Variables (`.env`)

Required variables:
```env
# OpenAI for GPT features
OPENAI_API_KEY=your_key_here

# AWS for file uploads
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=fintrafiles
AWS_REGION=ap-south-1

# Database (Docker auto-configured)
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=fintracms
```

### Language Support
- English (en)
- Hindi (hi)
- Gujarati (gu)

Select language via `ln` parameter:
```
GET /index.php?ln=english
GET /index.php?ln=hindi
GET /index.php?ln=gujarati
```

---

## 🐛 Troubleshooting

### Server Won't Start
```bash
# Check port isn't in use
lsof -i :8080

# Check Docker is running
docker-compose ps

# View logs
docker-compose logs web
```

### Database Connection Failed
```bash
# Check MySQL is running
mysql -u root -e "SELECT 1;"

# Check credentials in .env
grep DB_ .env

# Reset database
docker-compose down -v
docker-compose up -d
```

### API Returns Empty
```bash
# Check database is populated
docker-compose exec db mysql -u fintra_user -pfintra_pass fintracms -e "SHOW TABLES;"

# Check logs for errors
docker-compose logs web | tail -50
```

### "Docker daemon not running"
- Open Docker Desktop (look for whale 🐳 icon)
- Wait for "Docker is running" notification

See [QUICK_START.md](./QUICK_START.md#troubleshooting) for more solutions.

---

## 🚀 Deployment

### To Production Server
```bash
# SSH into server
ssh -i ~/Desktop/fintra.pem ec2-user@13.126.2.19

# Navigate to code
cd /var/www/html

# Pull latest
git pull origin main

# Restart services
# (Apache/PHP configuration needed on server)
```

### Using Docker
```bash
docker-compose up -d
```

---

## 📊 Project Status

| Component | Status |
|-----------|--------|
| Core API | ✅ Production |
| Calculators | ✅ Production |
| Search | ✅ Production |
| Authentication | ✅ Implemented |
| Admin Panel | 🔄 Development |
| Mobile App | ✅ Available |

---

## 👥 Contributing

1. Create a branch: `git checkout -b feature/your-feature`
2. Make changes and test: `bash test_server.sh`
3. Commit: `git commit -m "Add your feature"`
4. Push: `git push origin feature/your-feature`
5. Create Pull Request

---

## 📝 License

Proprietary - Fintra

---

## 🤝 Support

### Documentation
- **Code Architecture** → [CLAUDE.md](./CLAUDE.md)
- **Setup Guide** → [SETUP_INSTRUCTIONS.md](./SETUP_INSTRUCTIONS.md)
- **Quick Start** → [QUICK_START.md](./QUICK_START.md)

### Diagnostics
```bash
# Run full diagnostic
bash diagnose.sh

# Test server
bash test_server.sh

# Test APIs
bash test_api.sh
```

### Remote Server
- SSH: `ssh -i ~/Desktop/fintra.pem ec2-user@13.126.2.19`
- Path: `/var/www/html`

---

## 🎯 Next Steps

1. **New here?** → Start with [QUICK_START.md](./QUICK_START.md)
2. **Want setup details?** → Read [SETUP_INSTRUCTIONS.md](./SETUP_INSTRUCTIONS.md)
3. **Need Docker help?** → Check [INSTALL_DOCKER.md](./INSTALL_DOCKER.md)
4. **Understanding code?** → See [CLAUDE.md](./CLAUDE.md)
5. **Testing?** → Run `bash test_server.sh`

---

**Made with ❤️ for Fintra**
