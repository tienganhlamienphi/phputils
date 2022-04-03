# PHPUTILS

phputils is a share utility library

## Usage

```php
use Talmp\Phputils\StrUtil;

// normal case
str_replace([1, 2], [2, 3], '12');                            // '33'
StrUtil::replaceOnce([1, 2], [2, 3], '12');                   // '23'
str_replace(['1', '0'], ['x110x2', 'x010'], 'a1b0c')          // 'ax11x010x2bx010c'
StrUtil::replaceOnce(['1', '0'], ['x110x2', 'x010'], 'a1b0c') // 'ax110x2bx010c'

// edge case
str_replace([12, 23], [23, 45], '123') // '453'
StrUtil::replaceOnce([12, 23], [23, 45], '123') // false
str_replace([12, 23], [23, 45], '1223') // '4545'
StrUtil::replaceOnce([12, 23], [23, 45], '1223') // false
```

## Requirement
Need mbstring extension

## Installation

Use the package manager [composer](https://getcomposer.org/) to install phputils.

```bash
composer require talmp/phputils
```

## Testing

```php
./vendor/bin/phpunit tests
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)
