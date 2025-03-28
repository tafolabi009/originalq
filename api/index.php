<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include configuration and helper files
require_once 'config.php';
require_once 'database.php';
require_once 'helpers.php';

// Include controllers
require_once 'controllers/AuthController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/TeacherController.php';
require_once 'controllers/StudentController.php';
require_once 'controllers/MessageController.php';
require_once 'controllers/SessionController.php';
require_once 'controllers/PaymentController.php';
require_once 'controllers/AdminController.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Initialize controllers
$authController = new AuthController($conn);
$userController = new UserController($conn);
$teacherController = new TeacherController($conn);
$studentController = new StudentController($conn);
$messageController = new MessageController($conn);
$sessionController = new SessionController($conn);
$paymentController = new PaymentController($conn);
$adminController = new AdminController($conn);

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/api/', '', $path);
$path = rtrim($path, '/');

// Simple router
switch ($path) {
    // Auth routes
    case 'ping':
        echo json_encode(['status' => 'success', 'message' => 'API is running']);
        break;
    case 'register':
        $authController->register();
        break;
    case 'login':
        $authController->login();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'forgot-password':
        $authController->forgotPassword();
        break;
    case 'reset-password':
        $authController->resetPassword();
        break;
    
    // User routes
    case 'user':
        $userController->getUser();
        break;
    case 'user/update':
        $userController->updateUser();
        break;
    case 'user/change-password':
        $userController->changePassword();
        break;
    
    // Teacher routes
    case 'teacher/profile':
        $teacherController->getProfile();
        break;
    case 'teacher/update':
        $teacherController->updateProfile();
        break;
    case 'teacher/verification':
        $teacherController->getVerificationStatus();
        break;
    case 'teacher/verification/update':
        $teacherController->updateVerificationStatus();
        break;
    case 'teacher/dashboard':
        $teacherController->getDashboardStats();
        break;
    case 'teacher/sessions':
        $teacherController->getUpcomingSessions();
        break;
    case 'teacher/recommended-students':
        $teacherController->getRecommendedStudents();
        break;
    case 'teacher/requests':
        $teacherController->getStudentRequests();
        break;
    case 'teacher/requests/accept':
        $teacherController->acceptStudentRequest();
        break;
    case 'teacher/requests/decline':
        $teacherController->declineStudentRequest();
        break;
    
    // Student routes
    case 'student/profile':
        $studentController->getProfile();
        break;
    case 'student/update':
        $studentController->updateProfile();
        break;
    case 'student/dashboard':
        $studentController->getDashboardStats();
        break;
    case 'student/teachers':
        $studentController->getTeachers();
        break;
    case 'student/sessions':
        $studentController->getUpcomingSessions();
        break;
    case 'student/request':
        $studentController->sendRequest();
        break;
    
    // Message routes
    case 'messages':
        $messageController->getMessages();
        break;
    case 'messages/send':
        $messageController->sendMessage();
        break;
    
    // Session routes
    case 'sessions':
        $sessionController->getSessions();
        break;
    case 'sessions/create':
        $sessionController->createSession();
        break;
    case 'sessions/update':
        $sessionController->updateSession();
        break;
    case 'sessions/cancel':
        $sessionController->cancelSession();
        break;
    
    // Payment routes
    case 'payments':
        $paymentController->getPayments();
        break;
    case 'payments/create':
        $paymentController->createPayment();
        break;
    
    // Admin routes
    case 'admin/users':
        $adminController->getUsers();
        break;
    case 'admin/teachers':
        $adminController->getTeachers();
        break;
    case 'admin/students':
        $adminController->getStudents();
        break;
    case 'admin/verification-requests':
        $adminController->getVerificationRequests();
        break;
    case 'admin/schedule-verification':
        $adminController->scheduleVerification();
        break;
    case 'admin/complete-verification':
        $adminController->completeVerification();
        break;
    
    // Default route
    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        break;
}

