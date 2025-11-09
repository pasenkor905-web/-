<?php
session_start();
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// ПРОВЕРКА АВТОРИЗАЦИИ
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $login_error = 'Неверные учетные данные';
        }
    }
    
    // ФОРМА ВХОДА ЕСЛИ НЕ АВТОРИЗИРОВАН 
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Вход в админ-панель</title>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                :root {
                    --mint: #43d8a4;
                    --mint-dark: #36c095;
                    --pink: #d47794;
                    --text-dark: #2c3e50;
                    --white: #ffffff;
                }
                body { 
                    font-family: 'Open Sans', sans-serif; 
                    background: linear-gradient(135deg, var(--mint), var(--pink));
                    height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .login-container {
                    background: var(--white);
                    padding: 50px 40px;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    width: 100%;
                    max-width: 450px;
                    text-align: center;
                }
                .login-title {
                    margin-bottom: 30px;
                    color: var(--text-dark);
                    font-family: 'Montserrat', sans-serif;
                    font-size: 2rem;
                    font-weight: 700;
                }
                .login-title i {
                    color: var(--mint);
                    margin-right: 10px;
                }
                .form-group { 
                    margin-bottom: 25px;
                    text-align: left;
                }
                .form-label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 600;
                    color: var(--text-dark);
                }
                .form-input {
                    width: 100%;
                    padding: 15px;
                    border: 2px solid #e0e0e0;
                    border-radius: 10px;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    font-family: 'Open Sans', sans-serif;
                }
                .form-input:focus {
                    outline: none;
                    border-color: var(--mint);
                    box-shadow: 0 0 0 3px rgba(67, 216, 164, 0.1);
                }
                .submit-btn {
                    width: 100%;
                    padding: 15px;
                    background: linear-gradient(135deg, var(--mint) 0%, var(--mint-dark) 100%);
                    color: white;
                    border: none;
                    border-radius: 10px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-weight: 600;
                    font-family: 'Montserrat', sans-serif;
                }
                .submit-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(67, 216, 164, 0.4);
                }
                .error { 
                    color: #e74c3c; 
                    text-align: center; 
                    margin-bottom: 20px; 
                    padding: 12px;
                    background: #ffeaea;
                    border-radius: 8px;
                    border-left: 4px solid #e74c3c;
                }
                .login-info {
                    margin-top: 25px;
                    color: #666;
                    font-size: 0.9rem;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1 class="login-title"><i class="fas fa-crown"></i> Админ-панель</h1>
                <?php if (isset($login_error)): ?>
                    <div class="error"><?= $login_error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Логин</label>
                        <input type="text" name="username" class="form-input" placeholder="Введите логин" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-input" placeholder="Введите пароль" required>
                    </div>
                    <button type="submit" class="submit-btn">Войти в панель управления</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Подключение к БД
$database = new Database();
$pdo = $database->getConnection();

// ОБРАБОТКА ДЕЙСТВИЙ АДМИНА
$action_message = '';

// Одобрить заявку
if (isset($_GET['approve_app'])) {
    $app_id = intval($_GET['approve_app']);
    try {
        $stmt = $pdo->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
        $stmt->execute([$app_id]);
        $action_message = 'Заявка одобрена!';
    } catch(PDOException $e) {
        $action_message = 'Ошибка при одобрении заявки';
    }
}

if (isset($_GET['reject_app'])) {
    $app_id = intval($_GET['reject_app']);
    try {
        $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
        $stmt->execute([$app_id]);
        $action_message = 'Заявка отклонена и удалена!';
    } catch(PDOException $e) {
        $action_message = 'Ошибка при отклонении заявки';
    }
}

if (isset($_GET['approve_review'])) {
    $review_id = intval($_GET['approve_review']);
    try {
        $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$review_id]);
        $action_message = 'Отзыв одобрен!';
    } catch(PDOException $e) {
        $action_message = 'Ошибка при одобрении отзыва';
    }
}

if (isset($_GET['delete_review'])) {
    $review_id = intval($_GET['delete_review']);
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $action_message = 'Отзыв удален!';
    } catch(PDOException $e) {
        $action_message = 'Ошибка при удалении отзыва';
    }
}

if (isset($_GET['mark_answered'])) {
    $contact_id = intval($_GET['mark_answered']);
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET is_answered = 1 WHERE id = ?");
        $stmt->execute([$contact_id]);
        $action_message = 'Сообщение помечено как отвеченное!';
    } catch(PDOException $e) {
        $action_message = 'Ошибка при обновлении сообщения';
    }
}

if (isset($_GET['delete_contact'])) {
    $contact_id = intval($_GET['delete_contact']);
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$contact_id]);
        $action_message = 'Сообщение удалено!';
    } catch(PDOException $e) {
        $action_message = 'Ошибка при удалении сообщения';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

try {
    $applications_stmt = $pdo->query("
        SELECT a.*, GROUP_CONCAT(c.name SEPARATOR ', ') as category_names 
        FROM applications a 
        LEFT JOIN application_categories ac ON a.id = ac.application_id 
        LEFT JOIN categories c ON ac.category_id = c.id 
        GROUP BY a.id 
        ORDER BY a.created_at DESC
    ");
    $applications = $applications_stmt->fetchAll();

    $reviews_stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
    $reviews = $reviews_stmt->fetchAll();

    $contacts_stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
    $contacts = $contacts_stmt->fetchAll();

    $total_applications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
    $pending_applications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'pending'")->fetchColumn();
    $total_reviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    $pending_reviews = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0")->fetchColumn();
    $total_contacts = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
    $unanswered_contacts = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_answered = 0")->fetchColumn();

} catch(PDOException $e) {
    die("Ошибка при получении данных: " . $e->getMessage());
}

$active_tab = $_GET['tab'] ?? 'applications';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Конкурс Прожектор</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        :root {
            --mint: #43d8a4;
            --mint-light: #e8f9f3;
            --mint-dark: #36c095;
            --pink: #d47794;
            --text-dark: #2c3e50;
            --text-light: #666;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
            --shadow: 0 2px 15px rgba(0,0,0,0.1);
            --shadow-hover: 0 8px 25px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background: var(--light-gray);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--text-dark) 0%, #34495e 100%);
            color: var(--white);
            padding: 0;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }

        .sidebar-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header h1 i {
            color: var(--mint);
            font-size: 1.6rem;
        }

        .nav-links {
            list-style: none;
            padding: 20px 0;
        }

        .nav-links li {
            margin: 5px 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--mint) 0%, var(--mint-dark) 100%);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(67, 216, 164, 0.3);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.2rem;
        }

        .badge {
            background: var(--pink);
            color: white;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-left: auto;
            min-width: 25px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        .badge-large {
            font-size: 1rem;
            padding: 6px 12px;
            min-width: 35px;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin: 20px 15px;
            border: 1px solid rgba(255,255,255,0.2);
            font-weight: 600;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border-color: var(--pink);
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
        }

        .header {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border-left: 5px solid var(--mint);
        }

        .header h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 4px solid var(--mint);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(67, 216, 164, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-family: 'Montserrat', sans-serif;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tab-content h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-container {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        thead {
            background: linear-gradient(135deg, var(--mint-light) 0%, #f0f9f5 100%);
            border-bottom: 2px solid var(--mint);
        }

        th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 700;
            color: var(--text-dark);
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: var(--mint-light);
            transform: scale(1.01);
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-approved {
            background: #d1edff;
            color: #0c5460;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 2px;
        }

        .btn-success {
            background: var(--mint);
            color: white;
        }

        .btn-success:hover {
            background: var(--mint-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 216, 164, 0.3);
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
        }

        .btn-info {
            background: #3498db;
            color: white;
        }

        .btn-info:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 2rem;
            color: var(--text-light);
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .modal-close:hover {
            color: var(--text-dark);
            background: var(--light-gray);
            transform: rotate(90deg);
        }

        .message-modal .modal-content {
            max-width: 600px;
            padding: 40px;
        }

        .message-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--mint-light);
        }

        .message-header h3 {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .message-meta {
            color: var(--text-light);
            font-size: 0.9rem;
            display: flex;
            gap: 20px;
        }

        .message-body {
            line-height: 1.8;
            color: var(--text-dark);
            font-size: 1.05rem;
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 12px;
            border-left: 4px solid var(--mint);
        }

        .application-modal .modal-content {
            max-width: 800px;
            padding: 40px;
        }

        .application-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .application-info h4,
        .application-files h4 {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--mint-light);
            padding-bottom: 8px;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--text-light);
            background: var(--light-gray);
            padding: 10px 15px;
            border-radius: 8px;
            border-left: 3px solid var(--mint);
        }

        .file-preview {
            text-align: center;
            padding: 20px;
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .file-preview:hover {
            border-color: var(--mint);
            background: var(--mint-light);
        }

        .file-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        /* ==================== ALERT STYLES ==================== */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            animation: slideInDown 0.5s ease;
            border-left: 4px solid;
        }

        .alert-success {
            background: var(--mint-light);
            color: #155724;
            border-color: var(--mint);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                padding: 20px;
            }
            
            .application-details {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
            
            .modal-content {
                max-width: 95%;
                margin: 20px;
            }
        }

        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h2 {
                font-size: 1.5rem;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--mint);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--mint-dark);
        }

        .review-text-preview {
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            line-height: 1.4;
        }

        .review-text-full {
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
        }

        .photo-modal {
            z-index: 3000;
        }

        .photo-modal .modal-content {
            background: transparent;
            box-shadow: none;
            max-width: 95vw;
            max-height: 95vh;
        }

        .photo-modal img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h1><i class="fas fa-crown"></i> Админ-панель</h1>
            </div>
            <ul class="nav-links">
                <li><a href="?tab=applications" class="nav-link <?= $active_tab === 'applications' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Заявки
                    <span class="badge <?= $pending_applications > 99 ? 'badge-large' : '' ?>">
                        <?= $pending_applications > 99 ? '99+' : $pending_applications ?>
                    </span>
                </a></li>
                <li><a href="?tab=reviews" class="nav-link <?= $active_tab === 'reviews' ? 'active' : '' ?>">
                    <i class="fas fa-star"></i> Отзывы
                    <span class="badge <?= $pending_reviews > 99 ? 'badge-large' : '' ?>">
                        <?= $pending_reviews > 99 ? '99+' : $pending_reviews ?>
                    </span>
                </a></li>
                <li><a href="?tab=contacts" class="nav-link <?= $active_tab === 'contacts' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Сообщения
                    <span class="badge <?= $unanswered_contacts > 99 ? 'badge-large' : '' ?>">
                        <?= $unanswered_contacts > 99 ? '99+' : $unanswered_contacts ?>
                    </span>
                </a></li>
            </ul>
            <a href="?logout=1" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Выйти
            </a>
        </div>

        <div class="main-content">
            <div class="header">
                <h2>Панель управления конкурсом</h2>
                <p>Добро пожаловать в админ-панель конкурса Прожектор</p>
            </div>

            <?php if ($action_message): ?>
            <div class="alert alert-success">
                <?= $action_message ?>
            </div>
            <?php endif; ?>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number"><?= $total_applications ?></div>
        <div class="stat-label">Всего заявок</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $pending_applications ?></div>
        <div class="stat-label">Ожидают рассмотрения</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $total_reviews ?></div>
        <div class="stat-label">Всего отзывов</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $total_contacts ?></div>
        <div class="stat-label">Сообщений</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $unanswered_contacts ?></div>
        <div class="stat-label">Не отвеченных</div>
    </div>
</div>

            <div id="applications" class="tab-content <?= $active_tab === 'applications' ? 'active' : '' ?>">
                <h3><i class="fas fa-users"></i> Заявки на участие</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ФИО</th>
                                <th>Телефон</th>
                                <th>Возраст</th>
                                <th>Категории</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($applications) > 0): ?>
                                <?php foreach($applications as $app): ?>
                                <tr>
                                    <td><?= $app['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($app['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($app['phone']) ?></td>
                                    <td><?= $app['age'] ?> лет</td>
                                    <td><?= $app['category_names'] ?? 'Не указаны' ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($app['created_at'])) ?></td>
                                    <td>
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <span class="status-pending">Ожидает</span>
                                        <?php elseif ($app['status'] === 'approved'): ?>
                                            <span class="status-approved">Одобрена</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-info" onclick="showApplication(<?= $app['id'] ?>, '<?= addslashes($app['full_name']) ?>', '<?= addslashes($app['phone']) ?>', <?= $app['age'] ?>, '<?= addslashes($app['category_names'] ?? 'Не указаны') ?>', '<?= addslashes($app['photo_path'] ?? '') ?>', '<?= addslashes($app['music_path'] ?? '') ?>', '<?= date('d.m.Y H:i', strtotime($app['created_at'])) ?>', '<?= $app['status'] ?>')">
                                            <i class="fas fa-eye"></i> Подробнее
                                        </button>
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <a href="?tab=applications&approve_app=<?= $app['id'] ?>" class="btn btn-success" title="Одобрить">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="?tab=applications&reject_app=<?= $app['id'] ?>" class="btn btn-danger" title="Отклонить" onclick="return confirm('Удалить эту заявку?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn" style="background: #95a5a6; color: white;">Одобрена</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                        <p>Нет заявок</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="reviews" class="tab-content <?= $active_tab === 'reviews' ? 'active' : '' ?>">
                <h3><i class="fas fa-star"></i> Отзывы участников</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Автор</th>
                                <th>Отзыв</th>
                                <th>Рейтинг</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reviews) > 0): ?>
                                <?php foreach($reviews as $review): ?>
                                <tr>
                                    <td><?= $review['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($review['author_name']) ?></strong></td>
                                    <td style="max-width: 300px;">
                                        <div class="review-text-preview">
                                            <?= htmlspecialchars($review['review_text']) ?>
                                        </div>
                                        <button class="btn btn-info" style="margin-top: 5px; font-size: 0.8rem;" 
                                                onclick="showReview(<?= $review['id'] ?>, '<?= addslashes($review['author_name']) ?>', `<?= addslashes($review['review_text']) ?>`, <?= $review['rating'] ?>, '<?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>', <?= $review['is_approved'] ?>)">
                                            <i class="fas fa-expand"></i> Полный текст
                                        </button>
                                    </td>
                                    <td>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $review['rating'] ? 'active' : '' ?>" style="color: <?= $i <= $review['rating'] ? '#ffc107' : '#e0e0e0' ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                                    <td>
                                        <?php if ($review['is_approved']): ?>
                                            <span class="status-approved">Одобрен</span>
                                        <?php else: ?>
                                            <span class="status-pending">На модерации</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$review['is_approved']): ?>
                                            <a href="?tab=reviews&approve_review=<?= $review['id'] ?>" class="btn btn-success" title="Одобрить">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?tab=reviews&delete_review=<?= $review['id'] ?>" class="btn btn-danger" title="Удалить" onclick="return confirm('Удалить этот отзыв?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                        <p>Нет отзывов</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="contacts" class="tab-content <?= $active_tab === 'contacts' ? 'active' : '' ?>">
                <h3><i class="fas fa-envelope"></i> Сообщения от пользователей</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Сообщение</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($contacts) > 0): ?>
                                <?php foreach($contacts as $contact): ?>
                                <tr>
                                    <td><?= $contact['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($contact['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($contact['email']) ?></td>
                                    <td style="max-width: 300px;">
                                        <div style="max-height: 60px; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($contact['message']) ?>
                                        </div>
                                        <button class="btn btn-info" style="margin-top: 5px; font-size: 0.8rem;" 
                                                onclick="showMessage(<?= $contact['id'] ?>, '<?= addslashes($contact['name']) ?>', '<?= addslashes($contact['email']) ?>', `<?= addslashes($contact['message']) ?>`, '<?= date('d.m.Y H:i', strtotime($contact['created_at'])) ?>')">
                                            <i class="fas fa-expand"></i> Подробнее
                                        </button>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($contact['created_at'])) ?></td>
                                    <td>
                                        <?php if ($contact['is_answered']): ?>
                                            <span class="status-approved">Отвечено</span>
                                        <?php else: ?>
                                            <span class="status-pending">Не отвечено</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$contact['is_answered']): ?>
                                            <a href="?tab=contacts&mark_answered=<?= $contact['id'] ?>" class="btn btn-success" title="Пометить как отвеченное">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="btn btn-warning" title="Ответить">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        <a href="?tab=contacts&delete_contact=<?= $contact['id'] ?>" class="btn btn-danger" title="Удалить" onclick="return confirm('Удалить это сообщение?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        <i class="fas fa-envelope" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                        <p>Нет сообщений</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="photoModal" class="modal photo-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closePhotoModal()">&times;</span>
            <img id="modalPhoto" src="" alt="Фото участника" style="max-width: 100%; max-height: 80vh; display: block; margin: 0 auto;">
        </div>
    </div>

    <div id="musicModal" class="modal">
        <div class="modal-content" style="padding: 20px; text-align: center;">
            <span class="modal-close" onclick="closeMusicModal()">&times;</span>
            <h3 style="margin-bottom: 20px;">Аудио участника</h3>
            <audio id="modalAudio" controls style="width: 100%; max-width: 500px;">
                Ваш браузер не поддерживает аудио элементы.
            </audio>
        </div>
    </div>

    <div id="messageModal" class="modal message-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeMessageModal()">&times;</span>
            <div class="message-header">
                <h3 id="messageAuthor"></h3>
                <div class="message-meta">
                    <span id="messageEmail"></span>
                    <span id="messageDate"></span>
                </div>
            </div>
            <div class="message-body" id="messageText"></div>
            <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                <a id="messageReply" class="btn btn-warning">
                    <i class="fas fa-reply"></i> Ответить
                </a>
                <button class="btn" onclick="closeMessageModal()" style="background: var(--light-gray);">
                    <i class="fas fa-times"></i> Закрыть
                </button>
            </div>
        </div>
    </div>

    <div id="applicationModal" class="modal application-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeApplicationModal()">&times;</span>
            <h3 style="margin-bottom: 25px; font-family: 'Montserrat', sans-serif;">Детали заявки</h3>
            
            <div class="application-details">
                <div class="application-info">
                    <h4>Информация об участнике</h4>
                    <div class="info-group">
                        <div class="info-label">ФИО:</div>
                        <div class="info-value" id="appName"></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Телефон:</div>
                        <div class="info-value" id="appPhone"></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Возраст:</div>
                        <div class="info-value" id="appAge"></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Категории:</div>
                        <div class="info-value" id="appCategories"></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Дата подачи:</div>
                        <div class="info-value" id="appDate"></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Статус:</div>
                        <div class="info-value" id="appStatus"></div>
                    </div>
                </div>
                
                <div class="application-files">
                    <h4>Загруженные файлы</h4>
                    <div class="file-preview" id="appPhotoPreview">
                    </div>
                    <div class="file-preview" id="appAudioPreview">
                    </div>
                </div>
            </div>
            
            <div id="appActions" style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--border-color); padding-top: 20px;">
            </div>
        </div>
    </div>

    <div id="reviewModal" class="modal message-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeReviewModal()">&times;</span>
            <div class="message-header">
                <h3 id="reviewAuthor"></h3>
                <div class="message-meta">
                    <span>Рейтинг: <span id="reviewRating"></span></span>
                    <span id="reviewDate"></span>
                </div>
            </div>
            <div class="message-body review-text-full" id="reviewText"></div>
            <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                <button class="btn" onclick="closeReviewModal()" style="background: var(--light-gray);">
                    <i class="fas fa-times"></i> Закрыть
                </button>
            </div>
        </div>
    </div>

    <script>
        function showPhoto(photoPath) {
            document.getElementById('modalPhoto').src = photoPath;
            document.getElementById('photoModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function playMusic(musicPath) {
            const audio = document.getElementById('modalAudio');
            audio.src = musicPath;
            document.getElementById('musicModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            audio.play().catch(e => {
                console.log('Автовоспроизведение заблокировано браузером');
            });
        }

        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function closeMusicModal() {
            const audio = document.getElementById('modalAudio');
            audio.pause();
            audio.currentTime = 0;
            document.getElementById('musicModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showMessage(messageId, author, email, message, date) {
            const modal = document.getElementById('messageModal');
            document.getElementById('messageAuthor').textContent = author;
            document.getElementById('messageEmail').textContent = email;
            document.getElementById('messageDate').textContent = date;
            document.getElementById('messageText').textContent = message;
            
            const replyBtn = document.getElementById('messageReply');
            replyBtn.href = `mailto:${email}?subject=Ответ на ваше сообщение&body=Здравствуйте, ${author}!%0D%0A%0D%0A`;
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function showReview(reviewId, author, text, rating, date, isApproved) {
            const modal = document.getElementById('reviewModal');
            document.getElementById('reviewAuthor').textContent = author;
            document.getElementById('reviewRating').innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElement('i');
                star.className = `fas fa-star ${i <= rating ? 'active' : ''}`;
                star.style.color = i <= rating ? '#ffc107' : '#e0e0e0';
                star.style.marginRight = '2px';
                document.getElementById('reviewRating').appendChild(star);
            }
            document.getElementById('reviewDate').textContent = date;
            document.getElementById('reviewText').textContent = text;
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function showApplication(appId, name, phone, age, categories, photoPath, musicPath, date, status) {
            const modal = document.getElementById('applicationModal');
            
            document.getElementById('appName').textContent = name;
            document.getElementById('appPhone').textContent = phone;
            document.getElementById('appAge').textContent = age + ' лет';
            document.getElementById('appCategories').textContent = categories;
            document.getElementById('appDate').textContent = date;
            document.getElementById('appStatus').textContent = status === 'pending' ? 'Ожидает рассмотрения' : 'Одобрена';
            
            const photoPreview = document.getElementById('appPhotoPreview');
            if (photoPath && photoPath !== '') {
                photoPreview.innerHTML = `
                    <img src="${photoPath}" alt="Фото участника" 
                         onclick="showPhoto('${photoPath}')" 
                         style="max-width: 100%; border-radius: 8px; cursor: pointer;"
                         onerror="this.style.display='none'; photoPreview.innerHTML='<p style=\\'color: var(--text-light); text-align: center;\\'>Ошибка загрузки фото</p>'">
                    <div style="margin-top: 10px;">
                        <button class="btn btn-info" onclick="showPhoto('${photoPath}')">
                            <i class="fas fa-expand"></i> Увеличить фото
                        </button>
                    </div>
                `;
            } else {
                photoPreview.innerHTML = '<p style="color: var(--text-light); text-align: center;">Фото не загружено</p>';
            }
            
            const audioPreview = document.getElementById('appAudioPreview');
            if (musicPath && musicPath !== '') {
                audioPreview.innerHTML = `
                    <audio controls style="width: 100%;">
                        <source src="${musicPath}" type="audio/mpeg">
                        Ваш браузер не поддерживает аудио элементы.
                    </audio>
                    <div style="margin-top: 10px;">
                        <button class="btn btn-info" onclick="playMusic('${musicPath}')">
                            <i class="fas fa-play"></i> Прослушать
                        </button>
                    </div>
                `;
            } else {
                audioPreview.innerHTML = '<p style="color: var(--text-light); text-align: center;">Аудио не загружено</p>';
            }
            
            const actionButtons = document.getElementById('appActions');
            if (status === 'pending') {
                actionButtons.innerHTML = `
                    <a href="?tab=applications&approve_app=${appId}" class="btn btn-success">
                        <i class="fas fa-check"></i> Одобрить
                    </a>
                    <a href="?tab=applications&reject_app=${appId}" class="btn btn-danger" onclick="return confirm('Удалить эту заявку?')">
                        <i class="fas fa-times"></i> Отклонить
                    </a>
                `;
            } else {
                actionButtons.innerHTML = '<span class="btn" style="background: #95a5a6; color: white;">Заявка одобрена</span>';
            }
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function closeApplicationModal() {
            document.getElementById('applicationModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.addEventListener('click', function(event) {
            const modals = ['photoModal', 'musicModal', 'messageModal', 'applicationModal', 'reviewModal'];
            modals.forEach(modalId => {
                if (event.target === document.getElementById(modalId)) {
                    if (modalId === 'photoModal') closePhotoModal();
                    if (modalId === 'musicModal') closeMusicModal();
                    if (modalId === 'messageModal') closeMessageModal();
                    if (modalId === 'applicationModal') closeApplicationModal();
                    if (modalId === 'reviewModal') closeReviewModal();
                }
            });
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePhotoModal();
                closeMusicModal();
                closeMessageModal();
                closeApplicationModal();
                closeReviewModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                table.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
            
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.stat-card, .table-container');
                elements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.style.opacity = "1";
                        element.style.transform = "translateY(0)";
                    }
                });
            };
            
            const animatedElements = document.querySelectorAll('.stat-card, .table-container');
            animatedElements.forEach(element => {
                element.style.opacity = "0";
                element.style.transform = "translateY(20px)";
                element.style.transition = "opacity 0.6s ease, transform 0.6s ease";
            });
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll();
        });
    </script>
</body>
</html>