<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; margin: 0; padding: 0; direction: rtl; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb, #f15a24); padding: 32px 24px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body { padding: 32px 28px; }
        .greeting { font-size: 16px; color: #0f172a; margin-bottom: 16px; }
        .message { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 24px; }
        .code-box { background: #f1f5f9; border: 2px dashed #2563eb; border-radius: 12px; text-align: center; padding: 20px; margin-bottom: 24px; }
        .code { font-size: 40px; font-weight: 900; letter-spacing: 12px; color: #2563eb; }
        .expiry { font-size: 13px; color: #94a3b8; margin-top: 8px; }
        .warning { font-size: 13px; color: #ef4444; background: #fef2f2; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; }
        .footer { background: #f8fafc; padding: 20px 28px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>🔐 إعادة تعيين كلمة المرور</h1>
        </div>
        <div class="body">
            <p class="greeting">مرحباً {{ $userName }}،</p>
            <p class="message">
                تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك.
                استخدم الكود التالي لإتمام العملية:
            </p>
            <div class="code-box">
                <div class="code">{{ $code }}</div>
                <div class="expiry">صالح لمدة 15 دقيقة فقط</div>
            </div>
            <div class="warning">
                ⚠️ إذا لم تطلب إعادة تعيين كلمة المرور، تجاهل هذا البريد الإلكتروني.
            </div>
        </div>
        <div class="footer">
            © {{ date('Y') }} Care & Share — جميع الحقوق محفوظة
        </div>
    </div>
</body>
</html>
