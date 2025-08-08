<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Set content type
header('Content-Type: application/json');

// Require all necessary model files
$models = [
    'User', 'Admin', 'Menu', 'Order', 'Payment', 
    'Discount', 'Reservation', 'Rating', 'Feedback'
];
foreach ($models as $model) {
    $path = "models/$model.php";
    if (!file_exists($path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "$model model not found."]);
        exit;
    }
    require_once $path;
}

// Parse action and request method
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Safely decode JSON input only for POST/PUT
$data = in_array($method, ['POST', 'PUT']) ? json_decode(file_get_contents('php://input'), true) : [];

// Simulate logged-in user (temporary placeholder)
$loggedInUserId = $_GET['userId'] ?? 1;

$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    // User Management
    case 'addUser':
        $user = new User();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if ($username && $password && $email) {
            $res = $user->createUser($username, $password, $email);
            $response = $res ? ['success' => true, 'message' => 'User added successfully.'] : ['success' => false, 'message' => 'Failed to add user.'];
        } else {
            http_response_code(400);
            $response = ['success' => false, 'message' => 'Invalid user data.'];
        }
        break;

    case 'getUsers':
        $user = new User();
        $users = $user->getAllUsers();
        $response = ['success' => true, 'users' => $users];
        break;

    case 'updateUser':
        $user = new User();
        $res = $user->updateUser($data['id'], $data['username'], $data['email']);
        $response = $res ? ['success' => true, 'message' => 'User updated successfully.'] : ['success' => false, 'message' => 'Failed to update user.'];
        break;

    case 'deleteUser':
        $user = new User();
        $res = $user->deleteUser($data['id']);
        $response = $res ? ['success' => true, 'message' => 'User deleted successfully.'] : ['success' => false, 'message' => 'Failed to delete user.'];
        break;

    // Menu Management
    case 'addMenuItem':
        $menu = new Menu();
        $res = $menu->createMenuItem($data['name'], $data['price'], $data['ingredients']);
        $response = $res ? ['success' => true, 'message' => 'Menu item added successfully.'] : ['success' => false, 'message' => 'Failed to add menu item.'];
        break;

    case 'getMenuItems':
        $menu = new Menu();
        $items = $menu->getAllMenuItems();
        $response = ['success' => true, 'menuItems' => $items];
        break;

    case 'updateMenuItem':
        $menu = new Menu();
        $res = $menu->updateMenuItem($data['id'], $data['name'], $data['price'], $data['ingredients'], $data['availability']);
        $response = $res ? ['success' => true, 'message' => 'Menu item updated successfully.'] : ['success' => false, 'message' => 'Failed to update menu item.'];
        break;

    case 'deleteMenuItem':
        $menu = new Menu();
        $res = $menu->deleteMenuItem($data['id']);
        $response = $res ? ['success' => true, 'message' => 'Menu item deleted successfully.'] : ['success' => false, 'message' => 'Failed to delete menu item.'];
        break;

    // Order Process
    case 'createOrder':
        $order = new Order();
        $orderId = $order->createOrder($loggedInUserId, $data['items']);
        $response = $orderId ? ['success' => true, 'orderId' => $orderId, 'message' => 'Order created successfully.'] : ['success' => false, 'message' => 'Failed to create order.'];
        break;

    case 'getOrderDetails':
        $order = new Order();
        $orderDetails = $order->getOrderDetails($data['orderId']);
        $response = $orderDetails ? ['success' => true, 'orderDetails' => $orderDetails] : ['success' => false, 'message' => 'Order not found.'];
        break;

    // Payment
    case 'processPayment':
        $payment = new Payment();
        $res = $payment->processPayment($data['orderId'], $data['amount']);
        $response = $res ? ['success' => true, 'message' => 'Payment processed successfully.'] : ['success' => false, 'message' => 'Payment failed.'];
        break;

    // Table Reservation
    case 'createReservation':
        $reservation = new Reservation();
        $res = $reservation->createReservation($loggedInUserId, $data['date'], $data['time']);
        $response = $res ? ['success' => true, 'message' => 'Reservation created successfully.'] : ['success' => false, 'message' => 'Failed to create reservation.'];
        break;

    case 'cancelReservation':
        $reservation = new Reservation();
        $res = $reservation->cancelReservation($data['id']);
        $response = $res ? ['success' => true, 'message' => 'Reservation cancelled successfully.'] : ['success' => false, 'message' => 'Failed to cancel reservation.'];
        break;

    // Discount and Offers
    case 'validateDiscount':
        $discount = new Discount();
        $isValid = $discount->validateDiscount($data['code'], $data['billAmount']);
        $response = $isValid ? ['success' => true, 'message' => 'Discount code is valid.'] : ['success' => false, 'message' => 'Invalid discount code or bill amount.'];
        break;

    // Food Rating
    case 'submitRating':
        $rating = new Rating();
        $res = $rating->submitRating($data['menuItemId'], $loggedInUserId, $data['point'], $data['comment']);
        $response = $res ? ['success' => true, 'message' => 'Rating submitted successfully.'] : ['success' => false, 'message' => 'Failed to submit rating.'];
        break;

    // Customer Feedback
    case 'submitFeedback':
        $feedback = new Feedback();
        $res = $feedback->submitFeedback($loggedInUserId, $data['feedbackText']);
        $response = $res ? ['success' => true, 'message' => 'Feedback submitted successfully.'] : ['success' => false, 'message' => 'Failed to submit feedback.'];
        break;

    // QR Scanning (dummy)
    case 'scanQR':
        $response = ['success' => true, 'message' => 'QR code scanned successfully.'];
        break;

    default:
        http_response_code(400);
        $response = ['success' => false, 'message' => 'Invalid action.'];
        break;
}

// Output JSON response
echo json_encode($response);
