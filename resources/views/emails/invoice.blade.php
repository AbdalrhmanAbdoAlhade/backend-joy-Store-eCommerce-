{{-- resources/views/emails/invoice.blade.php --}}
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فاتورة</title>
</head>
<body style="font-family: Arial, sans-serif; direction: rtl;">
    <h2>فاتورة الطلب #{{ $order->order_number }}</h2>
    <p>عزيزي/عزيزتي {{ $user->name ?? 'عميلنا' }}،</p>
    <p>مرفق فاتورة طلبك.</p>
    <p>الإجمالي: <strong>{{ number_format($order->total, 2) }} ج.م</strong></p>
    <p>شكراً لتسوقك معنا.</p>
</body>
</html>
