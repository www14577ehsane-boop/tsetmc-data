# TSETMC بدون Cloudflare

این پروژه از GitHub Actions استفاده می‌کند:

TSETMC -> GitHub Actions -> data/market.json -> WordPress

## راه‌اندازی
1. یک Repository جدید در GitHub بسازید.
2. محتویات پوشه `github-repository` را داخل Repository آپلود کنید.
3. در GitHub به Actions بروید و workflow `Update TSETMC Data` را یک‌بار Run کنید.
4. بعد از ساخته شدن فایل data/market.json آدرس زیر را بسازید:
   https://raw.githubusercontent.com/USERNAME/REPOSITORY/main/data/market.json
5. افزونه داخل wordpress-plugin را ZIP و در وردپرس نصب کنید.
6. در پیشخوان > TSETMC Data آدرس JSON را وارد کنید.
7. شورت‌کد:
   [tsetmc_symbols]

رنگ اصلی: #182966

نکته: GitHub Actions هر 6 ساعت داده را به‌روزرسانی می‌کند.
