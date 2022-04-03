### Курс Symfony API Platform with React Full Stack Masterclass
🔑 Learn how to make [REST API in Symfony using API Platform](https://www.udemy.com/course/symfony-api-platform-reactjs-full-stack-masterclass)

🏃 Дополнительно в курсе представлено создание [фронт-приложения на Rect](https://github.com/agdobrynin/api-platform-react-app-course)


### Создать JWT сертификаты
```shell
# php bin/console lexik:jwt:generate-keypair
```
### Выполнить миграции
```shell
# php bin/console doctrine:migrations:migrate
```
### Заполнить тестовыми данными таблицы
(!) Затрёт все изменения в базе данных:
```shell
# php bin/console doctrine:fixtures:load -q
```
Тестовый пароль у всех пользователей в таблице `user` `SaSa145`

Endpoint для авторизации и получения JWT токена `/api/login_check`
отправлять json body
```json
{
    "username": "nyasia",
    "password": "SaSa145"
}
```
### Запуск тестов
Заполнить тестовыми данными перед тестом
```shell
# php php bin/console doctrine:migrations:migrate
# php bin/console doctrine:fixtures:load -q
# php bin/phpunit
```
### Доступ в EasyAdmin
`http://localhost:8000/admin` авторизация под пользователем **nyasia** c дефолтным паролем **SaSa145**
