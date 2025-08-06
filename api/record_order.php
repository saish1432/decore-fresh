<?php
header('Content-Type: application/json');
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items']) || !isset($input['total_value'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

try {
    $items = $input['items'];
    $total_value = $input['total_value'];
    $order_date = $input['order_date'] ?? date('Y-m-d H:i:s');
    
    // Count total items
    $items_count = 0;
    foreach ($items as $item) {
        $items_count += $item['quantity'] ?? 1;
    }
    
    // Prepare order items as JSON
    $order_items = json_encode($items);
    
    // Insert order into database
    $stmt = $pdo->prepare("INSERT INTO orders (order_items, items_count, total_amount, order_date, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$order_items, $items_count, $total_value, $order_date]);
    
    $order_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order recorded successfully',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>