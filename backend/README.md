# Hotel Reservation System - Backend API

## Overview

This is a complete PHP and MySQL backend for a hotel reservation system. It provides RESTful API endpoints for managing rooms, checking availability, creating reservations, and more.

## Tech Stack

- **Backend**: PHP (PDO)
- **Database**: MySQL
- **Data Format**: JSON
- **Security**: Prepared statements, input validation

## Database Setup

### 1. Create Database

Run the SQL file to create the database and tables:

```sql
mysql -u root -p < backend/sql/hotel_reservation.sql
```

Or manually:

1. Open phpMyAdmin or MySQL command line
2. Import `backend/sql/hotel_reservation.sql`
3. The script will create:
   - Database: `hotel_reservation_db`
   - Tables: `rooms`, `reservations`
   - Sample data for 6 rooms

### 2. Configure Database Connection

Edit `backend/config/database.php` and update credentials:

```php
private $host = "localhost";
private $db_name = "hotel_reservation_db";
private $username = "root";        // Change for production
private $password = "";            // Change for production
```

## API Endpoints

### Base URL

```
http://localhost/backend/api/
```

### 1. GET ROOMS

**Endpoint**: `GET /api/get_rooms.php`

**Description**: Retrieves all available rooms

**Response**:

```json
{
  "success": true,
  "message": "Rooms retrieved successfully",
  "data": [
    {
      "id": 1,
      "room_name": "Deluxe King Suite",
      "room_type": "Suite",
      "price_per_night": 299.0,
      "description": "...",
      "image_url": "...",
      "total_rooms": 5,
      "max_guests": 2,
      "features": ["King Bed", "City View", "Mini Bar", "WiFi"]
    }
  ],
  "count": 6
}
```

### 2. CHECK AVAILABILITY

**Endpoint**: `POST /api/check_availability.php`

**Request Body**:

```json
{
  "check_in_date": "2025-12-20",
  "check_out_date": "2025-12-23",
  "room_id": 1 // Optional
}
```

**Response**:

```json
{
  "success": true,
  "message": "Available rooms found",
  "data": [
    {
      "id": 1,
      "room_name": "Deluxe King Suite",
      "available_rooms": 4,
      "price_per_night": 299.0
    }
  ],
  "count": 5
}
```

### 3. BOOK ROOM

**Endpoint**: `POST /api/book_room.php`

**Request Body**:

```json
{
  "room_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+1-555-0123",
  "check_in_date": "2025-12-20",
  "check_out_date": "2025-12-23",
  "guests": 2,
  "special_requests": "Late check-in"
}
```

**Response**:

```json
{
  "success": true,
  "message": "Reservation created successfully!",
  "data": {
    "reservation_id": 4,
    "room_name": "Deluxe King Suite",
    "total_price": 897.0,
    "nights": 3,
    "status": "confirmed"
  }
}
```

### 4. GET RESERVATIONS

**Endpoint**: `GET /api/get_reservations.php?email=john@example.com`
or `POST /api/get_reservations.php`

**Request Body** (POST):

```json
{
  "email": "john@example.com"
}
```

**Response**:

```json
{
  "success": true,
  "message": "Reservations retrieved successfully",
  "data": [
    {
      "id": 1,
      "room_name": "Deluxe King Suite",
      "check_in_date": "2025-12-20",
      "check_out_date": "2025-12-23",
      "status": "confirmed",
      "total_price": 897.0
    }
  ],
  "count": 1
}
```

### 5. CANCEL RESERVATION

**Endpoint**: `POST /api/cancel_reservation.php`

**Request Body**:

```json
{
  "reservation_id": 1,
  "email": "john@example.com"
}
```

**Response**:

```json
{
  "success": true,
  "message": "Reservation cancelled successfully",
  "data": {
    "reservation_id": 1,
    "status": "cancelled"
  }
}
```

## Testing with Postman

1. **Install Postman** (https://www.postman.com/)
2. **Set Content-Type**: `application/json`
3. **Test Endpoints**:
   - GET Rooms: `GET http://localhost/backend/api/get_rooms.php`
   - Book Room: `POST http://localhost/backend/api/book_room.php`

## Testing with JavaScript (Frontend)

```javascript
// Example: Fetch all rooms
fetch("http://localhost/backend/api/get_rooms.php")
  .then((response) => response.json())
  .then((data) => {
    console.log(data);
  });

// Example: Book a room
fetch("http://localhost/backend/api/book_room.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    room_id: 1,
    full_name: "John Doe",
    email: "john@example.com",
    phone: "+1-555-0123",
    check_in_date: "2025-12-20",
    check_out_date: "2025-12-23",
    guests: 2,
  }),
})
  .then((response) => response.json())
  .then((data) => {
    console.log(data);
  });
```

## Security Features

- ✅ **Prepared Statements**: All queries use PDO prepared statements
- ✅ **Input Validation**: Email, phone, dates validated
- ✅ **SQL Injection Prevention**: Using parameterized queries
- ✅ **Date Validation**: No past dates, check-out after check-in
- ✅ **Availability Checking**: Prevents overbooking
- ✅ **Transaction Support**: Database consistency

## Error Handling

All endpoints return structured error responses:

```json
{
  "success": false,
  "message": "Error description here",
  "data": null
}
```

## Project Structure

```
backend/
├── config/
│   └── database.php          # Database connection
├── api/
│   ├── get_rooms.php         # Fetch rooms
│   ├── check_availability.php # Check availability
│   ├── book_room.php         # Create reservation
│   ├── get_reservations.php  # Get user reservations
│   └── cancel_reservation.php # Cancel booking
├── sql/
│   └── hotel_reservation.sql # Database schema
└── README.md                 # This file
```

## Notes

- Change database credentials in production
- Enable HTTPS in production
- Add authentication for admin endpoints
- Consider rate limiting for API calls
- Log errors to files instead of displaying them

## License

Academic use only - CSC203 Presentation Project
