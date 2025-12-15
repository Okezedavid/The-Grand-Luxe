<?php
/**
 * CHECK AVAILABILITY API ENDPOINT
 * 
 * Checks room availability for specified dates
 * Prevents overbooking by counting existing reservations
 * 
 * Method: POST
 * Required Parameters: check_in_date, check_out_date
 * Optional Parameters: room_id (if checking specific room)
 * Response: JSON with available rooms
 */

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database configuration
require_once '../config/database.php';

// Initialize response array
$response = array();

try {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"));

    // Validate required fields
    if (empty($data->check_in_date) || empty($data->check_out_date)) {
        throw new Exception("Check-in and check-out dates are required");
    }

    // Sanitize and validate dates
    $check_in = date('Y-m-d', strtotime($data->check_in_date));
    $check_out = date('Y-m-d', strtotime($data->check_out_date));
    
    // Validate date logic
    if ($check_in >= $check_out) {
        throw new Exception("Check-out date must be after check-in date");
    }

    // Check if dates are not in the past
    $today = date('Y-m-d');
    if ($check_in < $today) {
        throw new Exception("Check-in date cannot be in the past");
    }

    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    if ($db === null) {
        throw new Exception("Database connection failed");
    }

    // Optional: Check specific room or all rooms
    $room_id = isset($data->room_id) ? (int)$data->room_id : null;

    // Build query to check availability
    // This query counts overlapping reservations for each room
    if ($room_id) {
        // Check specific room
        $query = "SELECT 
                    r.id,
                    r.room_name,
                    r.room_type,
                    r.price_per_night,
                    r.description,
                    r.image_url,
                    r.total_rooms,
                    r.max_guests,
                    r.features,
                    (
                        SELECT COUNT(*) 
                        FROM reservations res 
                        WHERE res.room_id = r.id 
                        AND res.status != 'cancelled'
                        AND (
                            (res.check_in_date <= :check_in AND res.check_out_date > :check_in)
                            OR (res.check_in_date < :check_out AND res.check_out_date >= :check_out)
                            OR (res.check_in_date >= :check_in AND res.check_out_date <= :check_out)
                        )
                    ) as booked_count
                  FROM rooms r
                  WHERE r.id = :room_id
                  HAVING (r.total_rooms - booked_count) > 0";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    } else {
        // Check all rooms
        $query = "SELECT 
                    r.id,
                    r.room_name,
                    r.room_type,
                    r.price_per_night,
                    r.description,
                    r.image_url,
                    r.total_rooms,
                    r.max_guests,
                    r.features,
                    (
                        SELECT COUNT(*) 
                        FROM reservations res 
                        WHERE res.room_id = r.id 
                        AND res.status != 'cancelled'
                        AND (
                            (res.check_in_date <= :check_in AND res.check_out_date > :check_in)
                            OR (res.check_in_date < :check_out AND res.check_out_date >= :check_out)
                            OR (res.check_in_date >= :check_in AND res.check_out_date <= :check_out)
                        )
                    ) as booked_count
                  FROM rooms r
                  HAVING (r.total_rooms - booked_count) > 0
                  ORDER BY r.price_per_night ASC";
        
        $stmt = $db->prepare($query);
    }

    // Bind date parameters
    $stmt->bindParam(':check_in', $check_in);
    $stmt->bindParam(':check_out', $check_out);
    
    // Execute query
    $stmt->execute();

    // Fetch available rooms
    $available_rooms = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $available_count = (int)$row['total_rooms'] - (int)$row['booked_count'];
        
        $room_data = array(
            "id" => (int)$row['id'],
            "room_name" => $row['room_name'],
            "room_type" => $row['room_type'],
            "price_per_night" => (float)$row['price_per_night'],
            "description" => $row['description'],
            "image_url" => $row['image_url'],
            "total_rooms" => (int)$row['total_rooms'],
            "available_rooms" => $available_count,
            "max_guests" => (int)$row['max_guests'],
            "features" => !empty($row['features']) ? explode(',', $row['features']) : array()
        );
        
        array_push($available_rooms, $room_data);
    }

    // Prepare success response
    $response['success'] = true;
    $response['message'] = count($available_rooms) > 0 
        ? "Available rooms found" 
        : "No rooms available for selected dates";
    $response['data'] = $available_rooms;
    $response['count'] = count($available_rooms);
    $response['check_in_date'] = $check_in;
    $response['check_out_date'] = $check_out;
    http_response_code(200);

} catch (Exception $e) {
    // Handle errors
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['data'] = array();
    http_response_code(400);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>
