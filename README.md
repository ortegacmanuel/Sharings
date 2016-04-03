# Sharings

Sharings agrega a GNU social la posibilidad de añadir objetos y servicios para compartirlos con los usuarios en tu nodo o que bien, estando en otros nodos, estén conectados a ti a través de la federación.

El resultado es un **catálogo de objetos y servicios compartidos** que puede variar de nodo a nodo dependiendo de las conexiones de sus usuarios.

Sharings es la [primera herramienta distribuida para una nueva Sharing Economy](https://lasindias.com/primera-herramienta-distribuida-para-una-nueva-sharing-economy).

# Instalación

Clonar `Sharings` dentro del directorio `local/plugins` y activarlo en config.php:

```php
addPlugin('Sharings');
```

Actualizar los esquemas de la base de datos desde la raíz de tu instalación de GNU social

```php
php scripts/checkschema.php
```

Cargar las categorías, tipos y cuidades utilizadas en Sharings desde el directorio raíz del plugin en `local/plugins/Sharings`

```php
php scripts/seedsharings.php
```

# Actualización

Hacer `git pull` dentro del directorio raíz de Sharings

Actualizar los esquemas de la base de datos desde la raíz de tu instalación de GNU social

```php
php scripts/checkschema.php
```

Actualizar las categorías, tipos y cuidades utilizadas en Sharings desde el directorio raíz de Sharings

```php
php scripts/seedsharings.php
```

# Idiomas

El idioma por defecto de Sharings es el Español pero puedes activar otros idiomas compilando las traducción del idioma que quieres activar. Por ejemplo si quieres activar las traducciones al Esperanto tienes que ir a `local/eo/LC_MESSAGES` y compilar las traducciones ejecutando:

```php
msgfmt -o Sharings.mo Sharings.po
```

La activación de otros idiomas depende de que las traducciones al idioma que quieres activar estén completadas. Por es momento solo tenemos traducciones de Sharings al Esperanto - dialecto Komunuma - y al Inglés.
