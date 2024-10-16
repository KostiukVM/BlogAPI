
Запустити сервер :
```angular2html
php artisan serve --port=8000
```

Запустити міграції:
```angular2html
php artisan migrate
```

Документація API:
```angular2html
http://localhost:8000/api/documentation
```

Створити тестові дані для БД:  
 - створиться 1 користувач, якому належать всі пости і коментарі
```angular2html
php artisan db:seed
```

Запустити міграції та створити фейкові дані:
```angular2html
php artisan migrate --seed
```
