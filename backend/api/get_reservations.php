<?php
/**
 * GET RESERVATIONS API ENDPOINT
 * 
 * Retrieves reservations by email or phone number
 * Allows users to view their booking history
 * 
 * Method: GET or POST
 * Required Parameters: email OR phone
 * Response: JSON array of reservation objects
 */

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database configuration
require_once '../config/database.php';

// Initialize response array
$response = array();

try {
    // Get search parameters from GET or POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        $email = isset($data->email) ? trim($data->email) : null;
        $phone = isset($data->phone) ? trim($data->phone) : null;
    } else {
        $email = isset($_GET['email']) ? trim($_GET['email']) : null;
        $phone = isset($_GET['phone']) ? trim($_GET['phone']) : null;
    }

    // Validate that at least one search parameter is provided
    if (empty($email) && empty($phone)) {
        throw new Exception("Email or phone number is required to retrieve reservations");
    }

    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    if ($db === null) {
        throw new Exception("Database connection failed");
    }

    // Build query based on search parameters
    if (!empty($email) && !empty($phone)) {
        // Search by both email and phone
        $query = "SELECT 
                    r.id,
                    r.room_id,
                    ro.room_name,
                    ro.room_type,
                    ro.image_url,
                    r.full_name,
                    r.email,
                    r.phone,
                    r.check_in_date,
                    r.check_out_date,
                    r.guests,
                    r.special_requests,
                    r.total_price,
                    r.nights,
                    r.status,
                    r.created_at
                  FROM reservations r
                  JOIN rooms ro ON r.room_id = ro.id
                  WHERE r.email = :email AND r.phone = :phone
                  ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        
    } elseif (!empty($email)) {
        // Search by email only
        $query = "SELECT 
                    r.id,
                    r.room_id,
                    ro.room_name,
                    ro.room_type,
                    ro.image_url,
                    r.full_name,
                    r.email,
                    r.phone,
                    r.check_in_date,
                    r.check_out_date,
                    r.guests,
                    r.special_requests,
                    r.total_price,
                    r.nights,
                    r.status,
                    r.created_at
                  FROM reservations r
                  JOIN rooms ro ON r.room_id = ro.id
                  WHERE r.email = :email
                  ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        
    } else {
        // Search by phone only
        $query = "SELECT 
                    r.id,
                    r.room_id,
                    ro.room_name,
                    ro.room_type,
                    ro.image_url,
                    r.full_name,
                    r.email,
                    r.phone,
                    r.check_in_date,
                    r.check_out_date,
                    r.guests,
                    r.special_requests,
                    r.total_price,
                    r.nights,
                    r.status,
                    r.created_at
                  FROM reservations r
                  JOIN rooms ro ON r.room_id = ro.id
                  WHERE r.phone = :phone
                  ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':phone', $phone);
    }

    // Execute query
    $stmt->execute();

    // Fetch all reservations
    $reservations = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $reservation_data = array(
            "id" => (int)$row['id'],
            "room_id" => (int)$row['room_id'],
            "room_name" => $row['room_name'],
            "room_type" => $row['room_type'],
            "image_url" => $row['image_url'],
            "full_name" => $row['full_name'],
            "email" => $row['email'],
            "phone" => $row['phone'],
            "check_in_date" => $row['check_in_date'],
            "check_out_date" => $row['check_out_date'],
            "guests" => (int)$row['guests'],
            "special_requests" => $row['special_requests'],
            "total_price" => (float)$row['total_price'],
            "nights" => (int)$row['nights'],
            "status" => $row['status'],
            "created_at" => $row['created_at']
        );
        
        array_push($reservations, $reservation_data);
    }

    // Prepare response
    if (count($reservations) > 0) {
        $response['success'] = true;
        $response['message'] = "Reservations retrieved successfully";
        $response['data'] = $reservations;
        $response['count'] = count($reservations);
        http_response_code(200);
    } else {
        $response['success'] = true;
        $response['message'] = "No reservations found";
        $response['data'] = array();
        $response['count'] = 0;
        http_response_code(200);
    }

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
