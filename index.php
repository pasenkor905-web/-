<?php
session_start();
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

createUploadDirs();

$database = new Database();
$pdo = $database->getConnection();


if ($pdo) {
    logAction("Подключение к БД установлено");
} else {
    logAction("Ошибка подключения к БД");
}

// ОБРАБОТКА ФОРМЫ РЕГИСТРАЦИИ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'])) {
    logAction("Начата обработка формы регистрации");
    
    if ($pdo) {
        try {
            $fullname = trim($_POST['fullname']);
            $phone = trim($_POST['phone']);
            $age = intval($_POST['age']);
            $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
            
            logAction("Данные формы: name=$fullname, phone=$phone, age=$age, categories=" . count($categories));
            
            // ВАЛИДАЦИЯ ДАННЫХ
            if (empty($fullname) || empty($phone) || $age < 16 || $age > 99) {
                throw new Exception("Неверные данные формы");
            }
            
            // ЗАГРУЗКА ФАЙЛОВ
            $photo_path = '';
            $music_path = '';
            
            // ФОТО
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo_path = handleFileUpload($_FILES['photo'], 'photo');
                logAction("Фото загружено: " . ($photo_path ? 'успешно' : 'ошибка'));
            }
            
            // МУЗЫКА
            if (isset($_FILES['music']) && $_FILES['music']['error'] === UPLOAD_ERR_OK) {
                $music_path = handleFileUpload($_FILES['music'], 'music');
                logAction("Музыка загружена: " . ($music_path ? 'успешно' : 'ошибка'));
            }
            
            $pdo->beginTransaction();
        
            $stmt = $pdo->prepare("INSERT INTO applications (full_name, phone, age, photo_path, music_path, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$fullname, $phone, $age, $photo_path, $music_path]);
            
            $application_id = $pdo->lastInsertId();
            logAction("Заявка сохранена, ID: " . $application_id);
            
            foreach ($categories as $category_id) {
                $stmt = $pdo->prepare("INSERT INTO application_categories (application_id, category_id) VALUES (?, ?)");
                $stmt->execute([$application_id, $category_id]);
                logAction("Добавлена категория: " . $category_id);
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = 'Регистрация успешно завершена! Мы свяжемся с вами в ближайшее время.';
            logAction("Регистрация завершена успешно");
            
            header('Location: ' . $_SERVER['PHP_SELF'] . '#registration');
            exit;
            
        } catch(PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error'] = 'Ошибка при регистрации. Попробуйте еще раз.';
            logAction("Ошибка БД при регистрации: " . $e->getMessage());
        } catch(Exception $e) {
            $_SESSION['error'] = 'Ошибка при обработке данных. Проверьте введенные данные.';
            logAction("Ошибка валидации: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = 'Ошибка подключения к базе данных. Попробуйте позже.';
        logAction("Нет подключения к БД при регистрации");
    }
}

// ОБРАБОТКА ФОРМЫ ОТЗЫВОВ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['author_name'])) {
    logAction("Начата обработка формы отзыва");
    
    if ($pdo) {
        try {
            $author_name = trim($_POST['author_name']);
            $review_text = trim($_POST['review_text']);
            $rating = intval($_POST['rating']);
            
            // Валидация
            if (empty($author_name) || empty($review_text) || $rating < 1 || $rating > 5) {
                throw new Exception("Неверные данные отзыва");
            }
            
            $stmt = $pdo->prepare("INSERT INTO reviews (author_name, review_text, rating, is_approved) VALUES (?, ?, ?, 1)");
            $stmt->execute([$author_name, $review_text, $rating]);
            
            $_SESSION['success'] = 'Отзыв успешно отправлен! Спасибо за ваш отзыв.';
            logAction("Отзыв сохранен: " . $author_name);
            
            header('Location: ' . $_SERVER['PHP_SELF'] . '#reviews');
            exit;
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Ошибка при отправке отзыва. Попробуйте еще раз.';
            logAction("Ошибка БД при сохранении отзыва: " . $e->getMessage());
        } catch(Exception $e) {
            $_SESSION['error'] = 'Ошибка при обработке отзыва. Проверьте введенные данные.';
            logAction("Ошибка валидации отзыва: " . $e->getMessage());
        }
    }
}

// ОБРАБОТКА ФОРМЫ КОНТАКТОВ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_name'])) {
    logAction("Начата обработка формы контактов");
    
    if ($pdo) {
        try {
            $name = trim($_POST['contact_name']);
            $email = trim($_POST['contact_email']);
            $message = trim($_POST['contact_message']);
            
            // Валидация email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Неверный email");
            }
            
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);
            
            $_SESSION['success'] = 'Сообщение успешно отправлено! Мы ответим вам в ближайшее время.';
            logAction("Сообщение сохранено: " . $email);
            
            header('Location: ' . $_SERVER['PHP_SELF'] . '#contacts');
            exit;
            
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Ошибка при отправке сообщения. Попробуйте еще раз.';
            logAction("Ошибка БД при сохранении сообщения: " . $e->getMessage());
        } catch(Exception $e) {
            $_SESSION['error'] = 'Ошибка при обработке сообщения. Проверьте введенные данные.';
            logAction("Ошибка валидации сообщения: " . $e->getMessage());
        }
    }
}


$reviews = [];
$categories = [];

if ($pdo) {
    try {
   
        $reviews_stmt = $pdo->query("SELECT * FROM reviews WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 6");
        $reviews = $reviews_stmt->fetchAll();
        logAction("Загружено отзывов: " . count($reviews));
        
     
        $categories_stmt = $pdo->query("SELECT * FROM categories");
        $categories = $categories_stmt->fetchAll();
        logAction("Загружено категорий: " . count($categories));
        
    } catch(PDOException $e) {
        logAction("Ошибка загрузки данных: " . $e->getMessage());
    }
} else {
    logAction("Нет подключения к БД для загрузки данных");
}


$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';


unset($_SESSION['success']);
unset($_SESSION['error']);


logAction("Страница загружена. Сообщения: success=" . ($success_message ? 'есть' : 'нет') . ", error=" . ($error_message ? 'есть' : 'нет'));
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Конкурс Прожектор</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-container">
                <div class="nav-logo">
                    <h2>КонкурсПрожектор</h2>
                </div>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="#about" class="nav-link">О конкурсе</a></li>
                    <li><a href="#registration" class="nav-link">Регистрация</a></li>
                    <li><a href="#gallery" class="nav-link">Галерея</a></li>
                    <li><a href="#contacts" class="nav-link">Контакты</a></li>
                    <li><a href="#reviews" class="nav-link">Отзывы</a></li>
                    <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                        <li><a href="admin.php" class="nav-link" style="color: var(--pink); font-weight: 700;">Админ-панель</a></li>
                    <?php endif; ?>
                </ul>
                <div class="nav-toggle" id="navToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <!-- ==================== BANNER SECTION ==================== -->
    <section class="banner-section" id="home">
        <div class="background-container">
            <img src="img/model2.png" alt="Фон конкурса" class="background-image">
        </div>
        <div class="banner-overlay"></div>
        
        <div class="container">
            <div class="banner-content">
                <div class="banner-card fade-in-up">
                    <h2>В центре <br> внимания</h2>
                    <p>Присоединяйтесь к конкурсу дефиле и фото. <br> Покажите свой стиль и талант на подиуме!</p>
                    <a href="#registration" class="banner-btn">Зарегистрироваться</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== ABOUT SECTION ==================== -->
    <section class="section" id="about">
        <div class="container">
            <h2 class="section-title">О конкурсе</h2>
            
            <div class="benefits">
                <div class="benefit-card">
                    <div class="icon-circle">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Признание и награды</h3>
                    <p>Получите заслуженное признание своего таланта и профессиональные награды от жюри.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="icon-circle">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3>Профессиональная съёмка</h3>
                    <p>Работайте с опытными фотографами и получите качественное портфолио для карьеры.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="icon-circle">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>Новые возможности</h3>
                    <p>Откройте двери к новым проектам, контрактам и сотрудничеству в индустрии моды.</p>
                </div>
            </div>

            <!-- Categories - УЛУЧШЕННЫЙ ВАРИАНТ -->
            <div class="categories-section">
                <h2 class="section-title">Категории конкурса</h2>
                <p style="text-align: center; margin-bottom: 40px; color: var(--text-light); font-size: 1.1rem;">
                    Выберите подходящие направления для участия в конкурсе
                </p>
                
                <div class="categories">
                    <div class="category-card fade-in-up">
                        <img src="img/Defile.png" alt="Дефиле" class="category-image">
                        <div class="category-label" onclick="toggleDescription('desc1')">
                            <i class="fas fa-walking"></i> Дефиле
                        </div>
                        <div class="category-description" id="desc1">
                            <p>Продемонстрируйте свои навыки на подиуме перед профессиональным жюри. Участники будут оцениваться по походке, пластике, умению презентовать одежду и общему впечатлению.</p>
                        </div>
                    </div>
                    
                    <div class="category-card fade-in-up">
                        <img src="img/photo.png" alt="Фото" class="category-image">
                        <div class="category-label" onclick="toggleDescription('desc2')">
                            <i class="fas fa-camera"></i> Фото
                        </div>
                        <div class="category-description" id="desc2">
                            <p>Участвуйте в фотосессиях с профессиональными фотографами. Ваши работы будут оцениваться по композиции, эмоциональной выразительности, оригинальности и техническому качеству.</p>
                        </div>
                    </div>

                    <div class="category-card fade-in-up">
                        <img src="img/obras.png" alt="Креативный образ" class="category-image">
                        <div class="category-label" onclick="toggleDescription('desc3')">
                            <i class="fas fa-palette"></i> Креативный образ
                        </div>
                        <div class="category-description" id="desc3">
                            <p>Проявите свою фантазию и создайте уникальный образ. Участники оцениваются по оригинальности концепции, гармоничности сочетания элементов и общему визуальному впечатлению.</p>
                        </div>
                    </div>

                    <div class="category-card fade-in-up">
                        <img src="img/art.png" alt="Артистизм" class="category-image">
                        <div class="category-label" onclick="toggleDescription('desc4')">
                            <i class="fas fa-star"></i> Артистизм
                        </div>
                        <div class="category-description" id="desc4">
                            <p>Продемонстрируйте свой артистический талант и эмоциональную выразительность. Критерии оценки: харизма, способность передавать эмоции, сценическое присутствие и оригинальность.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== REGISTRATION SECTION ==================== -->
    <section class="section" id="registration">
        <div class="container">
            <h2 class="section-title">Регистрация на конкурс</h2>
            
            <form class="registration-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fullname" class="form-label">ФИО *</label>
                    <input type="text" id="fullname" name="fullname" class="form-input" placeholder="Иванов Иван Иванович" required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Номер телефона *</label>
                    <input type="tel" id="phone" name="phone" class="form-input" placeholder="+7 (999) 123-45-67" required>
                </div>
                
                <div class="form-group">
                    <label for="age" class="form-label">Возраст *</label>
                    <input type="number" id="age" name="age" class="form-input" placeholder="18" min="16" max="99" required>
                </div>
                
                <div style="height: 1px; background: #e0e0e0; margin: 40px 0;"></div>
                
                <div class="form-group">
                    <label class="form-label">Выберите конкурсы для участия *</label>
                    <div class="checkbox-group">
                        <?php foreach($categories as $category): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="categories[]" value="<?= $category['id'] ?>">
                            <span><?= htmlspecialchars($category['name']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="height: 1px; background: #e0e0e0; margin: 40px 0;"></div>
                
                <div class="form-group">
                    <label class="form-label">Загрузите фото *</label>
                    <div class="file-upload" onclick="document.getElementById('photo').click()">
                        <i class="fas fa-camera"></i>
                        <p>Нажмите для загрузки фото</p>
                        <input type="file" id="photo" name="photo" accept="image/*" style="display: none;" required>
                        <div class="file-info" id="photoInfo">Не выбран ни один файл</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Загрузите музыку</label>
                    <div class="file-upload" onclick="document.getElementById('music').click()">
                        <i class="fas fa-music"></i>
                        <p>Нажмите для загрузки музыки</p>
                        <input type="file" id="music" name="music" accept="audio/*" style="display: none;">
                        <div class="file-info" id="musicInfo">Не выбран ни один файл</div>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Зарегистрироваться</button>
            </form>
        </div>
    </section>

    <!-- ==================== GALLERY SECTION ==================== -->
    <section class="section" id="gallery">
        <div class="container">
            <h2 class="section-title">Галерея звезд</h2>
            <p style="text-align: center; margin-bottom: 40px; color: var(--text-light);">
                Познакомьтесь с нашими талантливыми участниками и победителями конкурса
            </p>
            
            <div class="gallery">
                <div class="gallery-item">
                    <img src="img/ogo.jpg" alt="Мария Барашко" class="gallery-img">
                    <div class="gallery-overlay">
                        <h3 class="gallery-title">Мария Барашко</h3>
                        <p class="gallery-description">Победитель в номинации "Лучший вокал"</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="img/ogo2.jpg" alt="Кирилл Олухов" class="gallery-img">
                    <div class="gallery-overlay">
                        <h3 class="gallery-title">Кирилл Олухов</h3>
                        <p class="gallery-description">Лауреат конкурса современного танца</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="img/летун.jpg" alt="Ночной Летун" class="gallery-img">
                    <div class="gallery-overlay">
                        <h3 class="gallery-title">Ночной Летун</h3>
                        <p class="gallery-description">Призер в номинации "Вселенская олимпиада по философии"</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="img/ogo5.jpg" alt="Дмитрий Козлов" class="gallery-img">
                    <div class="gallery-overlay">
                        <h3 class="gallery-title">Дмитрий Козлов</h3>
                        <p class="gallery-description">Победитель в номинации "Оригинальный жанр"</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="img/совет.jpg" alt="София Морозова" class="gallery-img">
                    <div class="gallery-overlay">
                        <h3 class="gallery-title">Вася Хоффманн</h3>
                        <p class="gallery-description">Лауреат конкурса Союза</p>
                    </div>
                </div>
                
                <div class="gallery-item">
                    <img src="img/макан.jpg" alt="Андрей Кто" class="gallery-img">
                    <div class="gallery-overlay">
                        <h3 class="gallery-title">Андрей Кто</h3>
                        <p class="gallery-description">Победитель в номинации "Лучший Макан"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== REVIEWS SECTION ==================== -->
    <section class="section" id="reviews">
        <div class="container">
            <h2 class="section-title">Отзывы участников</h2>
            <p style="text-align: center; margin-bottom: 40px; color: var(--text-light);">
                Узнайте, что говорят участники наших конкурсов о своем опыте
            </p>
            
            <div class="reviews-grid">
                <?php if(count($reviews) > 0): ?>
                    <?php foreach($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-text">
                            "<?= htmlspecialchars($review['review_text']) ?>"
                        </div>
                        <div class="review-author">
                            <strong><?= htmlspecialchars($review['author_name']) ?></strong>
                            <div class="review-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'active' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-light);">
                        <p>Пока нет отзывов. Будьте первым!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Add Review Form -->
            <div class="add-review-section">
                <h3 style="text-align: center; margin-bottom: 30px;">Оставьте свой отзыв</h3>
                <p style="text-align: center; margin-bottom: 30px; color: var(--text-light);">
                    Поделитесь впечатлениями о конкурсе Прожектор
                </p>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Ваша оценка</label>
                        <div class="rating-stars">
                            <i class="fas fa-star active" onclick="setRating(1)"></i>
                            <i class="fas fa-star active" onclick="setRating(2)"></i>
                            <i class="fas fa-star active" onclick="setRating(3)"></i>
                            <i class="fas fa-star active" onclick="setRating(4)"></i>
                            <i class="fas fa-star active" onclick="setRating(5)"></i>
                        </div>
                        <input type="hidden" id="rating" name="rating" value="5" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="author_name" class="form-label">Ваше имя *</label>
                        <input type="text" id="author_name" name="author_name" class="form-input" placeholder="Введите ваше имя" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="review_text" class="form-label">Ваш отзыв *</label>
                        <textarea id="review_text" name="review_text" class="form-input form-textarea" placeholder="Расскажите о своих впечатлениях..." required></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">Отправить отзыв</button>
                </form>
            </div>
        </div>
    </section>

    <!-- ==================== CONTACTS SECTION ==================== -->
    <section class="section" id="contacts">
        <div class="container">
            <h2 class="section-title">Свяжитесь с нами</h2>
            
            <div class="contact-info">
                <div class="contact-card">
                    <i class="fas fa-map-marker-alt contact-icon"></i>
                    <h3>Адрес</h3>
                    <p>г. Барнаул, пр. Ленина, 61</p>
                </div>
                
                <div class="contact-card">
                    <i class="fas fa-phone contact-icon"></i>
                    <h3>Телефон</h3>
                    <p>+7 (3852) 12-34-56</p>
                </div>
                
                <div class="contact-card">
                    <i class="fas fa-envelope contact-icon"></i>
                    <h3>Email</h3>
                    <p>info@projektor.ru</p>
                </div>
                
                <div class="contact-card">
                    <i class="fas fa-clock contact-icon"></i>
                    <h3>Время работы</h3>
                    <p>Пн-Пт: 9:00 - 18:00</p>
                    <p>Сб-Вс: 10:00 - 16:00</p>
                </div>
            </div>
            
            <form class="contact-form" method="POST">
                <div class="form-group">
                    <label for="contact_name" class="form-label">Имя *</label>
                    <input type="text" id="contact_name" name="contact_name" class="form-input" placeholder="Ваше имя" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email" class="form-label">Email *</label>
                    <input type="email" id="contact_email" name="contact_email" class="form-input" placeholder="you@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message" class="form-label">Сообщение *</label>
                    <textarea id="contact_message" name="contact_message" class="form-input form-textarea" placeholder="Ваше сообщение..." required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Отправить сообщение</button>
            </form>
            
            <!-- Яндекс Карта -->
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER SECTION ==================== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>КонкурсПрожектор</h3>
                    <p>Раскрой свой талант в самом масштабном творческом конкурсе года! Покажи свои способности, получи признание и ценные призы.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-vk"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-telegram"></i>
                        </a>

                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Быстрые ссылки</h3>
                    <a href="#about">О конкурсе</a>
                    <a href="#registration">Регистрация</a>
                    <a href="#gallery">Галерея</a>
                    <a href="#reviews">Отзывы</a>
                    <a href="#contacts">Контакты</a>
                </div>
                
                <div class="footer-section">
                    <h3>Категории</h3>
                    <a href="#about">Дефиле</a>
                    <a href="#about">Фото</a>
                    <a href="#about">Креативный образ</a>
                    <a href="#about">Артистизм</a>
                </div>
                
                <div class="footer-section">
                    <h3>Контакты</h3>
                    <p><i class="fas fa-map-marker-alt"></i> г. Барнаул, пр. Ленина, 61</p>
                    <p><i class="fas fa-phone"></i> +7 (3852) 12-34-56</p>
                    <p><i class="fas fa-envelope"></i> info@projektor.ru</p>
                    <p><i class="fas fa-clock"></i> Пн-Пт: 9:00-18:00</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 КонкурсПрожектор. Все права защищены.</p>
                <div class="footer-links">
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>