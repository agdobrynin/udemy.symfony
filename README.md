### Создать JWT сертификаты
```shell
# php bin/console lexik:jwt:generate-keypair
```
### Выполнить миграции
```shell
# php bin/console doctrine:migrations:migrate
```
### Заполнить тестовыми данными таблицы
```shell
# php bin/console doctrine:fixtures:load
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
