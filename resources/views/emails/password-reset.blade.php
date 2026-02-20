<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Aurora Beauty Salon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #333;
            margin-top: 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🌸 Aurora Beauty Salon</h1>
            <p>Reset Password Anda</p>
        </div>
        
        <div class="content">
            <h2>Halo!</h2>
            <p>Kami menerima permintaan untuk reset password akun Anda. Jika Anda yang melakukan permintaan ini, silakan klik tombol di bawah untuk membuat password baru.</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            </div>
            
            <div class="warning">
                <strong>⚠️ Penting:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Link ini hanya berlaku selama <strong>60 menit</strong></li>
                    <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                    <li>Password Anda tidak akan berubah sampai Anda mengakses link dan membuat password baru</li>
                </ul>
            </div>
            
            <p>Jika tombol di atas tidak bekerja, copy dan paste URL berikut ke browser Anda:</p>
            <p style="background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; font-family: monospace; font-size: 14px;">
                {{ $resetUrl }}
            </p>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem Aurora Beauty Salon</p>
            <p>Jika Anda memiliki pertanyaan, hubungi customer service kami</p>
            <p>&copy; {{ date('Y') }} Aurora Beauty Salon. All rights reserved.</p>
        </div>
    </div>
</body>
</html>