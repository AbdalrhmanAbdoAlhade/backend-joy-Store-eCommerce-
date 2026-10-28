{{-- resources/views/emails/welcome.blade.php --}}
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>مرحباً</title>
</head>
<body style="font-family: Arial, sans-serif; direction: rtl;">
    <h2>مرحباً {{ $user->name }}!</h2>
    <p>شكراً لانضمامك إلى متجر الألعاب.</p>
    <p>نتمنى لك تجربة تسوق ممتعة.</p>
</body>
</html>
