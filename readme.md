HOẶC cài đặt thủ công:
- Import file sql database/s-cart.sql vào database.
- Đổi tên file .env.example thành .env nếu file .env của có sẵn.
- Tạo API key nếu APP_KEY trong file .env rỗng. Dùng lệnh "php artisan key:generate"
- Cấu hình các giá trị trong file .env:
APP_DEBUG=false (Sét giá trị là false để bảo vệ thông tin website)
DB_HOST=127.0.0.1 (Địa chỉ server database)
DB_PORT=3306 (Port của database)
DB_DATABASE=s-cart (Tên dữ liệu của bạn)
DB_USERNAME=root (Tên người dùng kết nối dữ liệu)
DB_PASSWORD= (Mật khẩu người dùng dữ liệu)ss
APP_URL=http://localhost (đây là url website của bạn)
ADMIN_URL=system_admin (Đường dẫn tới Admin)

* Chú ý - Xóa hoặc đổi tên file public/install.php để người khách không truy cập được.

Cấu hình
- Sét quyền ghi cho thư mục và thư mục con của nó: public/documents/website, storage
- Thư mục chứa thumbnail (ảnh nhỏ) trong public/documents/website/thumb.
Lưu ý: Cấu trúc thư mục trong "public/documents/website/thumb" phải giống thư mục "public/documents/website"
- Cần đảm bảo vhost của website trỏ tới thư mục public.
- Link admin: your-domain/system_admin. User/pass mặc định admin/admin.


Cài đặt trên server

Vui lòng chắc chắn rằng virtual host của bạn trỏ tới thư mục public của s-cart. Bạn có thể tham khảo việc cấu hình vhost như bên dưới:

Cho websever Nginx

```
server {
        listen 80;
        root /home/domain/you-domain.com/public/;
        index index.php index.html index.htm;
        server_name your-domain.com;
        location / {
                try_files $uri $uri/ /index.php?$query_string;
        }
        location ~ \.php$ {
                try_files $uri =404;
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                fastcgi_pass unix:/run/php-fpm/php-fpm.sock;
                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include fastcgi_params;
        }
}
```

Cho websever Apache

    ServerAdmin your-domain.com
    DocumentRoot "C:\xampp\htdocs\s-cart/public"
    ServerName your-domain.com
