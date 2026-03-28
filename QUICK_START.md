# ⚡ Fintra Backend - Quick Start Guide

Get up and running in 5 minutes!

## Choose Your Setup Method

### 🐳 Method 1: Docker (Recommended - Easiest)

**Requirements:** Docker Desktop only

**Steps:**
```bash
# 1. Open Terminal and navigate to project
cd /Users/ruchi/fintra_backend

# 2. Copy environment template
cp .env.example .env

# 3. Edit .env with your credentials (OpenAI API key, AWS keys, etc.)
nano .env
# Press Ctrl+X, Y, Enter to save

# 4. Start the server
docker-compose up -d

# 5. Wait 10 seconds then test
sleep 10
curl http://localhost:8080

# 6. Run tests
bash test_server.sh
```

**Access:**
- Website: http://localhost:8080
- MySQL: localhost:3306

**Stop server:**
```bash
docker-compose down
```

---

### 🐍 Method 2: PHP Built-in Server (Quick Testing)

**Requirements:** PHP (no installation required)

**Steps:**
```bash
cd /Users/ruchi/fintra_backend

cp .env.example .env
nano .env  # Edit credentials

bash setup-php-server.sh
```

**Access:**
- Website: http://localhost:8000

**Note:** You'll need MySQL running separately:
```bash
mysql.server start
```

---

### ⚙️ Method 3: Manual Apache Setup

See `SETUP_INSTRUCTIONS.md` for detailed instructions.

---

## Test Your Setup

### Quick Test
```bash
curl http://localhost:8080
```

### Comprehensive Tests
```bash
# Run full diagnostic
bash diagnose.sh

# Run server tests
bash test_server.sh

# Test API endpoints
bash test_api.sh
```

### Test Individual APIs
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

## Common Commands

### Docker Commands
```bash
# View logs
docker-compose logs -f web

# Check container status
docker-compose ps

# Restart containers
docker-compose restart

# Stop all containers
docker-compose down

# Rebuild containers
docker-compose build --no-cache && docker-compose up -d

# Execute PHP command
docker-compose exec web php -v

# Access MySQL
docker-compose exec db mysql -u fintra_user -pfintra_pass fintracms
```

### PHP Built-in Server Commands
```bash
# Start server (runs in foreground)
php -S localhost:8000 server.php

# Run in background
php -S localhost:8000 server.php &

# Stop background server
kill %1
```

---

## Troubleshooting

### "Connection refused"
- Make sure server is running:
  - Docker: `docker-compose ps`
  - PHP: Check terminal with `php -S localhost:8000`

### "Port already in use"
```bash
# Find what's using the port
lsof -i :8080

# Kill the process (replace PID)
kill -9 <PID>

# Or use different port in docker-compose.yml
# Change "8080:80" to "8081:80"
```

### "Database connection failed"
```bash
# Check MySQL is running
mysql -u root -e "SELECT 1;"

# If error, start MySQL
mysql.server start

# Verify credentials in .env
cat .env | grep DB_
```

### "Docker daemon not running"
- Make sure Docker Desktop is open (look for whale 🐳 in menu bar)
- Relaunch Docker if needed

### ".env file not found"
```bash
cp .env.example .env
nano .env  # Edit with your credentials
```

---

## API Endpoints Reference

Available services:

| Service | Description |
|---------|-------------|
| `home` | Homepage data |
| `topics` | Topics list |
| `chapters` | Chapters list |
| `chapterdetail` | Chapter details |
| `topfunds` | Top mutual funds |
| `search` | Search suggestions |
| `searchCity` | Search cities |
| `getBlogs` | Blog listing |
| `getContentDetails` | Content details |
| `fetchrisk` | Risk questionnaire |
| `user` | User profile |
| `getPortfolio` | Portfolio data |
| `getlisting` | Credit card listing |
| `log` | Log analytics |

---

## Next Steps

1. ✅ Choose setup method (Docker recommended)
2. ✅ Follow steps for your method
3. ✅ Run `bash test_server.sh` to verify
4. ✅ Test API endpoints
5. ✅ Start developing!

---

## Files to Know

| File | Purpose |
|------|---------|
| `SETUP_INSTRUCTIONS.md` | Detailed setup guide |
| `INSTALL_DOCKER.md` | Docker installation steps |
| `LOCAL_SETUP.md` | Setup option details |
| `CLAUDE.md` | Architecture & code guide |
| `test_server.sh` | Comprehensive server tests |
| `test_api.sh` | API endpoint tests |
| `diagnose.sh` | System diagnostics |
| `docker-compose.yml` | Docker configuration |
| `.env.example` | Environment template |

---

## Getting Help

- **Setup issues?** → Check `INSTALL_DOCKER.md`
- **Need details?** → See `SETUP_INSTRUCTIONS.md`
- **Code questions?** → Read `CLAUDE.md`
- **Test failing?** → Run `bash diagnose.sh`

---

**Ready to start?** Pick a method above and follow the steps! 🚀
