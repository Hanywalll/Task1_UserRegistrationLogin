<?php
session_start();
require_once 'db_config.php';
$message = '';
$is_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];

    if (empty($username_or_email) || empty($password)) {
        $message = "Username/Email dan password wajib diisi.";
    } else {
        $is_email = filter_var($username_or_email, FILTER_VALIDATE_EMAIL);
        $field_to_check = $is_email ? 'email' : 'username';

        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE BINARY $field_to_check = ?");
        $stmt->bind_param("s", $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $message = "Login berhasil! Selamat datang, " . $user['username'] . ".";
                $is_success = true;
                header("refresh:2;url=dashboard.php");
            } else {
                $message = "Password salah.";
            }
        } else {
            $message = "Username atau email tidak terdaftar.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f0f2f5; margin: 0; }
        .login-container { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 90%; }
        h2 { margin-bottom: 20px; color: #333; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; border: none; border-radius: 8px; background-color: #007bff; color: white; font-size: 16px; cursor: pointer; transition: background-color 0.3s; }
        button:hover { background-color: #0056b3; }
        .message { margin-top: 15px; color: #777; }
        
        .input-container {
            position: relative;
            margin-bottom: 20px;
        }

        .input-container input:not(#password) {
            padding-right: 40px;
            margin-bottom: 0;
        }

        .input-container input#password {
            padding-right: 70px;
            margin-bottom: 0;
        }

        .error-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: red;
            font-size: 1.5em;
            cursor: pointer;
            display: none;
            z-index: 10;
        }

        .tooltip {
            visibility: hidden;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px 12px;
            position: absolute;
            z-index: 100;
            left: 105%;
            top: 50%;
            transform: translateY(-50%);
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip::after {
            content: "";
            position: absolute;
            top: 50%;
            right: 100%;
            margin-top: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent #333 transparent transparent;
        }

        .password-toggle-icon {
            position: absolute;
            top: 50%;
            right: 45px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            font-size: 1.2em;
            z-index: 10;
        }
        
        .password-toggle-icon svg {
            stroke: #333;
            width: 20px;
            height: 20px;
            vertical-align: middle;
        }

        .modal-backdrop { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0, 0, 0, 0.4); 
            backdrop-filter: blur(5px); 
            z-index: 999; 
            display: none; 
        }
        .custom-alert-modal { 
            position: fixed; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
            width: 90%; 
            max-width: 400px; 
            background: #fff; 
            border-radius: 20px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); 
            z-index: 1000; 
            text-align: center; 
            display: none; 
            opacity: 0; 
            transition: all 0.3s ease; 
        }
        .custom-alert-modal.show { 
            opacity: 1; 
            transform: translate(-50%, -50%) scale(1); 
        }
        .modal-content { 
            padding: 30px; 
        }
        .icon-container { 
            padding-top: 10px; 
            margin-bottom: 20px; 
        }
        .error-icon-modal { 
            width: 60px; 
            height: 60px; 
            stroke: #D14343; 
        }
        .success-icon { 
            width: 60px; 
            height: 60px; 
            stroke: #4CAF50; 
        }
        .modal-body h2 { 
            font-size: 24px; 
            font-weight: bold; 
            color: #333; 
            margin: 0 0 10px; 
        }
        .modal-body p { 
            font-size: 16px; 
            color: #666; 
            margin: 0 0 20px; 
        }
        #okButton { 
            padding: 12px 30px; 
            border: none; 
            border-radius: 10px; 
            background-color: #6C5CE7; 
            color: white; 
            font-weight: bold; 
            font-size: 16px; 
            cursor: pointer; 
            transition: background-color 0.3s; 
        }
        #okButton:hover { 
            background-color: #5d4ed1; 
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>User Login</h2>
        <form method="POST" action="index.php" id="loginForm">
            <div class="input-container">
                <input type="text" name="username_or_email" id="username_or_email" placeholder="Username or Email">
                <span class="error-icon" id="username_or_email-icon">!</span>
                <span class="tooltip" id="username_or_email-tooltip"></span>
            </div>
            
            <div class="input-container">
                <input type="password" name="password" id="password" placeholder="Password">
                <span class="password-toggle-icon" id="togglePassword">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
                <span class="error-icon" id="password-icon">!</span>
                <span class="tooltip" id="password-tooltip"></span>
            </div>

            <button type="submit" id="loginButton">Login</button>
        </form>
        <p class="message">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>

    <div class="modal-backdrop"></div>
    <div class="custom-alert-modal">
        <div class="modal-content">
            <div class="icon-container">
                </div>
            <div class="modal-body">
                <h2 id="alertTitle"></h2>
                <p id="alertMessage"></p>
                <button id="okButton">OK</button>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const usernameOrEmailInput = document.getElementById('username_or_email');
        const passwordInput = document.getElementById('password');
        const loginButton = document.getElementById('loginButton');

        const usernameOrEmailIcon = document.getElementById('username_or_email-icon');
        const passwordIcon = document.getElementById('password-icon');

        const usernameOrEmailTooltip = document.getElementById('username_or_email-tooltip');
        const passwordTooltip = document.getElementById('password-tooltip');

        const togglePassword = document.getElementById('togglePassword');
        
        const modal = document.querySelector('.custom-alert-modal');
        const backdrop = document.querySelector('.modal-backdrop');
        const okButton = document.getElementById('okButton');
        const alertTitle = document.getElementById('alertTitle');
        const alertMessage = document.getElementById('alertMessage');
        const iconContainer = document.querySelector('.icon-container');

        const eyeOpenSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        const eyeOffSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A5 5 0 0 1 12 17a5 5 0 0 1-5-5c0-1.28.5-2.43 1.25-3.3M15 12a3 3 0 1 1-6 0"></path><path d="M1 1l22 22"></path></svg>`;

        function showAlert(title, message, isSuccess) {
            alertTitle.textContent = title;
            alertMessage.textContent = message;

            if (isSuccess) {
                iconContainer.innerHTML = `<svg class="success-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-8.62"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
            } else {
                iconContainer.innerHTML = `<svg class="error-icon-modal" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`;
            }

            modal.style.display = 'block';
            backdrop.style.display = 'block';
            setTimeout(() => { modal.classList.add('show'); }, 10);
            
            okButton.focus();
        }

        function hideAlert() {
            modal.classList.remove('show');
            backdrop.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                backdrop.style.display = 'none';
            }, 300);
        }

        okButton.addEventListener('click', hideAlert);
        
        okButton.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                hideAlert();
            }
        });

        <?php if (!empty($message)): ?>
            document.addEventListener('DOMContentLoaded', () => {
                showAlert(
                    "<?php echo $is_success ? 'Login Berhasil' : 'Login Gagal'; ?>",
                    "<?php echo htmlspecialchars($message); ?>",
                    <?php echo $is_success ? 'true' : 'false'; ?>
                );
            });
        <?php endif; ?>

        function showTooltip(tooltipElement, iconElement, message) {
            tooltipElement.textContent = message;
            iconElement.style.display = 'block';
            tooltipElement.style.visibility = 'visible';
            tooltipElement.style.opacity = '1';
        }

        function hideTooltip(tooltipElement, iconElement) {
            iconElement.style.display = 'none';
            tooltipElement.style.visibility = 'hidden';
            tooltipElement.style.opacity = '0';
        }

        usernameOrEmailInput.addEventListener('focus', () => {
            hideTooltip(usernameOrEmailTooltip, usernameOrEmailIcon);
        });

        passwordInput.addEventListener('focus', () => {
            hideTooltip(passwordTooltip, passwordIcon);
        });

        usernameOrEmailInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                passwordInput.focus();
            }
        });

        passwordInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                loginButton.click();
            }
        });

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'password') {
                togglePassword.innerHTML = eyeOpenSVG;
            } else {
                togglePassword.innerHTML = eyeOffSVG;
            }
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault(); 
            
            let formIsValid = true; 
            
            const usernameOrEmail = usernameOrEmailInput.value.trim();
            if (usernameOrEmail === '') {
                showTooltip(usernameOrEmailTooltip, usernameOrEmailIcon, 'Username atau Email wajib diisi.');
                formIsValid = false;
            } else {
                hideTooltip(usernameOrEmailTooltip, usernameOrEmailIcon);
            }
            
            const password = passwordInput.value.trim();
            if (password === '') {
                showTooltip(passwordTooltip, passwordIcon, 'Password wajib diisi.');
                formIsValid = false;
            } else {
                hideTooltip(passwordTooltip, passwordIcon);
            }

            if (formIsValid) {
                form.submit();
            }
        });
    </script>
</body>
</html>