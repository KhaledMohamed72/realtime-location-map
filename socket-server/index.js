const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const cors = require("cors");
const redis = require("redis");

async function startServer() {
  // Initialize Express and HTTP server
  const app = express();
  app.use(cors());

  const server = http.createServer(app);

  const io = new Server(server, {
    cors: {
      origin: "*",
      methods: ["GET", "POST"],
    },
  });

  // Create Redis client
  const redisClient = redis.createClient();

  redisClient.on("connect", () => console.log("✅ Connected to Redis"));
  redisClient.on("ready", () => console.log("✅ Redis client ready"));
  redisClient.on("error", (err) => console.error("❌ Redis error:", err));

  await redisClient.connect();

  // Create a separate subscriber
  const subscriber = redisClient.duplicate();
  await subscriber.connect();

  await subscriber.subscribe("location-updates", (message) => {
    try {
      const payload = JSON.parse(message);

      if (payload.event === "App\\Events\\LocationUpdated") {
        const location = payload.data;
        io.emit("location-update", location);
        console.log("📡 Emitted location-update:", location);
      }
    } catch (err) {
      console.error("❌ Failed to parse message:", err);
    }
  });

  // Handle socket connections
  io.on("connection", (socket) => {
    console.log("A client connected:", socket.id);

    socket.on("disconnect", () => {
      console.log("Client disconnected:", socket.id);
    });
  });

  const PORT = 3000;
  server.listen(PORT, () => {
    console.log(`Node.js Socket.io server running on port ${PORT}`);
  });
}

startServer(); // Run the whole thing
