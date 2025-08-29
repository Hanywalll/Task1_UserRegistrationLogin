<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --text-color: #333;
            --bg-light: #f4f5f9;
            --card-bg: #fff;
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        .dashboard-container {
            background-color: var(--card-bg);
            padding: 50px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            max-width: 600px;
            width: 100%;
            transition: transform 0.3s ease-in-out;
            animation: fadeIn 1s ease-in-out;
        }
        
        .dashboard-container:hover {
            transform: translateY(-5px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 2.5em;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        p {
            font-size: 1.1em;
            color: #666;
            margin-top: 0;
            line-height: 1.6;
        }

        .username {
            font-weight: 600;
            color: var(--secondary-color);
        }

        a {
            text-decoration: none;
        }
        
        button {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            padding: 15px 30px;
            font-size: 1em;
            font-weight: 500;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(108, 92, 231, 0.3);
            letter-spacing: 0.5px;
            margin-top: 20px;
        }

        button:hover {
            background-color: #5d4ed1;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 92, 231, 0.4);
        }

        button:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Selamat Datang, <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?>!</span></h2>
        <p>Anda telah berhasil masuk ke dashboard.</p>
        
        <a href="logout.php">
            <button>Logout</button>
        </a>
    </div>
</body>
</html>