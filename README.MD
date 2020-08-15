###Słowo wstępu
Aplikacja jest napisana w możliwie łatwy sposób. Podszedłem do sprawy trochę ambitnie i postanowiłem napisać apkę w symfony które ogarniam tak sobie, niektóre rzeczy mogą być źle skonfigurowane.
Nie używałem zbytnio normalizerów czy serializerów a wydaje mi się, ze powinny zostać użyte do outputowania response'a. Jeżeli chodzi o exceptiony to nie wiem czy moje podejście jest dobre, 
użyłem prostego exception controllera implementującego normalizer. 
Największa zbrodnie popełniłem przy pisaniu testów, w momencie tworzenia testowych encji
występuje daleko posunięta duplikacja kodu. Prawidłowo, baza danych, powinna nimi być zapełniana za pomocą chociażby fixtures,
w jakiejś metodzie typu before. Z kolei ich usuwanie w metodzie after.
Zdecydowałem napisać tylko testy funkcjonalne, pozytywne. Tyle jeżeli chodzi o samokrytykę :)
 
Oparłem cart(koszyk) o cookie, tzn, przy tworzeniu carta klientowi zapisuje się ciasteczko z identyfikatorem
które jest później wykorzystywane przy innych operacjach. Inną opcją, aby w jakiś sposób identyfikować klienta niezalogowanego
mógłby być fingerprint przeglądarki. 
Inną rzeczą warta wspomnienia, jest fakt dodania przeze mnie encji pośredniczącej między produktem a cartem
```ProductInCart```, ma ona służyć zapisowi tego, ile produktów tego samego typu, jest w carcie.



###Apka

Jako bazę danych postanowiłem użyć SQLite'a.


Przed odpaleniem apki należy wykonać następujące komendy:

```composer install```

Następnie odpalić migracje:

```php bin/console make:migration```

```php bin/console doctrine:migrations:migrate```

Aby załadować fixtures

```php bin/console doctrine:fixtures:load```

Aby odpalić testy:

```bin/phpunit tests/```

Odpalenie servera:

```php bin/console server:start```

Mam nadzieje ze niczego nie pominąłem.

Pozdrawiam, 
Kacper


