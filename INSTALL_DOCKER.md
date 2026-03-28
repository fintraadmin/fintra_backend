# 🐳 Docker Installation Guide for macOS

Complete guide to install Docker Desktop on your Mac and start the Fintra Backend.

## Step 1: Download Docker Desktop

### For Apple Silicon (M1/M2/M3 Macs)
1. Go to: https://www.docker.com/products/docker-desktop
2. Click "Download for Mac with Apple Silicon"
3. File: `Docker.dmg`

### For Intel Macs
1. Go to: https://www.docker.com/products/docker-desktop
2. Click "Download for Mac with Intel Chip"
3. File: `Docker.dmg`

## Step 2: Install Docker

1. Open the downloaded `Docker.dmg` file
2. Drag the Docker icon to the Applications folder
3. Wait for the copy process to complete

## Step 3: Launch Docker

1. Open **Applications** folder
2. Double-click **Docker.app**
3. Enter your Mac password when prompted (needed for privileged settings)
4. Wait for Docker to fully load (watch the menu bar icon)

### Check Docker is Running

When Docker starts, you'll see the whale icon 🐳 in the menu bar (top-right of screen).

## Step 4: Verify Installation

Open Terminal and run:

```bash
docker --version
docker run hello-world
```

If you see version info and a "Hello from Docker!" message, Docker is installed correctly! ✅

## Step 5: Start Fintra Backend with Docker

```bash
cd /Users/ruchi/fintra_backend

# Create .env file
cp .env.example .env

# Edit .env with your credentials
nano .env

# Start the server
docker-compose up -d

# Check status
docker-compose ps
```

## Step 6: Access Your Website

Open your browser:
- **Website**: http://localhost:8080
- **MySQL Database**: localhost:3306

## Useful Commands

```bash
# View logs
docker-compose logs -f web

# Stop server
docker-compose down

# Rebuild containers
docker-compose build --no-cache

# Execute command in container
docker-compose exec web php -v
```

## Troubleshooting

### "Docker daemon is not running"
- Make sure Docker.app is running (check menu bar for whale icon 🐳)
- Relaunch Docker.app if needed

### "Port 8080 already in use"
Edit `docker-compose.yml`, change:
```yaml
ports:
  - "8081:80"  # Use 8081 instead
```

Then visit: http://localhost:8081

### "Permission denied"
Run with sudo:
```bash
sudo docker-compose up -d
```

### Still having issues?
Check Docker logs:
```bash
# Open Docker Desktop menu > Troubleshoot > Reset
# Or uninstall and reinstall
```

---

## Next Steps

1. ✅ Docker installed and running?
2. ✅ Run `docker-compose up -d`
3. ✅ Visit http://localhost:8080
4. ✅ Run test script: `bash test_server.sh`

You're all set! 🚀
