<?php
header('Content-Type: application/json');
require_once 'config.php';

// Get user ID from session (you'll implement authentication later)
$user_id = 1; // Default user for now

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Get all notes or filter by status
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        
        $sql = "SELECT * FROM notes WHERE user_id = :user_id";
        if ($status && $status !== 'all') {
            $sql .= " AND status = :status";
        }
        $sql .= " ORDER BY date_created DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        if ($status && $status !== 'all') {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($notes);
        break;
        
    case 'POST':
        // Create a new note
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['title']) || !isset($data['content'])) {
            echo json_encode(['error' => 'Title and content are required']);
            exit;
        }
        
        $sql = "INSERT INTO notes (title, content, status, date_created, user_id) 
                VALUES (:title, :content, 'normal', NOW(), :user_id)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->lastInsertId()]);
        } else {
            echo json_encode(['error' => 'Failed to create note']);
        }
        break;
        
    case 'PUT':
        // Update a note (status)
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['status'])) {
            echo json_encode(['error' => 'Note ID and status are required']);
            exit;
        }
        
        $sql = "UPDATE notes SET status = :status WHERE id = :id AND user_id = :user_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to update note']);
        }
        break;
        
    case 'DELETE':
        // Delete a note
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            echo json_encode(['error' => 'Note ID is required']);
            exit;
        }
        
        $sql = "DELETE FROM notes WHERE id = :id AND user_id = :user_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to delete note']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Method not supported']);
        break;
}
?>