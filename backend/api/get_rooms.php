<?php
/**
 * GET ROOMS API ENDPOINT
 * 
 * Retrieves all available rooms from the database
 * This endpoint fetches room information to display on the frontend
 * 
 * Method: GET
 * Response: JSON array of room objects
 */

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Include database configuration
require_once '../config/database.php';

// Initialize response array
$response = array();

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    // Check if connection was successful
    if ($db === null) {
        throw new Exception("Database connection failed");
    }

    // SQL query to fetch all rooms
    $query = "SELECT 
                id,
                room_name,
                room_type,
                price_per_night,
                description,
                image_url,
                total_rooms,
                max_guests,
                features,
                created_at
              FROM rooms 
              ORDER BY price_per_night ASC";

    // Prepare and execute the query
    $stmt = $db->prepare($query);
    $stmt->execute();

    // Fetch all results
    $rooms = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Convert features string to array
        $room_data = array(
            "id" => (int)$row['id'],
            "room_name" => $row['room_name'],
            "room_type" => $row['room_type'],
            "price_per_night" => (float)$row['price_per_night'],
            "description" => $row['description'],
            "image_url" => $row['image_url'],
            "total_rooms" => (int)$row['total_rooms'],
            "max_guests" => (int)$row['max_guests'],
            "features" => !empty($row['features']) ? explode(',', $row['features']) : array(),
            "created_at" => $row['created_at']
        );
        
        array_push($rooms, $room_data);
    }

    // Prepare success response
    $response['success'] = true;
    $response['message'] = "Rooms retrieved successfully";
    $response['data'] = $rooms;
    $response['count'] = count($rooms);
    http_response_code(200);

} catch (Exception $e) {
    // Handle errors
    $response['success'] = false;
    $response['message'] = "Error: " . $e->getMessage();
    $response['data'] = array();
    http_response_code(500);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>
