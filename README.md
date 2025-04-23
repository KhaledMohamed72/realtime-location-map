# Real-Time Location Tracking App

A real-time location tracking system built with Laravel, Node.js, Socket.io, Redis, and Leaflet.js. The application allows users to send, store, and visualize location data live on a map. Additional features include historical trails and filtering by time.

---

## ✨ Features

- Real-time location updates via WebSockets
- Interactive map display using Leaflet.js
- Store and view historical location data
- Time-based filtering of location trails
- REST API for sending and retrieving location data

---

## 📊 Tech Stack

- **Backend:** Laravel (PHP)
- **Real-time Server:** Node.js with Socket.io
- **Pub/Sub:** Redis
- **Frontend Map:** Leaflet.js
- **Database:** MySQL or PostgreSQL

---

## 🚧 Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/realtime-location-tracking.git
cd realtime-location-tracking
```

### 2. Backend Setup (Laravel)

```bash
cd laravel-backend
composer install
cp .env.example .env
php artisan key:generate
# Configure DB and Redis in .env
php artisan migrate
php artisan serve
```

### 3. Node.js Real-time Server

```bash
cd node-server
npm install
node index.js
```

### 4. Redis

Ensure Redis is installed and running on the default port (6379):

```bash
redis-server
```

### 5. Frontend

Open the HTML file in your browser:

```bash
cd frontend
open index.html
```

---

## ⚙️ Configuration

- Laravel runs on `http://localhost:8000`
- Node.js Socket.io server runs on `http://localhost:3000`
- Redis default port: `6379`
- Make sure `.env` file in Laravel is configured with correct DB and Redis settings

---

## 📁 API Endpoints

### POST /api/locations

Submit a new location.

```bash
curl -X POST http://localhost:8000/api/locations \
     -H "Content-Type: application/json" \
     -d '{"latitude": 41.712776, "longitude": -74.005974}'
```

### GET /api/locations?start=2024-01-01&end=2024-01-31

Retrieve location history within a date range.

```bash
curl http://localhost:8000/api/locations?start=2024-01-01&end=2024-01-31
```

---

## ⏰ Real-Time Flow

1. Laravel receives location via API.
2. Laravel broadcasts an event to Redis.
3. Node.js server subscribes to Redis, receives the event.
4. Socket.io emits it to all connected clients.
5. Leaflet.js updates the map in real-time.

