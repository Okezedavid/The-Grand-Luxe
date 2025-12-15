<?php
/**
 * BOOK ROOM API ENDPOINT
 * 
 * Creates a new reservation in the database
 * Validates all inputs and checks availability before booking
 * 
 * Method: POST
 * Required Parameters: room_id, full_name, email, phone, check_in_date, check_out_date, guests
 * Optional Parameters: special_requests
 * Response: JSON with booking confirmation
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
    if (empty($data->room_id)) {
        throw new Exception("Room ID is required");
    }
    if (empty($data->full_name)) {
        throw new Exception("Full name is required");
    }
    if (empty($data->email)) {
        throw new Exception("Email is required");
    }
    if (empty($data->phone)) {
        throw new Exception("Phone number is required");
    }
    if (empty($data->check_in_date)) {
        throw new Exception("Check-in date is required");
    }
    if (empty($data->check_out_date)) {
        throw new Exception("Check-out date is required");
    }
    if (empty($data->guests)) {
        throw new Exception("Number of guests is required");
    }

    // Sanitize and validate inputs
    $room_id = (int)$data->room_id;
    $full_name = trim($data->full_name);
    $email = filter_var(trim($data->email), FILTER_VALIDATE_EMAIL);
    $phone = trim($data->phone);
    $check_in = date('Y-m-d', strtotime($data->check_in_date));
    $check_out = date('Y-m-d', strtotime($data->check_out_date));
    $guests = (int)$data->guests;
    $special_requests = isset($data->special_requests) ? trim($data->special_requests) : null;

    // Validate email format
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    // Validate phone format (basic validation)
    if (!preg_match('/^[\d\s\-\+\(\)]+$/', $phone) || strlen($phone) < 10) {
        throw new Exception("Invalid phone number format");
    }

    // Validate date logic
    if ($check_in >= $check_out) {
        throw new Exception("Check-out date must be after check-in date");
    }

    // Check if dates are not in the past
    $today = date('Y-m-d');
    if ($check_in < $today) {
        throw new Exception("Check-in date cannot be in the past");
    }

    // Calculate number of nights
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date2->diff($date1)->days;

    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    if ($db === null) {
        throw new Exception("Database connection failed");
    }

    // Start transaction for data consistency
    $db->beginTransaction();

    // 1. Check if room exists and get details
    $query = "SELECT id, room_name, price_per_night, total_rooms, max_guests 
              FROM rooms 
              WHERE id = :room_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();

    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        throw new Exception("Room not found");
    }

    // 2. Validate number of guests
    if ($guests > $room['max_guests']) {
        throw new Exception("Number of guests exceeds room capacity (max: " . $room['max_guests'] . ")");
    }

    // 3. Check room availability for selected dates
    $availability_query = "SELECT COUNT(*) as booked_count
                          FROM reservations 
                          WHERE room_id = :room_id 
                          AND status != 'cancelled'
                          AND (
                              (check_in_date <= :check_in AND check_out_date > :check_in)
                              OR (check_in_date < :check_out AND check_out_date >= :check_out)
                              OR (check_in_date >= :check_in AND check_out_date <= :check_out)
                          )";
    
    $avail_stmt = $db->prepare($availability_query);
    $avail_stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $avail_stmt->bindParam(':check_in', $check_in);
    $avail_stmt->bindParam(':check_out', $check_out);
    $avail_stmt->execute();

    $availability = $avail_stmt->fetch(PDO::FETCH_ASSOC);
    $available_rooms = $room['total_rooms'] - $availability['booked_count'];

    if ($available_rooms <= 0) {
        throw new Exception("No rooms available for selected dates. Please choose different dates.");
    }

    // 4. Calculate total price
    $total_price = $nights * $room['price_per_night'];

    // 5. Insert reservation
    $insert_query = "INSERT INTO reservations 
                    (room_id, full_name, email, phone, check_in_date, check_out_date, 
                     guests, special_requests, total_price, nights, status) 
                    VALUES 
                    (:room_id, :full_name, :email, :phone, :check_in, :check_out, 
                     :guests, :special_requests, :total_price, :nights, 'confirmed')";
    
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':full_name', $full_name);
    $insert_stmt->bindParam(':email', $email);
    $insert_stmt->bindParam(':phone', $phone);
    $insert_stmt->bindParam(':check_in', $check_in);
    $insert_stmt->bindParam(':check_out', $check_out);
    $insert_stmt->bindParam(':guests', $guests, PDO::PARAM_INT);
    $insert_stmt->bindParam(':special_requests', $special_requests);
    $insert_stmt->bindParam(':total_price', $total_price);
    $insert_stmt->bindParam(':nights', $nights, PDO::PARAM_INT);

    if ($insert_stmt->execute()) {
        // Get the ID of the newly created reservation
        $reservation_id = $db->lastInsertId();

        // Commit transaction
        $db->commit();

        // Prepare success response with booking details
        $response['success'] = true;
        $response['message'] = "Reservation created successfully!";
        $response['data'] = array(
            "reservation_id" => (int)$reservation_id,
            "room_name" => $room['room_name'],
            "full_name" => $full_name,
            "email" => $email,
            "phone" => $phone,
            "check_in_date" => $check_in,
            "check_out_date" => $check_out,
            "guests" => $guests,
            "nights" => $nights,
            "price_per_night" => (float)$room['price_per_night'],
            "total_price" => (float)$total_price,
            "status" => "confirmed",
            "special_requests" => $special_requests
        );
        http_response_code(201);

    } else {
        throw new Exception("Failed to create reservation");
    }

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    // Handle errors
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['data'] = null;
    http_response_code(400);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>
