<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode Pemulihan Akun Alinea</title>
    <style>
        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #2563eb;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px 30px;
            color: #374151;
            line-height: 1.6;
            font-size: 16px;
        }
        .code-box {
            background-color: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 12px;
            margin: 0;
            padding-left: 12px; /* to balance the letter spacing */
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <div class="container">
                    <div class="header">
                        <img src="{{ $message->embed(public_path('images/alinealogo.png')) }}" alt="Alinea Logo" style="height: 40px; width: auto;">
                    </div>
                    <div class="content">
                        <p style="font-size: 18px; font-weight: 600;">Halo,</p>
                        <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Alinea Anda. Berikut adalah kode verifikasi yang Anda butuhkan:</p>
                        
                        <div class="code-box">
                            <p class="code">{{ $code }}</p>
                        </div>
                        
                        <p>Kode ini hanya berlaku selama <strong>15 menit</strong>. Mohon untuk tidak membagikan kode ini kepada siapa pun demi keamanan akun Anda.</p>
                        
                        <p>Jika Anda tidak merasa meminta pemulihan kata sandi, Anda dapat mengabaikan dan menghapus email ini dengan aman.</p>
                        
                        <br>
                        <p>Salam hangat,<br><strong style="color: #2563eb;">Tim Alinea</strong></p>
                    </div>
                    <div class="footer">
                        <p>&copy; {{ date('Y') }} Alinea. Hak Cipta Dilindungi.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
