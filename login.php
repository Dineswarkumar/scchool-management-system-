<!DOCTYPE html>
<html>
<head>
    <title>Login - My School</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="w.css">
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="bg-texture"></div>

    <header>
        <div class="header-container">
            <div class="logo-wrapper">
                <img id="logo" src="logo.jpg" alt="Logo">
                <h1>My school</h1>
            </div>
            <nav class="nav-links">
                <a href="w.html">Home</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </nav>
        </div>
    </header>

    <main class="welcome-main">
        <section class="glass-card">
            <h2 class="highlight">Login</h2>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert-message alert-error">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert-message alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Removed invalid outer form, kept only this inner functional form -->
            <form class="login-form" action="login_process.php" method="POST">
                <div class="form-group">
                    <select class="glass-input" name="role" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <input class="glass-input" type="text" name="username" placeholder="Username" required>
                </div>
                
                <div class="form-group">
                    <input class="glass-input" type="password" name="password" placeholder="Password" required>
                </div>

                <button class="welcome-btn primary-btn" type="submit" style="width: 100%;">Login</button>
            </form>

            <p class="welcome-text" style="margin-top:20px; font-size: 0.9rem;">
                Don't have an account? <a class="auth-link" href="reg.php">Sign Up</a>
            </p>
        </section>
    </main>
</body>
</html>