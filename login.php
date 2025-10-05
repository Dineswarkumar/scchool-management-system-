<!DOCTYPE html>
<html>
<head>
    <title>Login - My School</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="w.css">
</head>
<body>
    <form class="login-table" action="login_process.php" method="POST">
    <header>
        <div class="header-container">
            <img id="logo" src="logo.jpg" alt="Logo">
            <h1>My school</h1>
            <nav class="nav-links">
                <a href="W.HTML">Home</a>
                <a href="#about">about</a>
                <a href="#contact">contact</a>
            </nav>
        </div>
    </header>
    <main class="welcome-main">
        <section class="welcome-section">
            <h2>Login</h2>
            
            <?php if(isset($_GET['error'])): ?>
                <div style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['success'])): ?>
                <div style="background-color: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" action="login_process.php" method="POST">
                <select id="blank" name="role" required style="margin-bottom: 20px;">
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select><br><br>
                
                <input id="blank" type="text" name="username" placeholder="Username" required><br><br>
                <input id="blank" type="password" name="password" placeholder="Password" required><br><br>
                <button class="welcome-btn" type="submit">Login</button>
            </form>
            <p style="margin-top:20px;">Don't have an account? <a style="font-size:large;" href="reg.php">Sign Up</a></p>
        </section>
    </main>
</body>
</html>