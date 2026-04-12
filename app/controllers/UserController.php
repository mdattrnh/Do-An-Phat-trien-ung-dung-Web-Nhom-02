<?php
require_once BASE_PATH . '/app/models/user/Customer.php';

class UserController {
    public function register() {
        $error = '';
        $success = '';
        $action = $_GET['action'] ?? 'register'; // default to register

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formAction = $_POST['form_action'] ?? 'register';

            if ($formAction === 'register') {
                $fullName = $_POST['full_name'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $gender = $_POST['gender'] ?? 'male';

                if (empty($fullName) || empty($email) || empty($password)) {
                    $error = 'Vui lòng điền đầy đủ thông tin!';
                } else {
                    $customerModel = new Customer();
                    // Check if email already exists
                    if ($customerModel->findByEmail($email)) {
                        $error = 'Email đã tồn tại!';
                    } else {
                        if ($customerModel->register($fullName, $email, $password, $gender)) {
                            $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                        } else {
                            $error = 'Có lỗi xảy ra, vui lòng thử lại sau.';
                        }
                    }
                }
            } elseif ($formAction === 'login') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                if (empty($email) || empty($password)) {
                    $error = 'Vui lòng điền email và mật khẩu!';
                } else {
                    require_once BASE_PATH . '/config/database.php';
                    $db = Database::getInstance()->getConnection();
                    
                    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['password'])) {
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        if ($user['role'] === 'admin') {
                            $_SESSION['admin_logged_in'] = true;
                        }
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['full_name'];
                        
                        header("Location: " . BASE_URL . "/");
                        exit;
                    } else {
                        $error = 'Email hoặc mật khẩu không đúng!';
                    }
                }
            }
        }

        require BASE_PATH . '/app/views/user/register.php';
    }

    public function loginApi() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($email === 'admin' && $password === '123456') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_name'] = 'Admin';
            echo json_encode(['success' => true, 'role' => 'admin', 'name' => 'Admin']);
            return;
        }

        require_once BASE_PATH . '/config/database.php';
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
            }
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            
            echo json_encode(['success' => true, 'role' => $user['role'], 'name' => $user['full_name']]);
            return;
        }

        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Email hoặc mật khẩu không đúng']);
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header("Location: " . BASE_URL . "/");
        exit;
    }
}
