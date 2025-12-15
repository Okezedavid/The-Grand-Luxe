<?php
/**
 * CANCEL RESERVATION API ENDPOINT
 * 
 * Cancels an existing reservation by ID
 * Updates the status to 'cancelled' instead of deleting
 * 
 * Method: POST
 * Required Parameters: reservation_id
 * Optional Parameters: email (for verification)
 * Response: JSON confirmation
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
    if (empty($data->reservation_id)) {
        throw new Exception("Reservation ID is required");
    }

    $reservation_id = (int)$data->reservation_id;
    $email = isset($data->email) ? trim($data->email) : null;

    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    if ($db === null) {
        throw new Exception("Database connection failed");
    }

    // First, verify reservation exists
    if ($email) {
        // Verify with email for security
        $verify_query = "SELECT id, full_name, email, status, room_id, check_in_date
                        FROM reservations 
                        WHERE id = :reservation_id AND email = :email";
        
        $verify_stmt = $db->prepare($verify_query);
        $verify_stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);
        $verify_stmt->bindParam(':email', $email);
    } else {
        // Verify without email
        $verify_query = "SELECT id, full_name, email, status, room_id, check_in_date
                        FROM reservations 
                        WHERE id = :reservation_id";
        
        $verify_stmt = $db->prepare($verify_query);
        $verify_stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);
    }

    $verify_stmt->execute();
    $reservation = $verify_stmt->fetch(PDO::FETCH_ASSOC);

    // Check if reservation exists
    if (!$reservation) {
        throw new Exception("Reservation not found or email does not match");
    }

    // Check if already cancelled
    if ($reservation['status'] === 'cancelled') {
        throw new Exception("This reservation has already been cancelled");
    }

    // Optional: Check if cancellation is allowed (e.g., not past check-in date)
    $check_in = new DateTime($reservation['check_in_date']);
    $today = new DateTime();
    
    if ($check_in < $today) {
        throw new Exception("Cannot cancel a reservation with a past check-in date");
    }

    // Update reservation status to cancelled
    $update_query = "UPDATE reservations 
                    SET status = 'cancelled', 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :reservation_id";
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        // Prepare success response
        $response['success'] = true;
        $response['message'] = "Reservation cancelled successfully";
        $response['data'] = array(
            "reservation_id" => $reservation_id,
            "guest_name" => $reservation['full_name'],
            "email" => $reservation['email'],
            "status" => "cancelled"
        );
        http_response_code(200);

    } else {
        throw new Exception("Failed to cancel reservation");
    }

} catch (Exception $e) {
    // Handle errors
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['data'] = null;
    http_response_code(400);
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>
