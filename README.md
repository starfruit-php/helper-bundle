Starfruit Helper Bundle

Includes useful functions for platform Pimcore v.11

<!-- [TOC] -->

# Installation

1. On your Pimcore 11 root project:
```bash
$ composer require starfruit/helper-bundle
```

2. Update `config/bundles.php` file:
```bash
return [
    ....
    Starfruit\HelperBundle\StarfruitHelperBundle::class => ['all' => true],
];
```