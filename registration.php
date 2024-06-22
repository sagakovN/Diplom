<?php
session_start();
require("pdo_connect.php");
require("checking_form_submit.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_css/index_registration_CSS.css">
    <title>Регистрация</title>
</head>
<body>
    <div class="container_registration">
        <h2>Регистрация</h2>
        <form action="registration.php" method="post" class="form_registration">
            <input type="text" name="login" id="login" placeholder="Логин" value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>">
            
            <input type="password" name="password" id="password" placeholder="Пароль" oninput="checkPasswordStrength()">
            <small id="password_requirements" class="password_requirements">Пароль должен быть минимум 8 символов, содержать хотя бы одну цифру, одну заглавную и одну строчную букву.</small>
            <small id="password_strength" class="strength"></small>
            
            <input type="text" name="name" id="name" placeholder="Имя" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            
            <input type="text" name="surname" id="surname" placeholder="Фамилия" value="<?php echo isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : ''; ?>">
            
            <input type="email" name="email" id="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            
            <input type="submit" id="submit" class="submit" value="Зарегистрироваться" onclick="return validatePasswordStrength()">
            
            <?php if (!empty($error_fields)): ?>
                <div class="error_message">
                    <?php foreach ($error_fields as $field => $message): ?>
                        <p><?php echo $message; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        function checkPasswordStrength() {
            var password = document.getElementById('password').value;
            var strengthText = document.getElementById('password_strength');
            var requirementsText = document.getElementById('password_requirements');
            var strength = 'weak';
            
            if (password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password)) {
                strength = 'strong';
            } else if (password.length >= 6) {
                strength = 'normal';
            }
            
            switch (strength) {
                case 'weak':
                    strengthText.textContent = 'Пароль слабый';
                    strengthText.className = 'strength weak';
                    requirementsText.style.display = 'block';
                    break;
                case 'normal':
                    strengthText.textContent = 'Пароль нормальный';
                    strengthText.className = 'strength normal';
                    requirementsText.style.display = 'block';
                    break;
                case 'strong':
                    strengthText.textContent = 'Пароль сильный';
                    strengthText.className = 'strength strong';
                    requirementsText.style.display = 'none';
                    break;
            }
            
            strengthText.style.display = 'block';
        }
        
        function validatePasswordStrength() {
            var strengthText = document.getElementById('password_strength').textContent;
            if (strengthText !== 'Пароль сильный') {
                alert('Пароль должен быть "сильным" для регистрации.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
