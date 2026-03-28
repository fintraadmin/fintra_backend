#!/bin/bash

echo "🚀 Fintra Backend - Local Setup Script"
echo "======================================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "📋 Creating .env file from template..."
    cp .env.example .env
    echo "✅ Created .env - Please edit it with your actual credentials"
    echo ""
fi

# Check for Docker
if command -v docker &> /dev/null; then
    echo "✅ Docker found!"
    echo ""
    echo "Starting with Docker..."
    docker-compose up -d
    echo ""
    echo "📍 Website: http://localhost:8080"
    echo "🗄️  Database: localhost:3306"
    echo ""
    echo "Logs:"
    docker-compose logs -f web
else
    echo "⚠️  Docker not found."
    echo ""

    # Check for PHP
    if command -v php &> /dev/null; then
        echo "✅ PHP found: $(php -v | head -1)"
        echo ""
        echo "Starting PHP built-in server..."
        php -S localhost:8000 server.php
        echo ""
        echo "📍 Website: http://localhost:8000"
        echo "⚠️  Note: You need to have MySQL running separately"
    else
        echo "❌ Neither Docker nor PHP found."
        echo ""
        echo "Please install one of the following:"
        echo "1. Docker Desktop: https://www.docker.com/products/docker-desktop"
        echo "2. PHP: brew install php (macOS) or apt-get install php (Linux)"
        echo ""
        echo "Then run this script again."
        exit 1
    fi
fi
