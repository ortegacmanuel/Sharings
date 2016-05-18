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

La activación de otros idiomas depende de que las traducciones al idioma que quieres activar estén completadas. Por el momento solo tenemos traducciones de Sharings al Esperanto - dialecto Komunuma - y al Inglés.



# Sharings

Sharings adds to GNU Social the ability to add objects and services to share with users on your node, while other nodes are connected to you through the federation.

The result is a catalog of objects and shared services that can vary from node to node depending on the connections of its users.

Sharings is the [first tool distributed for a new Sharing Economy] (https://english.lasindias.com/the-first-distributed-tool-for-a-new-sharing-economy).

# Installation

Clone Sharings within the local / plugins` directory and activate it in config.php:

``` Php
addPlugin('sharings');
```

Update schemes database from the root of your installation of GNU social

``` Php
php scripts/checkschema.php
```

Load categories, types and care used in sharings from the root directory of the plugin in `local/plugins/Sharings`

``` Php
php scripts/seedsharings.php
```

# Update

`Git pull` within the root directory sharings

Update schemes database from the root of your installation of GNU social

``` Php
php scripts/checkschema.php
```

Update categories, types and care used in sharings from the root directory of sharings

``` Php
php scripts/seedsharings.php
```

# Languages

The sharings default language is Spanish but other languages can activate compiling language translation you want to activate. For example if you want to activate English translations you have to go to `local/en/LC_MESSAGES` and compile translations running:

``` bash
cd local/en/LC_MESSAGES
msgfmt -o Sharings.mo Sharings.po
```

