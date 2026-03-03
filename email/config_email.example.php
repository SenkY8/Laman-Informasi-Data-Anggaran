<?php
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_USERNAME',  '');   // isi email pengirim
define('MAIL_PASSWORD',  '');   // isi app password Gmail
define('MAIL_PORT',      587);
define('MAIL_FROM',      '');   // isi sama dengan USERNAME
define('MAIL_FROM_NAME', 'MinDA BBKK Batam');
?>
```

File ini **di-push ke GitHub** (tidak di-ignore), lalu di `README.md` tulis instruksi:
```
1. Clone repo ini
2. Copy koneksi.php.example menjadi koneksi.php → isi kredensial database
3. Copy email/config_email.example.php menjadi email/config_email.php → isi kredensial email dan jangan lupa chat Rapi pak untuk email dan app passwordnya ya