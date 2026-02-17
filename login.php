<?php
session_start();

// s'il n'y a pas de session, rediriger vers login.php
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - OrizonPlus</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #111111;
            --bg-tertiary: #1a1a1a;
            --border-color: #2a2a2a;
            --text-primary: #ededed;
            --text-secondary: #a0a0a0;
            --accent-blue: #0070f3;
            --accent-cyan: #00d4ff;
            --accent-green: #00e676;
            --accent-red: #ff3b3b;
            --gradient-main: linear-gradient(135deg, #0070f3 0%, #00d4ff 100%);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.5);
            --radius: 12px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Background decoration */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(0, 112, 243, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 50%, rgba(0, 212, 255, 0.1) 0%, transparent 50%);
            animation: rotate 20s linear infinite;
            z-index: 0;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .login-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 48px 40px;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(10px);
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-main);
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: var(--gradient-main);
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(0, 112, 243, 0.3);
        }

        .logo i {
            font-size: 32px;
            color: white;
        }

        .logo-section h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-section p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 15px;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(0, 112, 243, 0.1);
        }

        .form-input:focus+.input-icon {
            color: var(--accent-blue);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: rgba(255, 59, 59, 0.1);
            border: 1px solid rgba(255, 59, 59, 0.3);
            border-radius: 8px;
            color: var(--accent-red);
            font-size: 14px;
            margin-bottom: 24px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--gradient-main);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 112, 243, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-login:disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .footer-links {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent-blue);
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }

            .logo-section h1 {
                font-size: 24px;
            }

            .form-input {
                padding: 12px 14px 12px 44px;
            }
        }

        /* Animation d'entrée */
        .login-card {
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div id="app" class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1>OrizonPlus</h1>
                <p>Gestion de Projets et Budgets</p>
            </div>

            <div v-if="errorMessage" class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ errorMessage }}</span>
            </div>

            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label class="form-label" for="username">Nom d'utilisateur</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="username"
                            class="form-input"
                            v-model="username"
                            placeholder="Entrez votre nom d'utilisateur"
                            required
                            :disabled="isLoading" />
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            class="form-input"
                            v-model="password"
                            placeholder="Entrez votre mot de passe"
                            required
                            :disabled="isLoading" />
                        <i class="fas fa-lock input-icon"></i>
                        <button
                            type="button"
                            class="password-toggle"
                            @click="showPassword = !showPassword"
                            :disabled="isLoading">
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" :disabled="isLoading">
                    <span v-if="isLoading">
                        <span class="loading-spinner"></span>
                        Connexion en cours...
                    </span>
                    <span v-else>
                        Se connecter
                    </span>
                </button>


            </form>

            <p class="text-center text-secondary small text-center mt-4"
                style="text-align: center; margin-top: 30px; ">
                &copy; OrizonPlus 2026 <br> Built with Blood, Sweat and Tears by
                <a class="text text-secondary"
                    style="text-decoration: none; color:   white; font-weight: bold;"
                    href="https://rachad-alabi-adekambi.github.io/portfolio/">RA</a>
            </p>
        </div>

    </div>

    <script>
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    username: '',
                    password: '',
                    showPassword: false,
                    isLoading: false,
                    errorMessage: ''
                }
            },
            methods: {
                async handleLogin() {
                    // Validation
                    if (!this.username.trim() || !this.password.trim()) {
                        this.errorMessage = 'Veuillez remplir tous les champs';
                        return;
                    }

                    this.isLoading = true;
                    this.errorMessage = '';

                    const route = 'api/index.php?action=login';

                    const payload = {
                        name: this.username,
                        password: this.password
                    };

                    console.log('=== LOGIN REQUEST ===');
                    console.log('Route:', route);
                    console.log('Payload:', payload);

                    try {
                        const response = await axios.post(route, payload, {
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        });

                        console.log('HTTP status:', response.status);
                        console.log('Raw response:', response.data);

                        if (response.data.success) {
                            window.location.href = 'index.php';
                        } else {
                            this.errorMessage = response.data.message || 'Identifiants incorrects';
                            this.isLoading = false;
                        }

                    } catch (error) {
                        console.error('=== LOGIN ERROR ===');

                        if (error.response) {
                            console.error('HTTP status:', error.response.status);
                            console.error('Response:', error.response.data);
                            this.errorMessage = error.response.data.message || 'Identifiants incorrects';
                        } else if (error.request) {
                            console.error('No response received:', error.request);
                            this.errorMessage = 'Impossible de contacter le serveur';
                        } else {
                            console.error('Error:', error.message);
                            this.errorMessage = 'Une erreur est survenue';
                        }

                        this.isLoading = false;
                    }
                }

            },
            mounted() {
                // Focus automatique sur le champ username
                document.getElementById('username').focus();
            }
        }).mount('#app');
    </script>
</body>

</html>