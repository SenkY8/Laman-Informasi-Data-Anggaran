<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — BBKK Batam</title>

<link href="asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="asset/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">

<style>
* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
    font-family: "Poppins", sans-serif; 
}

/* === ANIMASI FADE BODY === */
body {
    background: linear-gradient(135deg, #1abc9c 0%, #17bcc4 50%, #c5f033 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow-x: hidden;
}

/* Background blur decorative elements */
body::before {
    content: '';
    position: fixed;
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    top: -100px;
    left: -100px;
    z-index: 0;
}

body::after {
    content: '';
    position: fixed;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    bottom: -50px;
    right: -50px;
    z-index: 0;
}

/* === CARD LOGIN + ANIMASI === */
.login-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 460px;
    padding: 20px;
}

.login-card {
    background: white;
    padding: 45px 40px;
    border-radius: 20px;
    box-shadow: 0 15px 45px rgba(0,0,0,0.15);
    text-align: center;
    backdrop-filter: blur(10px);

    opacity: 0;
    transform: translateY(40px) scale(0.95);
    animation: fadeSlideUp 0.8s ease-out forwards;
}

@keyframes fadeSlideUp {
    0% { 
        opacity: 0; 
        transform: translateY(40px) scale(0.95);
    }
    100% { 
        opacity: 1; 
        transform: translateY(0) scale(1);
    }
}

.login-card img {
    width: 100px;
    margin-bottom: 15px;
    filter: drop-shadow(0 4px 8px rgba(26, 188, 156, 0.2));
    animation: floatImg 3s ease-in-out infinite;
}

@keyframes floatImg {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

.login-title {
    font-size: 28px;
    font-weight: 700;
    background: linear-gradient(135deg, #1abc9c 0%, #17bcc4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}

.login-subtitle {
    font-size: 14px;
    font-style: italic;
    color: #666;
    margin-bottom: 28px;
    opacity: 0.8;
    font-weight: 500;
}

/* INPUT STYLE */
.input-group-custom {
    width: 100%;
    margin-bottom: 20px;
    position: relative;
}

.input-group-custom i {
    position: absolute;
    top: 50%;
    left: 16px;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #1abc9c 0%, #17bcc4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 18px;
    font-weight: 600;
}

.input-group-custom input {
    width: 100%;
    height: 52px;
    padding-left: 50px;
    padding-right: 16px;
    font-size: 15px;
    border-radius: 12px;
    border: 2px solid #e8f0f2;
    transition: all 0.3s ease;
    background: #f8fbfc;
}

.input-group-custom input:focus {
    border-color: #1abc9c;
    background: white;
    box-shadow: 0 0 15px rgba(26, 188, 156, 0.2);
    outline: none;
}

.input-group-custom input::placeholder {
    color: #aaa;
}

/* BUTTON */
.btn-login {
    width: 100%;
    height: 52px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1abc9c 0%, #17bcc4 100%);
    color: white;
    border: none;
    font-weight: 700;
    font-size: 16px;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-top: 8px;
    box-shadow: 0 8px 20px rgba(26, 188, 156, 0.3);
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
    background: rgba(255,255,255,0.2);
    transition: left 0.4s ease;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(26, 188, 156, 0.4);
}

.btn-login:active {
    transform: translateY(0);
}

.btn-back {
    width: 100%;
    height: 52px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    border: 2px solid #e8e8e8;
    margin-top: 12px;
    background: white;
    color: #666;
    transition: all 0.3s ease;
    display: inline-block;
    line-height: 48px;
    text-decoration: none;
    cursor: pointer;
}

.btn-back:hover {
    border-color: #1abc9c;
    color: #1abc9c;
    background: #f8fbfc;
}

.footer-text {
    margin-top: 24px;
    color: #999;
    font-size: 13px;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 480px) {
    .login-card {
        padding: 35px 25px;
    }
    
    .login-title {
        font-size: 24px;
    }
    
    .login-subtitle {
        font-size: 13px;
    }
}
</style>
</head>

<body>

<div class="login-container">
    <div class="login-card">

        <img src="img/karantina.png" alt="BBKK Batam">

        <div class="login-title">MinDA BBKK Batam</div>
        <div class="login-subtitle">Laman Informasi Data Anggaran</div>

        <form action="login_cek.php" method="POST">

            <div class="input-group-custom">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Masukkan Username" required>
            </div>

            <div class="input-group-custom">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Masukkan Password" required>
            </div>

            <button type="submit" class="btn-login">Masuk</button>

        </form>

        <a href="index.php" class="btn-back">Kembali ke Beranda</a>


    </div>
</div>

</body>
</html>