<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - OrizonPlus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        input,
        button {
            border-radius: 8px;
        }

        button {
            background: #764ba2;
            border: none;
        }

        button:hover {
            background: #667eea;
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="card shadow">
            <h3 class="mb-4 text-center">Connexion OrizonPlus</h3>
            <div v-if="error" class="alert alert-danger">{{ error }}</div>
            <form @submit.prevent="login">
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Nom" v-model="name">
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Mot de passe" v-model="password">
                </div>
                <button type="submit" class="btn w-100 text-white">Se connecter</button>
            </form>
        </div>
    </div>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    name: '',
                    password: '',
                    error: ''
                }
            },
            methods: {
                async login() {
                    this.error = '';
                    try {
                        const url = 'http://127.0.0.1/orizonplus/api/index.php?action=login';
                        const bodyData = {
                            name: this.name,
                            password: this.password
                        };

                        console.log("🚀 Route appelée :", url);
                        console.log("📦 Body envoyé :", bodyData);

                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(bodyData)
                        });

                        const text = await res.text(); // lire la réponse brute
                        console.log("📥 Réponse brute :", text);

                        const data = JSON.parse(text); // parser le JSON
                        console.log("✅ Réponse JSON :", data);

                        if (!data.success) throw new Error(data.message);

                        localStorage.setItem('user', JSON.stringify(data.data));
                        window.location.href = 'index.php';
                    } catch (e) {
                        console.error("❌ Erreur login :", e);
                        this.error = e.message;
                    }
                }

            }
        });
        app.mount('#app');
    </script>
</body>

</html>